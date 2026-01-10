<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;

class CreateUserAction
{
    public function __construct(
        private readonly CreateEmailVerificationTokenAction $createEmailVerificationToken
    ) {}

    public function handle(string $name, string $email, string $password): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        if (config('auth.email_verification.enabled')) {
            $this->createEmailVerificationToken->handle($user);
        }

        return $user;
    }
}
