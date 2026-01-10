<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\AI\Enums\ProviderModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiAiChatModelsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['models' => ProviderModel::toVuetifyOptions()]);
    }
}
