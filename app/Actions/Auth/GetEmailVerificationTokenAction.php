<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GetEmailVerificationTokenAction
{
    public function handle(User $user): ?string
    {
        $result = DB::table('email_verification_tokens')
            ->where('email', $user->email)
            ->select(['token', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $result) {
            return null;
        }

        $tokenExpirationSeconds = (int) config('auth.email_verification.token_expiration');
        if (
            ! $result->token ||
            (
                $tokenExpirationSeconds &&
                Carbon::parse($result->created_at)->diffInSeconds(now()) > $tokenExpirationSeconds
            )
        ) {
            DB::table('email_verification_tokens')
                ->where('email', $user->email)
                ->delete();

            return null;
        }

        return $result->token;
    }
}
