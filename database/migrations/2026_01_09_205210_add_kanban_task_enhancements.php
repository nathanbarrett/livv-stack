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
        Schema::table('kanban_tasks', function (Blueprint $table) {
            $table->mediumText('implementation_plans')->nullable()->after('description');
            $table->text('implementation_notes')->nullable()->after('implementation_plans');
        });

        Schema::create('kanban_task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(KanbanTask::class, 'task_id')->constrained('kanban_tasks')->cascadeOnDelete();
            $table->foreignIdFor(KanbanTask::class, 'depends_on_task_id')->constrained('kanban_tasks')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['task_id', 'depends_on_task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanban_task_dependencies');

        Schema::table('kanban_tasks', function (Blueprint $table) {
            $table->dropColumn(['implementation_plans', 'implementation_notes']);
        });
    }
};
