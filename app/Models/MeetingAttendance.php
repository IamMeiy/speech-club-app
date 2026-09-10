<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingAttendance extends Model
{
    use HasFactory;

    protected $table = 'meeting_attendance';

    protected $fillable = [
        'meeting_id',
        'user_id',
        'status',
        'notes',
    ];

    /**
     * The meeting this attendance record belongs to.
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * The user this attendance record is for.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Available attendance statuses.
     */
    public static function statuses(): array
    {
        return [
            'present' => 'Present',
            'absent'  => 'Absent',
            'late'    => 'Late',
            'excused' => 'Excused',
        ];
    }

    /**
     * Status color classes for badges.
     */
    public function statusColor(): string
    {
        return match ($this->status) {
            'present' => 'bg-green-100 text-green-700',
            'absent'  => 'bg-red-100 text-red-700',
            'late'    => 'bg-yellow-100 text-yellow-700',
            'excused' => 'bg-gray-100 text-gray-700',
            default   => 'bg-gray-100 text-gray-700',
        };
    }
}
