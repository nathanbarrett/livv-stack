<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Realtime\CreateTokenRequest;
use App\Services\RealtimeSessionService;
use Illuminate\Http\JsonResponse;

class ApiRealtimeTokenController extends Controller
{
    public function store(
        CreateTokenRequest $request,
        RealtimeSessionService $service
    ): JsonResponse {
        $result = $service->createEphemeralToken(
            $request->user(),
            $request->validated('mode')
        );

        return response()->json($result);
    }
}
