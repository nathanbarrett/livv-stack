<?php

namespace App\Http\Middleware;

use App\Enums\FlashLocation;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Keep in sync with AppPageProps in inertia.ts
        return array_merge(parent::share($request), [
            'appName' => config('app.name'),
            'csrfToken' => csrf_token(),

            'flash.success' => fn () => $request->session()->get('success'),
            'flash.error' => fn () => $request->session()->get('error'),
            'flash.info' => fn () => $request->session()->get('info'),
            'flash.warning' => fn () => $request->session()->get('warning'),
            'flash.location' => fn () => $request->session()->get(FlashLocation::sessionKey()),

            'passwordResetToken' => fn () => $request->session()->get('password-reset-token'),
            'passwordResetEmail' => fn () => $request->session()->get('password-reset-email'),

            'auth.user' => fn () => $request->user()
                ? $request->user()->toArray()
                : null,
        ]);
    }
}
