<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SendPasswordResetEmailRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
        //
    }

    public function logout(): RedirectResponse
    {
        $this->auth->logout();

        return redirect()
            ->route('home')
            ->with('info', 'You have been logged out');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->auth->login(
            email: $request->input('email'),
            password: $request->input('password'),
            remember: $request->boolean('remember'),
        );

        if ($user) {
            return response()->json(compact('user'));
        }

        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }

    /**
     * @throws ValidatorException
     */
    public function register(AuthRegisterRequest $request): JsonResponse
    {
        $user = $this->auth->register(
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password'),
        );

        if (! $user) {
            return response()->json([
                'message' => 'Unable to register user',
            ], 500);
        }

        return response()->json([
            'user' => $user,
        ], Response::HTTP_CREATED);
    }

    public function verifyEmail(string $token): RedirectResponse
    {
        $user = $this->auth->verifyEmail($token);

        $redirectRoute = config('auth.email_verification.redirect_to');

        if (! $user) {
            return redirect()
                ->to($redirectRoute)
                ->with('error', 'Invalid token');
        }

        return redirect()
            ->to($redirectRoute)
            ->with('success', 'Email verified');
    }

    public function sendResetPasswordEmail(SendPasswordResetEmailRequest $request): Response
    {
        $sent = $this->auth->sendPasswordResetEmail(
            email: $request->input('email'),
        );

        return response('OK');
    }

    public function resetPassword(string $token): RedirectResponse
    {
        $user = $this->auth->validatePasswordResetToken($token);

        if ($user) {
            return redirect()
                ->route('home')
                ->with('password-reset-token', $token)
                ->with('password-reset-email', $user->email);
        }

        return redirect()
            ->route('home')
            ->with('error', 'Invalid password reset token');
    }

    public function updatePassword(UpdatePasswordRequest $request): Response
    {
        session()->forget([
            'password-reset-token',
            'password-reset-email',
        ]);

        $user = $this->auth->updatePassword(
            token: $request->input('token'),
            email: $request->input('email'),
            password: $request->input('password'),
        );

        if (! $user) {
            return response('Invalid token', 401);
        }

        return response('OK');
    }
}
