<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'speaker_id',
        'evaluator_user_id',
        'notes',
    ];

    /**
     * The meeting this evaluation belongs to.
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * The speaker being evaluated (meeting_speakers record).
     */
    public function speaker(): BelongsTo
    {
        return $this->belongsTo(MeetingSpeaker::class, 'speaker_id');
    }

    /**
     * The user who is the evaluator.
     */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_user_id');
    }
}
