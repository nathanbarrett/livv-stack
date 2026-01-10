<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\Response;

uses(\Illuminate\Foundation\Testing\LazilyRefreshDatabase::class);

describe('AI Chat Models', function () {
    test('user can list available models', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('api.chat.models.index'));

        $response->assertOk();

        expect($response->json('models'))->toBeArray();
    });

    test('models are grouped by provider', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('api.chat.models.index'));

        $response->assertOk();

        $models = $response->json('models');

        expect($models)->toBeArray();

        if (count($models) > 0) {
            $firstGroup = $models[0];
            expect($firstGroup)->toHaveKeys(['provider', 'providerLabel', 'enabled', 'models']);
            expect($firstGroup['models'])->toBeArray();

            if (count($firstGroup['models']) > 0) {
                $firstModel = $firstGroup['models'][0];
                expect($firstModel)->toHaveKeys(['value', 'label']);
            }
        }
    });

    test('each provider group has enabled flag', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('api.chat.models.index'));

        $response->assertOk();

        foreach ($response->json('models') as $group) {
            expect($group)->toHaveKey('enabled');
            expect($group['enabled'])->toBeBool();
        }
    });

    test('only text models are returned', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('api.chat.models.index'));

        $response->assertOk();

        $allModels = collect($response->json('models'))
            ->flatMap(fn ($group) => $group['models'])
            ->pluck('value')
            ->all();

        // Text models should not include embedding, image generation, or TTS models
        foreach ($allModels as $modelValue) {
            expect($modelValue)->not->toContain('embedding');
            expect($modelValue)->not->toContain('dall-e');
            expect($modelValue)->not->toContain('tts');
        }
    });

    test('unauthenticated user cannot list models', function () {
        $response = $this->getJson(route('api.chat.models.index'));

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
})->group('ai-chat', 'ai-chat-models');
