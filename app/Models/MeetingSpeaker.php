<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MeetingSpeaker extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'user_id',
        'slot',
        'speech_type',
        'project',
        'topic',
        'duration',
        'notes',
    ];

    /**
     * The meeting this speaker belongs to.
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * The user who is the speaker.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The evaluation assigned to this speaker.
     */
    public function evaluation(): HasOne
    {
        return $this->hasOne(MeetingEvaluation::class, 'speaker_id');
    }
}
