<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @test
     */
    public function it_should_login_a_user_with_valid_credentials(): void
    {
        $this->assertNull(auth()->user());

        /** @var User $user */
        $password = 'password';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => $password,
            'remember' => false,
        ]);

        $response->assertOk();

        $loggedInUser = auth()->user();

        $this->assertNotNull($loggedInUser);

        $this->assertSame($user->id, $loggedInUser->id);
    }

    /**
     * @test
     */
    public function it_should_not_login_a_user_with_invalid_credentials(): void
    {
        $this->assertNull(auth()->user());

        /** @var User $user */
        $password = 'password';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'invalid-password',
            'remember' => false,
        ]);

        $response->assertStatus(401);

        $this->assertNull(auth()->user());
    }

    /**
     * @test
     */
    public function it_should_logout_a_user(): void
    {
        /** @var User $user */
        $password = 'password';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $this->actingAs($user);

        $this->assertNotNull(auth()->user());

        $response = $this->get(route('auth.logout'));

        $response->assertRedirect(route('home'));

        $this->assertNull(auth()->user());
    }
}
