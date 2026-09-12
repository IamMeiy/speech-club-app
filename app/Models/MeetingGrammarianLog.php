<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingGrammarianLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'user_id',
        'word_of_day_count',
        'good_phrases',
        'awkward_phrases',
        'notes',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
