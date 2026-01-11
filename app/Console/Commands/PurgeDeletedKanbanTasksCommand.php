<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\KanbanTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeDeletedKanbanTasksCommand extends Command
{
    protected $signature = 'kanban:purge-deleted-tasks';

    protected $description = 'Permanently delete kanban tasks that were soft deleted more than a year ago';

    public function handle(): void
    {
        $deletedCount = 0;
        $attachmentCount = 0;
        $oneYearAgo = now()->subYear();

        KanbanTask::onlyTrashed()
            ->where('deleted_at', '<', $oneYearAgo)
            ->with('attachments')
            ->chunkById(100, function ($tasks) use (&$deletedCount, &$attachmentCount): void {
                foreach ($tasks as $task) {
                    $this->info("Purging task id `{$task->id}`...");

                    foreach ($task->attachments as $attachment) {
                        Storage::disk($attachment->disk)->delete($attachment->path);
                        $attachmentCount++;
                    }

                    $task->forceDelete();
                    $deletedCount++;
                }
            });

        $this->comment("Purged {$deletedCount} tasks and {$attachmentCount} attachments.");
    }
}
