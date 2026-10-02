<?php

namespace App\Http\Controllers;

use App\Models\MailboxConnection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request)
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect('/login')->with('status', 'Google sign-in needs server configuration. You can use your owner email and password.');
        }
        $request->session()->put('google_intent', ['purpose' => 'login']);

        return Socialite::driver('google')->setScopes(['openid', 'email', 'profile'])->redirect();
    }

    public function connect(Request $request)
    {
        abort_if($request->user()->is_demo, 403, 'Sign in to your owner account to connect a real mailbox.');
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect('/settings/mailboxes')->with('message', 'Google connection needs server configuration.');
        }
        $request->session()->put('google_intent', ['purpose' => 'mailbox', 'user_id' => $request->user()->id]);

        return Socialite::driver('google')->redirectUrl(config('services.google.gmail_redirect'))
            ->setScopes(['openid', 'email', 'profile', 'https://www.googleapis.com/auth/gmail.readonly'])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])->redirect();
    }

    public function callback(Request $request)
    {
        $intent = $request->session()->pull('google_intent');
        $mailbox = $request->is('settings/gmail/callback');
        $target = $mailbox ? '/settings/mailboxes' : '/login';
        $flash = $mailbox ? 'message' : 'status';
        if (! $intent || ($intent['purpose'] ?? '') !== ($mailbox ? 'mailbox' : 'login') || ($mailbox && ($intent['user_id'] ?? null) !== $request->user()?->id)) {
            return redirect($target)->with($flash, 'This connection request expired. Please start again.');
        }
        try {
            $driver = Socialite::driver('google');
            if ($mailbox) {
                $driver->redirectUrl(config('services.google.gmail_redirect'));
            }
            $google = $driver->user(); // Socialite verifies the session state; never use stateless here.
            if (! filter_var($google->user['email_verified'] ?? $google->user['verified_email'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                return redirect($target)->with($flash, 'Google must verify this email before it can be connected.');
            }
            if ($mailbox) {
                if (! in_array('https://www.googleapis.com/auth/gmail.readonly', $google->approvedScopes ?? [], true)) {
                    return redirect($target)->with($flash, 'Read-only Gmail permission is required. Please reconnect and grant it.');
                }
                $existing = MailboxConnection::where('user_id', $request->user()->id)->where('provider', 'gmail')->where('address', strtolower($google->email))->first();
                $refresh = $google->refreshToken ?: ($existing?->credentials['refresh_token'] ?? null);
                if (! $refresh) {
                    return redirect($target)->with($flash, 'Offline access was not granted. Please reconnect with Google.');
                }
                MailboxConnection::updateOrCreate(['user_id' => $request->user()->id, 'provider' => 'gmail', 'address' => strtolower($google->email)], [
                    'display_name' => $google->name, 'state' => 'connected', 'generation' => ($existing?->generation ?? 0) + 1,
                    'credentials' => ['subject' => $google->id, 'access_token' => $google->token, 'refresh_token' => $refresh, 'expires_at' => now()->addSeconds($google->expiresIn ?? 3600)->timestamp],
                    'settings' => ['months' => 6, 'folder' => 'INBOX'],
                ]);

                return redirect($target)->with($flash, 'Gmail connected. Your messages have not been scanned yet.');
            }
            $user = User::where('google_subject', $google->id)->first();
            if (! $user && User::where('is_demo', false)->count() === 0 && config('pegasus.owner_email') && strtolower($google->email) === strtolower(config('pegasus.owner_email'))) {
                $user = User::create(['name' => $google->name ?: 'Owner', 'email' => strtolower($google->email), 'password' => Str::random(64)]);
                $user->forceFill(['google_subject' => $google->id, 'email_verified_at' => now()])->save();
            }
            if (! $user) {
                return redirect('/login')->with('status', 'This Google identity is not linked to the Pegasus owner. Use owner email and password.');
            }
            Auth::login($user);
            $request->session()->regenerate();

            return redirect('/applications');
        } catch (Throwable) {
            // Provider exception messages can contain tokens. Show a safe error without logging payloads.
            return redirect($target)->with($flash, 'Google connection failed or was cancelled. Please try again.');
        }
    }
}
