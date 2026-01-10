<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GetUserFromPasswordResetTokenAction
{
    public function handle(string $token): ?User
    {
        $result = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->select(['email', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $result) {
            return null;
        }

        $tokenExpirationMinutes = (int) config('auth.passwords.users.expire');
        if (
            ! $result->email ||
            (
                $tokenExpirationMinutes &&
                Carbon::parse($result->created_at)->diffInMinutes(now()) > $tokenExpirationMinutes
            )
        ) {
            DB::table('password_reset_tokens')
                ->where('email', $result->email)
                ->delete();

            return null;
        }

        return User::query()->firstWhere('email', $result->email);
    }
}
