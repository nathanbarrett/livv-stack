<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreatePasswordResetTokenAction
{
    public function handle(User $user): string
    {
        $token = Str::random(32);
        DB::table('password_reset_tokens')->updateOrInsert(
            [
                'email' => $user->email,
            ],
            [
                'token' => $token,
                'created_at' => now(),
            ]
        );

        return $token;
    }
}
