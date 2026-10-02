<?php

use App\Models\MailboxConnection;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;

it('shows an actionable message when google is not configured', function () {
    $this->actingAs(User::factory()->create())->get('/settings/mailboxes/google/connect')->assertRedirect('/settings/mailboxes')->assertSessionHas('message');
});

it('binds google consent to the authenticated connection intent', function () {
    $owner = User::factory()->create();
    $this->actingAs($owner)->get('/settings/gmail/callback')->assertRedirect('/settings/mailboxes');
    expect(MailboxConnection::count())->toBe(0);
});

it('accepts a verified authorized mailbox and encrypts its oauth tokens', function () {
    $owner = User::factory()->create();
    $google = (new GoogleUser)->setRaw(['sub' => 'google-123', 'email_verified' => true])->map(['id' => 'google-123', 'email' => 'mailbox@example.com', 'name' => 'Mailbox']);
    $google->token = 'secret-access';
    $google->refreshToken = 'secret-refresh';
    $google->expiresIn = 3600;
    $google->approvedScopes = ['https://www.googleapis.com/auth/gmail.readonly'];
    Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
    Socialite::shouldReceive('redirectUrl')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($google);
    $this->actingAs($owner)->withSession(['google_intent' => ['purpose' => 'mailbox', 'user_id' => $owner->id]])->get('/settings/gmail/callback')->assertRedirect('/settings/mailboxes');
    $mailbox = MailboxConnection::first();
    expect($mailbox->address)->toBe('mailbox@example.com');
    expect($mailbox->getRawOriginal('credentials'))->not->toContain('secret-refresh');
});

it('rejects oauth responses without read permission or refresh access', function () {
    $owner = User::factory()->create();
    $google = (new GoogleUser)->setRaw(['email_verified' => true])->map(['id' => 'subject', 'email' => 'mail@example.com']);
    $google->token = 'token';
    $google->approvedScopes = [];
    Socialite::shouldReceive('driver')->andReturnSelf();
    Socialite::shouldReceive('redirectUrl')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($google);
    $this->actingAs($owner)->withSession(['google_intent' => ['purpose' => 'mailbox', 'user_id' => $owner->id]])->get('/settings/gmail/callback')->assertSessionHas('message');
    expect(MailboxConnection::count())->toBe(0);
});
