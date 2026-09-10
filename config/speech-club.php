<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Club Roles
    |--------------------------------------------------------------------------
    |
    | These are Spatie role names that are considered "club roles".
    | A user with any of these roles is considered club-scoped and belongs
    | to exactly one club.
    |
    */
    'club_roles' => [
        'President',
        'VP Education',
        'VP Membership',
        'VP Public Relations',
        'Secretary',
        'Treasurer',
        'Sergeant at Arms',
        'Member',
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Roles
    |--------------------------------------------------------------------------
    |
    | These are Spatie role names that are considered "global" (non-club) roles.
    | Users with these roles can be assigned to multiple clubs.
    |
    */
    'global_roles' => [
        'Super Admin',
        'Admin',
        'Global Viewer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Attendance Statuses
    |--------------------------------------------------------------------------
    */
    'attendance_statuses' => [
        'present' => 'Present',
        'absent'  => 'Absent',
        'late'    => 'Late',
        'excused' => 'Excused',
    ],

    /*
    |--------------------------------------------------------------------------
    | Meeting Statuses
    |--------------------------------------------------------------------------
    */
    'meeting_statuses' => [
        'draft'     => 'Draft',
        'scheduled' => 'Scheduled',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Statuses
    |--------------------------------------------------------------------------
    */
    'user_statuses' => [
        'active'    => 'Active',
        'inactive'  => 'Inactive',
        'suspended' => 'Suspended',
    ],

];
