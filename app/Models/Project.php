<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'track',
        'level',
        'min_minutes',
        'max_minutes',
        'default_duration',
        'overview',
        'objectives',
        'evaluator_notes',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'level'       => 'integer',
        'min_minutes' => 'integer',
        'max_minutes' => 'integer',
        'sort_order'  => 'integer',
        'is_active'   => 'boolean',
    ];

    public function meetingSpeakers(): HasMany
    {
        return $this->hasMany(MeetingSpeaker::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTrack($query, ?string $track)
    {
        return $track ? $query->where('track', $track) : $query;
    }

    public function scopeByLevel($query, ?int $level)
    {
        return $level ? $query->where('level', $level) : $query;
    }

    public function formattedTiming(): string
    {
        if ($this->default_duration) {
            return $this->default_duration;
        }

        return "{$this->min_minutes}-{$this->max_minutes} mins";
    }

    public function levelBadge(): string
    {
        if (! $this->level) {
            return 'General';
        }

        return "Project {$this->level}";
    }
}
