<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('provisions one private owner with a hashed password', function () {
    $this->artisan('pegasus:owner', ['email' => 'owner@example.com', '--name' => 'Owner'])
        ->expectsQuestion('Password (at least 12 characters)', 'correct horse battery staple')
        ->expectsQuestion('Confirm password', 'correct horse battery staple')
        ->assertSuccessful();
    expect(User::count())->toBe(1);
    expect(Hash::check('correct horse battery staple', User::first()->password))->toBeTrue();
});

it('does not create a second owner', function () {
    User::factory()->create();
    $this->artisan('pegasus:owner', ['email' => 'other@example.com'])->assertFailed();
    expect(User::count())->toBe(1);
});
