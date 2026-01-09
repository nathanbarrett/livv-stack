<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanBoard extends Model
{
    /** @use HasFactory<\Database\Factories\KanbanBoardFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<KanbanColumn, $this>
     */
    public function columns(): HasMany
    {
        return $this->hasMany(KanbanColumn::class)->orderBy('position');
    }
}
