<?php

use App\Models\User;

it('keeps public registration closed', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register', ['name' => 'Stranger', 'email' => 'stranger@example.com', 'password' => 'password'])->assertNotFound();
    expect(User::count())->toBe(0);
});
