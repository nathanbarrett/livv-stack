<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHP-Unit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function getPasswordResetToken(User $user): string
{
    return DB::table('password_reset_tokens')
        ->where('email', $user->email)
        ->select(['token'])
        ->first()
        ->token;
}

function registerNewUser(?User $nonPersisted = null): User
{
    /** @var TestCase $testCase */
    $testCase = test();

    Mail::fake();

    config(['auth.email_verification.enabled' => true]);

    $user = $nonPersisted ?? User::factory()->make();

    $response = $testCase->postJson(route('auth.register'), [
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'password',
    ]);
    $response->assertStatus(Response::HTTP_CREATED);

    return User::query()
        ->where('email', $user->email)
        ->where('email_verified_at', null)
        ->where('name', $user->name)
        ->firstOrFail();
}
