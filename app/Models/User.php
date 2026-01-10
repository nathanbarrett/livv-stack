<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property \Carbon\Carbon|null $email_verified_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasMany<KanbanBoard, $this>
     */
    public function kanbanBoards(): HasMany
    {
        return $this->hasMany(KanbanBoard::class);
    }

    /**
     * @return HasMany<AiChatSession, $this>
     */
    public function chatSessions(): HasMany
    {
        return $this->hasMany(AiChatSession::class)->orderByDesc('updated_at');
    }

    /**
     * @return HasMany<UserMemory, $this>
     */
    public function memories(): HasMany
    {
        return $this->hasMany(UserMemory::class);
    }
}
