<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DemoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
