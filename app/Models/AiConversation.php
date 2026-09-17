<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'club_id',
        'title',
        'mode',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'conversation_id')->orderBy('created_at', 'asc');
    }

    /**
     * Scope for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Group label for history sidebar (Today, Yesterday, Previous 7 Days, Older).
     */
    public function getHistoryGroupAttribute(): string
    {
        $updated = $this->updated_at;
        if ($updated->isToday()) {
            return 'Today';
        }
        if ($updated->isYesterday()) {
            return 'Yesterday';
        }
        if ($updated->greaterThanOrEqualTo(now()->subDays(7))) {
            return 'Previous 7 Days';
        }
        return 'Older';
    }

    /**
     * Mode emoji icon.
     */
    public function getModeEmojiAttribute(): string
    {
        return match ($this->mode) {
            'outline'     => '🎤',
            'tabletopics' => '🎯',
            'role'        => '📋',
            'coaching'    => '✨',
            default       => '💬',
        };
    }
}
