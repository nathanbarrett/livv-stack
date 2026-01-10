<?php

declare(strict_types=1);

use App\Enums\AiChatMessageRole;
use App\Models\AiChatAttachment;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\User;
use Illuminate\Http\Response;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Text\Response as TextResponse;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\Usage;

uses(\Illuminate\Foundation\Testing\LazilyRefreshDatabase::class);

function createFakeTextResponse(string $text): TextResponse
{
    return new TextResponse(
        steps: collect([]),
        text: $text,
        finishReason: FinishReason::Stop,
        toolCalls: [],
        toolResults: [],
        usage: new Usage(10, 20),
        meta: new Meta('fake-id', 'fake-model'),
        messages: collect([]),
    );
}

describe('AI Chat Messages', function () {
    test('user can send a message to their session', function () {
        // Mock Prism to return a fake AI response
        Prism::fake([
            createFakeTextResponse('Hello! How can I help you today?'),
        ]);

        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson(route('api.chat.messages.store', $session), [
            'content' => 'Hello, AI!',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        expect($response->json('user_message.content'))->toBe('Hello, AI!');
        expect($response->json('user_message.role'))->toBe(AiChatMessageRole::User->value);
        expect($response->json('assistant_message.content'))->toBe('Hello! How can I help you today?');
        expect($response->json('assistant_message.role'))->toBe(AiChatMessageRole::Assistant->value);

        $this->assertDatabaseHas('ai_chat_messages', [
            'ai_chat_session_id' => $session->id,
            'content' => 'Hello, AI!',
            'role' => AiChatMessageRole::User->value,
        ]);

        $this->assertDatabaseHas('ai_chat_messages', [
            'ai_chat_session_id' => $session->id,
            'content' => 'Hello! How can I help you today?',
            'role' => AiChatMessageRole::Assistant->value,
        ]);
    });

    test('user can send message with attachments', function () {
        // Mock Prism to return a fake AI response
        Prism::fake([
            createFakeTextResponse('I see the file you attached.'),
        ]);

        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);
        $attachment = AiChatAttachment::factory()->unattached()->create([
            'ai_chat_session_id' => $session->id,
        ]);

        $response = $this->actingAs($user)->postJson(route('api.chat.messages.store', $session), [
            'content' => 'Check this file',
            'attachment_ids' => [$attachment->id],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $messageId = $response->json('user_message.id');

        $this->assertDatabaseHas('ai_chat_attachments', [
            'id' => $attachment->id,
            'ai_chat_message_id' => $messageId,
        ]);
    });

    test('user cannot send message to other users session', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $userA->id]);

        $response = $this->actingAs($userB)->postJson(route('api.chat.messages.store', $session), [
            'content' => 'Trying to send',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('message content is required', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson(route('api.chat.messages.store', $session), [
            'content' => '',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['content']);
    });

    test('user can delete their message', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);
        $message = AiChatMessage::factory()->user()->create(['ai_chat_session_id' => $session->id]);

        $response = $this->actingAs($user)->deleteJson(route('api.chat.messages.destroy', $message));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('ai_chat_messages', ['id' => $message->id]);
    });

    test('user cannot delete message from other users session', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $userA->id]);
        $message = AiChatMessage::factory()->create(['ai_chat_session_id' => $session->id]);

        $response = $this->actingAs($userB)->deleteJson(route('api.chat.messages.destroy', $message));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('deleting message cascades to attachments', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);
        $message = AiChatMessage::factory()->user()->create(['ai_chat_session_id' => $session->id]);
        $attachment = AiChatAttachment::factory()->create([
            'ai_chat_message_id' => $message->id,
            'ai_chat_session_id' => null,
        ]);

        $response = $this->actingAs($user)->deleteJson(route('api.chat.messages.destroy', $message));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('ai_chat_messages', ['id' => $message->id]);
        $this->assertDatabaseMissing('ai_chat_attachments', ['id' => $attachment->id]);
    });

    test('unauthenticated user cannot send messages', function () {
        $session = AiChatSession::factory()->create();

        $response = $this->postJson(route('api.chat.messages.store', $session), [
            'content' => 'Hello',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
})->group('ai-chat', 'ai-chat-messages');
