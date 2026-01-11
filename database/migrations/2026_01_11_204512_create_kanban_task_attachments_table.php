<?php

declare(strict_types=1);

use App\Models\KanbanTask;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanban_task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(KanbanTask::class)->constrained()->cascadeOnDelete();
            $table->string('filename', 100);
            $table->string('original_filename', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->string('disk', 50);
            $table->string('path', 500);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanban_task_attachments');
    }
};
