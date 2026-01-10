<?php

declare(strict_types=1);

use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\User;
use Illuminate\Http\Response;

uses(\Illuminate\Foundation\Testing\LazilyRefreshDatabase::class);

describe('AI Chat Sessions', function () {
    test('user can list their sessions', function () {
        $user = User::factory()->create();
        AiChatSession::factory()->count(2)->create(['user_id' => $user->id]);
        AiChatSession::factory()->create();

        $response = $this->actingAs($user)->getJson(route('api.chat.sessions.index'));

        $response->assertOk();

        expect($response->json('sessions'))->toHaveCount(2);
    });

    test('user cannot see other users sessions', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        AiChatSession::factory()->create(['user_id' => $userA->id]);

        $response = $this->actingAs($userB)->getJson(route('api.chat.sessions.index'));

        $response->assertOk();

        expect($response->json('sessions'))->toHaveCount(0);
    });

    test('user can create a session', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('api.chat.sessions.store'), [
            'title' => 'Test Chat',
            'model' => 'claude-opus-4-5-20251101',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        expect($response->json('session.title'))->toBe('Test Chat');
        expect($response->json('session.model'))->toBe('claude-opus-4-5-20251101');

        $this->assertDatabaseHas('ai_chat_sessions', [
            'user_id' => $user->id,
            'title' => 'Test Chat',
        ]);
    });

    test('user can create session without title', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('api.chat.sessions.store'), [
            'model' => 'gpt-4o',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        expect($response->json('session.title'))->toBeNull();
        expect($response->json('session.model'))->toBe('gpt-4o');
    });

    test('user can view their session with messages', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);
        AiChatMessage::factory()->user()->create(['ai_chat_session_id' => $session->id]);
        AiChatMessage::factory()->assistant()->create(['ai_chat_session_id' => $session->id]);

        $response = $this->actingAs($user)->getJson(route('api.chat.sessions.show', $session));

        $response->assertOk();

        expect($response->json('session.id'))->toBe($session->id);
        expect($response->json('session.messages'))->toHaveCount(2);
    });

    test('user cannot view other users session', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $userA->id]);

        $response = $this->actingAs($userB)->getJson(route('api.chat.sessions.show', $session));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('user can update their session', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
        ]);

        $response = $this->actingAs($user)->patchJson(route('api.chat.sessions.update', $session), [
            'title' => 'New Title',
        ]);

        $response->assertOk();

        expect($response->json('session.title'))->toBe('New Title');

        $this->assertDatabaseHas('ai_chat_sessions', [
            'id' => $session->id,
            'title' => 'New Title',
        ]);
    });

    test('user cannot update other users session', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $userA->id]);

        $response = $this->actingAs($userB)->patchJson(route('api.chat.sessions.update', $session), [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('user can delete their session', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson(route('api.chat.sessions.destroy', $session));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('ai_chat_sessions', ['id' => $session->id]);
    });

    test('deleting session cascades to messages', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);
        $message = AiChatMessage::factory()->create(['ai_chat_session_id' => $session->id]);

        $response = $this->actingAs($user)->deleteJson(route('api.chat.sessions.destroy', $session));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('ai_chat_sessions', ['id' => $session->id]);
        $this->assertDatabaseMissing('ai_chat_messages', ['id' => $message->id]);
    });

    test('user can clear session messages', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Chat',
        ]);
        AiChatMessage::factory()->count(3)->create(['ai_chat_session_id' => $session->id]);

        $response = $this->actingAs($user)->postJson(route('api.chat.sessions.clear', $session));

        $response->assertOk();

        expect($response->json('session.title'))->toBeNull();

        $this->assertDatabaseCount('ai_chat_messages', 0);
    });

    test('unauthenticated user cannot access sessions', function () {
        $response = $this->getJson(route('api.chat.sessions.index'));

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
})->group('ai-chat', 'ai-chat-sessions');
