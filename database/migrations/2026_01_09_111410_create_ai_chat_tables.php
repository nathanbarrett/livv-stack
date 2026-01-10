<?php

declare(strict_types=1);

use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('title', 255)->nullable();
            $table->string('model', 100)->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AiChatSession::class)->constrained()->cascadeOnDelete();
            $table->string('role', 20);
            $table->text('content');
            $table->string('model', 100)->nullable();
            $table->json('usage')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['ai_chat_session_id', 'created_at']);
        });

        Schema::create('ai_chat_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AiChatMessage::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(AiChatSession::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('filename', 100);
            $table->string('original_filename', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->string('disk', 50)->default('chat_attachments');
            $table->string('path', 500);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_attachments');
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_sessions');
    }
};
