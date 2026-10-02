<?php

use App\Models\JobApplication;
use App\Models\MailboxConnection;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('lists only the signed in owners applications', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $mailbox = MailboxConnection::create(['user_id' => $owner->id, 'provider' => 'gmail', 'address' => $owner->email, 'state' => 'disconnected']);
    $application = JobApplication::create(['user_id' => $owner->id, 'mailbox_connection_id' => $mailbox->id, 'company' => 'Acme', 'role' => 'Developer', 'detected_status' => 'awaiting_response']);
    JobApplication::create(['user_id' => $other->id, 'company' => 'Private employer', 'detected_status' => 'offer']);
    $this->actingAs($owner)->get('/applications')->assertInertia(fn (Assert $page) => $page
        ->component('applications/Index')->has('applications.data', 1)->where('applications.data.0.company', 'Acme'));
    $this->actingAs($other)->get('/applications/'.$application->id)->assertNotFound();
});

it('preserves a manual correction independently from detected status', function () {
    $owner = User::factory()->create();
    $application = JobApplication::create(['user_id' => $owner->id, 'company' => 'Acme', 'detected_status' => 'awaiting_response']);
    $this->actingAs($owner)->patch('/applications/'.$application->id, ['company' => 'Acme', 'role' => 'Engineer', 'manual_status' => 'interview'])->assertRedirect();
    expect($application->fresh()->manual_status)->toBe('interview');
    expect($application->fresh()->detected_status)->toBe('awaiting_response');
});

it('hides mailbox credentials from connection pages', function () {
    $owner = User::factory()->create();
    $mailbox = MailboxConnection::create(['user_id' => $owner->id, 'provider' => 'imap', 'address' => 'owner@example.com', 'credentials' => ['password' => 'private-credential'], 'state' => 'connected']);
    $this->actingAs($owner)->get('/settings/mailboxes')->assertDontSee('private-credential');
    expect($mailbox->getRawOriginal('credentials'))->not->toContain('private-credential');
});
