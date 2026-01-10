<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateEmailVerificationTokenAction
{
    public function handle(User $user): void
    {
        DB::table('email_verification_tokens')->updateOrInsert(
            [
                'email' => $user->email,
            ],
            [
                'token' => Str::random(32),
                'created_at' => now(),
            ]
        );
    }
}
