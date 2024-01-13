<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Prettus\Repository\Eloquent\BaseRepository;

class UserRepository extends BaseRepository
{
    public function model(): string
    {
        return User::class;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): User
    {
        /** @var User $user */
        $user = parent::create($attributes);

        if (config('auth.email_verification.enabled')) {
            $this->createEmailVerificationToken($user);
        }

        return $user;
    }

    public function createPasswordResetToken(User $user): string
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

    public function getUserFromPasswordResetToken(string $token): ?User
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

    public function getEmailVerificationToken(User $user): ?string
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

    private function createEmailVerificationToken(User $user): void
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
