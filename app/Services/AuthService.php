<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AuthException;
use App\Mail\ForgotPassword;
use App\Mail\VerifyEmail;
use App\Models\User;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    public function __construct(private readonly UserRepository $users)
    {
        //
    }

    public function login(string $email, string $password, bool $remember = false): ?User
    {
        $success = auth()->attempt([
            'email' => $email,
            'password' => $password,
        ], $remember);

        if ($success) {
            session()->regenerate();
        }

        return auth()->user() ?? null;
    }

    public function logout(): void
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    /**
     * @throws AuthException
     */
    public function register(string $name, string $email, string $password): ?User
    {
        try {
            $user = $this->users->create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt($password),
            ]);
        } catch (\Exception $e) {
            throw AuthException::newUserCreationException($email, $e);
        }

        auth()->login($user);
        session()->regenerate();

        if (
            config('auth.email_verification.enabled') &&
            $token = $this->users->getEmailVerificationToken($user)
        ) {
            Mail::to($user)->send(new VerifyEmail($token));
        }

        return $user;
    }

    public function verifyEmail(string $token): ?User
    {
        $result = DB::table('email_verification_tokens')
            ->where('token', $token)
            ->select(['email', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $result) {
            return null;
        }

        $tokenExpirationSeconds = (int) config('auth.email_verification.token_expiration');
        /** @var User|null $user */
        $user = $this->users->findByField('email', $result->email)->first();
        if (
            ! $user ||
            (
                $tokenExpirationSeconds &&
                Carbon::parse($result->created_at)->diffInSeconds(now()) > $tokenExpirationSeconds
            )
        ) {
            DB::table('email_verification_tokens')
                ->where('email', $result->email)
                ->delete();

            return null;
        }

        $user->email_verified_at = now();
        $user->save();

        DB::table('email_verification_tokens')
            ->where('email', $result->email)
            ->delete();

        return $user;
    }

    public function sendPasswordResetEmail(string $email): bool
    {
        $user = $this->users->findByField('email', $email)->first();

        if (! $user) {
            return false;
        }

        $token = $this->users->createPasswordResetToken($user);

        Mail::to($user)->send(new ForgotPassword($token));

        return true;
    }

    public function validatePasswordResetToken(string $token): ?User
    {
        return $this->users->getUserFromPasswordResetToken($token);
    }

    public function updatePassword(string $token, string $email, string $password): ?User
    {
        $user = $this->users->getUserFromPasswordResetToken($token);

        if (! $user || $user->email !== $email) {
            return null;
        }

        $user->password = bcrypt($password);
        $user->save();

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->delete();

        auth()->login($user);
        session()->regenerate();

        return $user;
    }
}
