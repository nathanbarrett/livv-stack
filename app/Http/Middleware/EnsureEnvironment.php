<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class EnsureEnvironment
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (SymfonyResponse)  $next
     */
    public function handle(Request $request, Closure $next, string ...$environments): SymfonyResponse
    {
        if (! in_array(config('app.env'), $environments, true)) {
            abort(Response::HTTP_FORBIDDEN, 'This route is not available in this environment.');
        }

        return $next($request);
    }
}
