<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'meeting_role_type_id',
        'user_id',
    ];

    /**
     * The meeting this role assignment belongs to.
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * The type of meeting role (TMOD, GE, etc.).
     */
    public function roleType(): BelongsTo
    {
        return $this->belongsTo(MeetingRoleType::class, 'meeting_role_type_id');
    }

    /**
     * The user assigned to this meeting role.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
