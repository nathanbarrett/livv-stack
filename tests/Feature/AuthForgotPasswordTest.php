<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\ForgotPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthForgotPasswordTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @test
     */
    public function it_should_reset_a_password_for_an_existing_user(): void
    {
        Mail::fake();

        $forgottenPassword = 'password';
        $user = User::factory()->create([
            'password' => bcrypt($forgottenPassword),
        ]);

        $this->assertNull(auth()->user());

        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'invalid-password',
        ]);
        $response->assertStatus(401);

        $response = $this->postJson(route('auth.forgot-password'), [
            'email' => $user->email,
        ]);

        $response->assertOk();

        Mail::assertSent(ForgotPassword::class, function (ForgotPassword $mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $token = $this->getPasswordResetToken($user);
        // go to the link in the email
        $response = $this->get(route('auth.reset-password', [
            'token' => $token,
        ]));

        $response->assertRedirectToRoute('home')
            ->assertSessionHas('password-reset-token', $token)
            ->assertSessionHas('password-reset-email', $user->email);

        $updatedPassword = 'new-password';
        $response = $this->postJson(route('auth.update-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $updatedPassword,
        ]));
        $response->assertOk();

        // auto logs in user
        $this->assertNotNull(auth()->user());

        auth()->logout();

        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => $updatedPassword,
        ]);
        $response->assertOk();

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);

        // auto logs in user
        $this->assertSame($user->id, auth()->user()->id);
    }

    /**
     * @test
     */
    public function it_should_not_reset_a_password_for_an_expired_token(): void
    {
        Mail::fake();

        $tokenExpirationMinutes = 60 * 24;
        config(['auth.passwords.users.expire' => $tokenExpirationMinutes]);

        $forgottenPassword = 'password';
        $user = User::factory()->create([
            'password' => bcrypt($forgottenPassword),
        ]);

        $this->assertNull(auth()->user());

        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'invalid-password',
        ]);
        $response->assertStatus(401);

        $response = $this->postJson(route('auth.forgot-password'), [
            'email' => $user->email,
        ]);

        $response->assertOk();

        Mail::assertSent(ForgotPassword::class, function (ForgotPassword $mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $token = $this->getPasswordResetToken($user);
        // go to the link in the email
        $response = $this->get(route('auth.reset-password', [
            'token' => $token,
        ]));

        $response->assertRedirectToRoute('home')
            ->assertSessionHas('password-reset-token', $token)
            ->assertSessionHas('password-reset-email', $user->email);

        $this->travelTo(now()->addMinutes($tokenExpirationMinutes + 1));

        $updatedPassword = 'new-password';
        $response = $this->postJson(route('auth.update-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $updatedPassword,
        ]));
        $response->assertStatus(401);

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);

        $this->assertNull(auth()->user());
    }

    private function getPasswordResetToken(User $user): string
    {
        return DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->select(['token'])
            ->first()
            ->token;
    }
}
