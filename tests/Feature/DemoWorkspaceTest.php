<?php

use App\Models\JobApplication;
use App\Models\User;

it('does not expose the sample workspace when disabled', function () {
    $this->get('/demo')->assertNotFound();
});

it('opens an explicitly enabled local sample workspace with fictional records', function () {
    config(['pegasus.demo_enabled' => true]);
    app()->detectEnvironment(fn () => 'local');
    $this->get('/demo')->assertRedirect('/applications');
    expect(JobApplication::where('is_demo', true)->count())->toBe(6);
    expect(User::where('is_demo', true)->count())->toBe(1);
});

it('never enables the public sample login in production', function () {
    config(['pegasus.demo_enabled' => true]);
    app()->detectEnvironment(fn () => 'production');
    $this->get('/demo')->assertNotFound();
    expect(User::count())->toBe(0);
});
