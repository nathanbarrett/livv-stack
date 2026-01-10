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
        Schema::create('kanban_task_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(KanbanTask::class)->constrained()->cascadeOnDelete();
            $table->text('note');
            $table->string('author', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanban_task_notes');
    }
};
