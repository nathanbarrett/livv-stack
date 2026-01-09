<?php

declare(strict_types=1);

use App\Mail\ForgotPassword;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

uses(\Illuminate\Foundation\Testing\LazilyRefreshDatabase::class);

test('it should reset a password for an existing user', function () {
    /** @var TestCase $this */
    Mail::fake();

    $forgottenPassword = 'password';
    $user = User::factory()->create([
        'password' => bcrypt($forgottenPassword),
    ]);

    $this->assertGuest();

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

    $token = getPasswordResetToken($user);
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
    $this->assertAuthenticated();

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
});

test('it should not reset a password for an expired token', function () {
    /** @var TestCase $this */
    Mail::fake();

    $tokenExpirationMinutes = 60 * 24;
    config(['auth.passwords.users.expire' => $tokenExpirationMinutes]);

    $forgottenPassword = 'password';
    $user = User::factory()->create([
        'password' => bcrypt($forgottenPassword),
    ]);

    $this->assertGuest();

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

    $token = getPasswordResetToken($user);
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

    $this->assertGuest();
});
