<?php

declare(strict_types=1);

use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

uses(\Illuminate\Foundation\Testing\LazilyRefreshDatabase::class);

test('it should register a user with valid data', function () {
    /** @var TestCase $this */
    $newUser = registerNewUser();

    $this->assertDatabaseHas('users', [
        'name' => $newUser->name,
        'email' => $newUser->email,
    ]);

    // user is auto logged in after registration
    $this->assertAuthenticated();
});

test('it should verify an email address with a valid token', function () {
    /** @var TestCase $this */
    /** @var User $newUser */
    $newUser = User::factory()->make();

    $this->assertDatabaseMissing('email_verification_tokens', [
        'email' => $newUser->email,
    ]);

    $newUser = registerNewUser($newUser);

    Mail::assertSent(VerifyEmail::class, function (VerifyEmail $mail) use ($newUser) {
        return $mail->hasTo($newUser->email);
    });

    $token = DB::table('email_verification_tokens')
        ->where('email', $newUser->email)
        ->select(['token'])
        ->first()
        ->token;

    // go to the link in the email
    $response = $this->get(route('auth.verify-email', [
        'token' => $token,
    ]));

    $response->assertRedirectToRoute('home');

    $this->assertDatabaseMissing('email_verification_tokens', [
        'email' => $newUser->email,
    ]);

    expect($newUser->fresh()->email_verified_at)->not->toBeNull();
});

test('it should not verify an email address with an expired token', function () {
    /** @var TestCase $this */
    $tokenExpirationSeconds = 60 * 60 * 24;
    config(['auth.email_verification.token_expiration' => $tokenExpirationSeconds]);

    $this->assertGuest();

    $newUser = User::factory()->make();

    $this->assertDatabaseMissing('email_verification_tokens', [
        'email' => $newUser->email,
    ]);

    $newUser = registerNewUser($newUser);

    Mail::assertSent(VerifyEmail::class, function (VerifyEmail $mail) use ($newUser) {
        return $mail->hasTo($newUser->email);
    });

    $token = DB::table('email_verification_tokens')
        ->where('email', $newUser->email)
        ->select(['token'])
        ->first()
        ->token;

    $this->travelTo(now()->addSeconds($tokenExpirationSeconds + 1));

    // go to the link in the email
    $response = $this->get(route('auth.verify-email', [
        'token' => $token,
    ]));

    $response->assertRedirectToRoute('home')
        ->assertSessionHas('error', 'Invalid token');

    $this->assertDatabaseMissing('email_verification_tokens', [
        'email' => $newUser->email,
    ]);

    expect($newUser->fresh()->email_verified_at)->toBeNull();
});
