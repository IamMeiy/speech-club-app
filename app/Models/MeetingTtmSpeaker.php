<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingTtmSpeaker extends Model
{
    use HasFactory;

    protected $table = 'meeting_ttm_speakers';

    protected $fillable = [
        'meeting_id',
        'user_id',
        'slot',
        'topic',
        'duration',
        'notes',
    ];

    /**
     * The meeting this TTM speaker belongs to.
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * The user who is the TTM speaker.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted timing duration for this Table Topics speech.
     */
    public function formattedTiming(): string
    {
        return ! empty($this->duration) ? $this->duration : '1-2 mins';
    }
}
