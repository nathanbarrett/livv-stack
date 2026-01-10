<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiChatMessageRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $ai_chat_session_id
 * @property AiChatMessageRole $role
 * @property string $content
 * @property string|null $model
 * @property array<string, mixed>|null $usage
 * @property array<string, mixed>|null $metadata
 * @property Collection<int, AiChatAttachment> $attachments
 */
class AiChatMessage extends Model
{
    /** @use HasFactory<\Database\Factories\AiChatMessageFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => AiChatMessageRole::class,
            'usage' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<AiChatSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AiChatSession::class, 'ai_chat_session_id');
    }

    /**
     * @return HasMany<AiChatAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(AiChatAttachment::class);
    }
}
