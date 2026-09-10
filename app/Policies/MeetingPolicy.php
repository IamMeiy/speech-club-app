<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;

class MeetingPolicy
{
    /**
     * Determine whether the user can view any meetings.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('meetings.view');
    }

    /**
     * Determine whether the user can view the meeting.
     */
    public function view(User $user, Meeting $meeting): bool
    {
        if (! $user->can('meetings.view')) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->clubs()->where('clubs.id', $meeting->club_id)->exists();
    }

    /**
     * Determine whether the user can create meetings.
     */
    public function create(User $user): bool
    {
        return $user->can('meetings.create');
    }

    /**
     * Determine whether the user can update the meeting.
     */
    public function update(User $user, Meeting $meeting): bool
    {
        if (! $user->can('meetings.update')) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->clubs()->where('clubs.id', $meeting->club_id)->exists();
    }

    /**
     * Determine whether the user can delete the meeting.
     */
    public function delete(User $user, Meeting $meeting): bool
    {
        if (! $user->can('meetings.delete')) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->clubs()->where('clubs.id', $meeting->club_id)->exists();
    }

    /**
     * Determine whether the user can manage attendance.
     */
    public function manageAttendance(User $user, Meeting $meeting): bool
    {
        if (! $user->can('attendance.manage')) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->clubs()->where('clubs.id', $meeting->club_id)->exists();
    }
}
