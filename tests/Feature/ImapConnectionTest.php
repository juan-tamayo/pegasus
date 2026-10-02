<?php

use App\Models\MailboxConnection;
use App\Models\User;
use App\Services\Mail\ImapConnectionTester;

it('validates and stores a tested imap mailbox without exposing its password', function () {
    $owner = User::factory()->create();
    $tester = Mockery::mock(ImapConnectionTester::class);
    $tester->shouldReceive('test')->once()->andReturnNull();
    app()->instance(ImapConnectionTester::class, $tester);
    $this->actingAs($owner)->post('/settings/mailboxes/imap', ['provider' => 'namecheap', 'address' => 'owner@custom.example', 'username' => 'owner@custom.example', 'password' => 'private-mail-password', 'host' => 'mail.privateemail.com', 'port' => 993, 'encryption' => 'ssl'])->assertRedirect('/settings/mailboxes');
    $mailbox = MailboxConnection::first();
    expect($mailbox->state)->toBe('connected');
    expect($mailbox->getRawOriginal('credentials'))->not->toContain('private-mail-password');
});

it('rejects an unapproved host before connecting', function () {
    $this->actingAs(User::factory()->create())->post('/settings/mailboxes/imap', ['provider' => 'imap', 'address' => 'a@example.com', 'username' => 'a', 'password' => 'pass', 'host' => 'localhost', 'port' => 993, 'encryption' => 'ssl'])->assertSessionHasErrors('host');
    expect(MailboxConnection::count())->toBe(0);
});

it('disconnects only the owners selected mailbox', function () {
    $owner = User::factory()->create();
    $a = MailboxConnection::create(['user_id' => $owner->id, 'provider' => 'imap', 'address' => 'a@example.com', 'state' => 'connected', 'credentials' => ['password' => 'secret']]);
    $b = MailboxConnection::create(['user_id' => $owner->id, 'provider' => 'gmail', 'address' => 'b@example.com', 'state' => 'connected']);
    $this->actingAs(User::factory()->create())->delete('/settings/mailboxes/'.$a->id)->assertNotFound();
    $this->actingAs($owner)->delete('/settings/mailboxes/'.$a->id)->assertRedirect();
    expect($a->fresh()->credentials)->toBeNull();
    expect($a->fresh()->generation)->toBe(2);
    expect($b->fresh()->state)->toBe('connected');
});
