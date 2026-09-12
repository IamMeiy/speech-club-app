<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingAhCounterLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'user_id',
        'ah_count',
        'um_count',
        'er_count',
        'like_count',
        'you_know_count',
        'so_count',
        'repeats_count',
        'other_count',
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

    public function totalFillers(): int
    {
        return (int) (
            $this->ah_count +
            $this->um_count +
            $this->er_count +
            $this->like_count +
            $this->you_know_count +
            $this->so_count +
            $this->repeats_count +
            $this->other_count
        );
    }
}
