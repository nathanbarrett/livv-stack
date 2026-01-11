<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class KanbanTaskAttachment extends Model
{
    protected $guarded = ['id'];

    /**
     * @return BelongsTo<KanbanTask, $this>
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(KanbanTask::class, 'kanban_task_id');
    }

    public function getUrlAttribute(): ?string
    {
        return Storage::disk($this->disk)->temporaryUrl($this->path, now()->addMinutes(30));
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }
}
