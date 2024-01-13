<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @test
     */
    public function it_should_register_a_user_with_valid_data(): void
    {
        $newUser = $this->registerNewUser();

        $this->assertDatabaseHas('users', [
            'name' => $newUser->name,
            'email' => $newUser->email,
        ]);

        // user is auto logged in after registration
        $this->assertNotNull(auth()->user());
    }

    /**
     * @test
     */
    public function it_should_verify_an_email_address_with_a_valid_token(): void
    {
        /** @var User $newUser */
        $newUser = User::factory()->make();

        $this->assertDatabaseMissing('email_verification_tokens', [
            'email' => $newUser->email,
        ]);

        $newUser = $this->registerNewUser($newUser);

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

        $this->assertNotNull($newUser->fresh()->email_verified_at);
    }

    /**
     * @test
     */
    public function it_should_not_verify_an_email_address_with_an_expired_token(): void
    {
        $tokenExpirationSeconds = 60 * 60 * 24;
        config(['auth.email_verification.token_expiration' => $tokenExpirationSeconds]);

        $this->assertNull(auth()->user());

        $newUser = User::factory()->make();

        $this->assertDatabaseMissing('email_verification_tokens', [
            'email' => $newUser->email,
        ]);

        $newUser = $this->registerNewUser($newUser);

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

        $this->assertNull($newUser->fresh()->email_verified_at);
    }

    private function registerNewUser(?User $nonPersisted = null): User
    {
        Mail::fake();

        config(['auth.email_verification.enabled' => true]);

        $user = $nonPersisted ?? User::factory()->make();

        $this->postJson(route('auth.register'), [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'password',
        ]);

        return User::query()
            ->where('email', $user->email)
            ->where('email_verified_at', null)
            ->where('name', $user->name)
            ->firstOrFail();
    }
}
