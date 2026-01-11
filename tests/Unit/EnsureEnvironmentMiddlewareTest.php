<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureEnvironment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

describe('EnsureEnvironment Middleware', function () {
    it('should allow request when environment matches single allowed environment', function () {
        config(['app.env' => 'local']);

        $middleware = new EnsureEnvironment;
        $request = Request::create('/test', 'GET');

        $response = $middleware->handle($request, fn () => new Response('OK'), 'local');

        expect($response->getContent())->toBe('OK');
    });

    it('should allow request when environment matches one of multiple allowed environments', function () {
        config(['app.env' => 'staging']);

        $middleware = new EnsureEnvironment;
        $request = Request::create('/test', 'GET');

        $response = $middleware->handle($request, fn () => new Response('OK'), 'local', 'staging');

        expect($response->getContent())->toBe('OK');
    });

    it('should block request with 403 when environment does not match', function () {
        config(['app.env' => 'production']);

        $middleware = new EnsureEnvironment;
        $request = Request::create('/test', 'GET');

        $middleware->handle($request, fn () => new Response('OK'), 'local', 'staging');
    })->throws(HttpException::class);

    it('should return 403 status code when environment does not match', function () {
        config(['app.env' => 'production']);

        $middleware = new EnsureEnvironment;
        $request = Request::create('/test', 'GET');

        try {
            $middleware->handle($request, fn () => new Response('OK'), 'local', 'staging');
        } catch (HttpException $e) {
            expect($e->getStatusCode())->toBe(Response::HTTP_FORBIDDEN);
            expect($e->getMessage())->toBe('This route is not available in this environment.');
        }
    });

    it('should work with single environment parameter', function () {
        config(['app.env' => 'local']);

        $middleware = new EnsureEnvironment;
        $request = Request::create('/test', 'GET');

        $response = $middleware->handle($request, fn () => new Response('OK'), 'local');

        expect($response->getStatusCode())->toBe(Response::HTTP_OK);
    });

    it('should block when given single non-matching environment', function () {
        config(['app.env' => 'production']);

        $middleware = new EnsureEnvironment;
        $request = Request::create('/test', 'GET');

        $middleware->handle($request, fn () => new Response('OK'), 'local');
    })->throws(HttpException::class);
});
