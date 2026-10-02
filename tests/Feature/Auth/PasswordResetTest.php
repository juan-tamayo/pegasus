<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('keeps public email password recovery disabled', function () {
    $this->get('/forgot-password')->assertNotFound();
    $this->post('/forgot-password', ['email' => 'owner@example.com'])->assertNotFound();
    $this->get('/reset-password/fake')->assertNotFound();
    $this->post('/reset-password', ['token' => 'fake'])->assertNotFound();
});

it('resets the existing owner privately without creating another account', function () {
    $owner = User::factory()->create(['email' => 'owner@example.com']);
    $this->artisan('pegasus:owner', ['email' => 'owner@example.com', '--reset' => true])
        ->expectsQuestion('Password (at least 12 characters)', 'new private passphrase')
        ->expectsQuestion('Confirm password', 'new private passphrase')
        ->assertSuccessful();
    expect(User::count())->toBe(1);
    expect(Hash::check('new private passphrase', $owner->fresh()->password))->toBeTrue();
});
