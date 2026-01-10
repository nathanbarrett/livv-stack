<?php

declare(strict_types=1);

use App\Models\AiChatAttachment;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\LazilyRefreshDatabase::class);

describe('AI Chat Attachments', function () {
    beforeEach(function () {
        Storage::fake('chat_attachments');
    });

    test('user can upload an attachment', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);

        $response = $this->actingAs($user)->postJson(route('api.chat.attachments.store'), [
            'file' => $file,
            'session_id' => $session->id,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        expect($response->json('attachment.original_filename'))->toBe('test.jpg');
        expect($response->json('attachment.mime_type'))->toBe('image/jpeg');

        $this->assertDatabaseHas('ai_chat_attachments', [
            'ai_chat_session_id' => $session->id,
            'original_filename' => 'test.jpg',
        ]);
    });

    test('uploaded file is stored in chat_attachments disk', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);
        $file = UploadedFile::fake()->image('photo.png', 200, 200);

        $response = $this->actingAs($user)->postJson(route('api.chat.attachments.store'), [
            'file' => $file,
            'session_id' => $session->id,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $attachment = AiChatAttachment::first();
        Storage::disk('chat_attachments')->assertExists($attachment->path);
    });

    test('user can upload document attachment', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->postJson(route('api.chat.attachments.store'), [
            'file' => $file,
            'session_id' => $session->id,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        expect($response->json('attachment.original_filename'))->toBe('document.pdf');
        expect($response->json('attachment.mime_type'))->toBe('application/pdf');
    });

    test('user cannot upload to other users session', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $userA->id]);
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($userB)->postJson(route('api.chat.attachments.store'), [
            'file' => $file,
            'session_id' => $session->id,
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('user can view their unattached attachment', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);
        $attachment = AiChatAttachment::factory()->unattached()->create([
            'ai_chat_session_id' => $session->id,
        ]);

        $response = $this->actingAs($user)->getJson(route('api.chat.attachments.show', $attachment));

        $response->assertOk();

        expect($response->json('attachment.id'))->toBe($attachment->id);
    });

    test('user can view attachment from their session message', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);
        $message = AiChatMessage::factory()->create(['ai_chat_session_id' => $session->id]);
        $attachment = AiChatAttachment::factory()->create([
            'ai_chat_message_id' => $message->id,
            'ai_chat_session_id' => null,
        ]);

        $response = $this->actingAs($user)->getJson(route('api.chat.attachments.show', $attachment));

        $response->assertOk();
    });

    test('user cannot view attachment from other users session', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $userA->id]);
        $attachment = AiChatAttachment::factory()->unattached()->create([
            'ai_chat_session_id' => $session->id,
        ]);

        $response = $this->actingAs($userB)->getJson(route('api.chat.attachments.show', $attachment));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('user can delete their unattached attachment', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);
        $attachment = AiChatAttachment::factory()->unattached()->create([
            'ai_chat_session_id' => $session->id,
        ]);

        $response = $this->actingAs($user)->deleteJson(route('api.chat.attachments.destroy', $attachment));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('ai_chat_attachments', ['id' => $attachment->id]);
    });

    test('user cannot delete attachment from other users session', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $userA->id]);
        $attachment = AiChatAttachment::factory()->unattached()->create([
            'ai_chat_session_id' => $session->id,
        ]);

        $response = $this->actingAs($userB)->deleteJson(route('api.chat.attachments.destroy', $attachment));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('file is required for upload', function () {
        $user = User::factory()->create();
        $session = AiChatSession::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson(route('api.chat.attachments.store'), [
            'session_id' => $session->id,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['file']);
    });

    test('session_id is required for upload', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($user)->postJson(route('api.chat.attachments.store'), [
            'file' => $file,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['session_id']);
    });

    test('unauthenticated user cannot upload attachments', function () {
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->postJson(route('api.chat.attachments.store'), [
            'file' => $file,
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
})->group('ai-chat', 'ai-chat-attachments');
