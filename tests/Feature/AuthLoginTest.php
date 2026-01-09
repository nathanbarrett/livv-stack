<?php

declare(strict_types=1);

use App\Models\User;
use Tests\TestCase;

uses(\Illuminate\Foundation\Testing\LazilyRefreshDatabase::class);

test('it should login a user with valid credentials', function () {
    /** @var TestCase $this */
    $this->assertGuest();

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

    $this->assertAuthenticated();

    $this->assertSame($user->id, $loggedInUser->id);
});

test('it should not login a user with invalid credentials', function () {
    /** @var TestCase $this */
    $this->assertGuest();

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

    $this->assertGuest();
});

test('it should logout a user', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $password = 'password';
    $user = User::factory()->create([
        'password' => bcrypt($password),
    ]);

    $this->actingAs($user);

    $this->assertAuthenticated();

    $response = $this->get(route('auth.logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});
