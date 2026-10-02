<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConnectImapRequest;
use App\Models\MailboxConnection;
use App\Services\Mail\ImapConnectionTester;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MailboxController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('settings/Mailboxes', [
            'mailboxes' => MailboxConnection::where('user_id', $request->user()->id)->get()->map(fn ($m) => $m->only('id', 'provider', 'address', 'display_name', 'state', 'settings', 'last_success_at')),
            'googleConfigured' => (bool) config('services.google.client_id'),
            'isDemo' => (bool) $request->user()->is_demo,
        ]);
    }

    public function store(ConnectImapRequest $request, ImapConnectionTester $tester)
    {
        $data = $request->validated();
        if (MailboxConnection::where('user_id', $request->user()->id)->where('address', $data['address'])->where('state', 'connected')->exists()) {
            throw ValidationException::withMessages(['address' => 'This mailbox is already connected.']);
        }
        $tester->test($data);
        $existing = MailboxConnection::where('user_id', $request->user()->id)->where('provider', $data['provider'])->where('address', $data['address'])->first();
        MailboxConnection::updateOrCreate(['user_id' => $request->user()->id, 'provider' => $data['provider'], 'address' => $data['address']], [
            'state' => 'connected', 'generation' => ($existing?->generation ?? 0) + 1,
            'credentials' => ['username' => $data['username'], 'password' => $data['password']],
            'settings' => ['host' => $data['host'], 'port' => (int) $data['port'], 'encryption' => $data['encryption'], 'folder' => 'INBOX', 'months' => 6],
        ]);

        return redirect('/settings/mailboxes')->with('message', 'Mailbox connection verified and saved. Your messages have not been scanned yet.');
    }

    public function destroy(Request $request, int $id)
    {
        $mailbox = MailboxConnection::where('user_id', $request->user()->id)->findOrFail($id);
        $mailbox->update(['credentials' => null, 'state' => 'disconnected', 'generation' => $mailbox->generation + 1]);

        return back()->with('message', 'Disconnected. Stored application history remains available.');
    }
}
