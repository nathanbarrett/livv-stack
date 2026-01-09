<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KanbanTaskPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KanbanTask extends Model
{
    /** @use HasFactory<\Database\Factories\KanbanTaskFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'priority' => KanbanTaskPriority::class,
        ];
    }

    /**
     * @return BelongsTo<KanbanColumn, $this>
     */
    public function column(): BelongsTo
    {
        return $this->belongsTo(KanbanColumn::class, 'kanban_column_id');
    }
}
