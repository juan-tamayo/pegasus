<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class ProvisionOwner extends Command
{
    protected $signature = 'pegasus:owner {email} {--name=Owner} {--reset : Reset the existing owner password}';

    protected $description = 'Privately provision or reset the Pegasus owner';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        $validator = Validator::make(['email' => $email], ['email' => 'required|email|max:254']);
        if ($validator->fails()) {
            $this->error('Enter a valid owner email.');

            return self::FAILURE;
        }
        if (User::where('email', $email)->where('is_demo', true)->exists()) {
            $this->error('Choose an owner email separate from the sample workspace.');

            return self::FAILURE;
        }
        $existing = User::where('is_demo', false)->first();
        if ($existing && (! $this->option('reset') || $existing->email !== $email)) {
            $this->error('An owner already exists. Use --reset with that owner email to reset the password.');

            return self::FAILURE;
        }
        $password = $this->secret('Password (at least 12 characters)');
        $confirmation = $this->secret('Confirm password');
        if (! is_string($password) || strlen($password) < 12 || $password !== $confirmation) {
            $this->error('Passwords must match and contain at least 12 characters.');

            return self::FAILURE;
        }
        User::updateOrCreate(['email' => $email], ['name' => $this->option('name'), 'password' => $password, 'email_verified_at' => now()]);
        $this->info('Owner ready. Sign in with this email and password.');

        return self::SUCCESS;
    }
}
