<?php

namespace App\Actions;

use App\Models\JobApplication;
use App\Models\MailboxConnection;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OpenDemoWorkspace
{
    public function handle(): User
    {
        return DB::transaction(function () {
            $user = User::firstOrCreate(['email' => 'sample@pegasus.example'], ['name' => 'Sample Workspace', 'password' => Str::random(64), 'is_demo' => true, 'email_verified_at' => now()]);
            abort_unless($user->is_demo, 409);
            $gmail = MailboxConnection::firstOrCreate(['user_id' => $user->id, 'provider' => 'gmail', 'address' => 'alex@gmail.example'], ['state' => 'disconnected']);
            $imap = MailboxConnection::firstOrCreate(['user_id' => $user->id, 'provider' => 'namecheap', 'address' => 'alex@personal.example'], ['state' => 'disconnected']);
            $rows = [
                ['Northstar', 'Frontend Engineer', 'interview', 4, $gmail, 'We would love to schedule a conversation with you.'],
                ['Linear Studio', 'Product Designer', 'awaiting_response', 8, $imap, 'Thank you for applying. We have received your application.'],
                ['Evergreen', 'Full Stack Developer', 'awaiting_response', 12, $gmail, 'Your application is with our hiring team.'],
                ['Forma', 'Vue Developer', 'rejected', 18, $imap, 'After careful consideration, we will not be moving forward with your application.'],
                ['Fieldwork', 'Software Engineer', 'response_received', 6, $gmail, 'Could you tell us more about your experience with Laravel?'],
                ['Orbit Labs', 'Web Developer', 'offer', 21, $imap, 'We are pleased to offer you the position of Web Developer.'],
            ];
            foreach ($rows as [$company, $role, $status, $days, $mailbox, $evidence]) {
                $a = JobApplication::firstOrCreate(['user_id' => $user->id, 'company' => $company, 'is_demo' => true], ['role' => $role, 'mailbox_connection_id' => $mailbox->id, 'detected_status' => $status, 'applied_at' => now()->subDays($days), 'last_message_at' => now()->subDays($status === 'awaiting_response' ? $days : 1)]);
                if (! $a->events()->exists()) {
                    $a->events()->create(['event_type' => 'confirmation', 'subject' => 'Your application at '.$company, 'evidence' => 'Thank you for applying for '.$role.'. Your application has been received.', 'occurred_at' => $a->applied_at]);
                    if ($status !== 'awaiting_response') {
                        $a->events()->create(['event_type' => $status === 'rejected' ? 'rejection' : ($status === 'response_received' ? 'response' : $status), 'subject' => 'An update from '.$company, 'evidence' => $evidence, 'occurred_at' => $a->last_message_at]);
                    }
                }
            }

            return $user;
        });
    }
}
