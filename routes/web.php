<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DemoController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| AI Agent Quick Login
|--------------------------------------------------------------------------
|
| This route allows AI agents to quickly authenticate in local/staging environments.
|
| Usage:
| 1. Query available users: User::select('id', 'name', 'email')->get()
| 2. Navigate to: /ai/login/{user_id}
| 3. You are now authenticated as that user
|
| Example: GET /ai/login/1 - logs in as user with ID 1
|
*/
Route::get('/ai/login/{user}', function (User $user) {
    auth()->login($user);

    return redirect()->route('home');
})->middleware('env:local,staging')->name('ai.login');

Route::get('/', function () {
    //    session()->now('success', 'LIVV Stack App Is Running!');
    return Inertia::render('HomeDemo');
})->name('home');

Route::get('/demo/redirect-with-flash-info', [DemoController::class, 'redirectWithFlashInfo'])
    ->name('demo.redirect-with-flash-info');

Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])
    ->name('auth.verify-email');

Route::get('/reset-password/{token}', [AuthController::class, 'resetPassword'])
    ->name('auth.reset-password');
Route::post('/reset-password', [AuthController::class, 'updatePassword'])
    ->name('auth.update-password');

Route::get('/logout', [AuthController::class, 'logout'])
    ->name('auth.logout');

Route::group(['middleware' => 'guest'], function () {
    Route::inertia('/register', 'Auth/Register')
        ->name('auth.register.home');
    Route::post('/register', [AuthController::class, 'register'])
        ->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])
        ->name('auth.login');
    Route::post('/forgot-password', [AuthController::class, 'sendResetPasswordEmail'])
        ->name('auth.forgot-password');
});

Route::get('/demo/kanban', function () {
    return Inertia::render('KanbanDemo');
})->name('kanban.demo');

Route::get('/demo/ai-chat', function () {
    return Inertia::render('Demo/AiChat');
})->name('ai-chat.demo');

Route::prefix('api')
    ->name('api.')
    ->group(base_path('routes/api.php'));
