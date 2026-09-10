<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // -----------------------------------------------------------------------
        // Permissions
        // -----------------------------------------------------------------------

        $permissions = [
            // Users (club-scoped)
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Global Users
            'global-users.view',
            'global-users.create',
            'global-users.update',
            'global-users.delete',

            // Clubs
            'clubs.view',
            'clubs.create',
            'clubs.update',
            'clubs.delete',

            // Meetings
            'meetings.view',
            'meetings.create',
            'meetings.update',
            'meetings.delete',

            // Meeting Roles
            'meeting-roles.manage',

            // Attendance
            'attendance.view',
            'attendance.manage',

            // Reports
            'reports.view',
            'reports.manage',

            // Roles & Permissions
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'permissions.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // -----------------------------------------------------------------------
        // Roles
        // -----------------------------------------------------------------------

        // Super Admin — full access, bypasses permission checks via Spatie's gate
        Role::firstOrCreate(['name' => 'Super Admin']);

        // Admin — global user, manages multiple clubs
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions([
            'users.view', 'users.create', 'users.update', 'users.delete',
            'global-users.view', 'global-users.create', 'global-users.update', 'global-users.delete',
            'clubs.view', 'clubs.create', 'clubs.update',
            'meetings.view', 'meetings.create', 'meetings.update', 'meetings.delete',
            'meeting-roles.manage',
            'attendance.view', 'attendance.manage',
            'reports.view', 'reports.manage',
        ]);

        // Global Viewer — read-only global access
        $globalViewer = Role::firstOrCreate(['name' => 'Global Viewer']);
        $globalViewer->syncPermissions([
            'users.view',
            'clubs.view',
            'meetings.view',
            'attendance.view',
            'reports.view',
        ]);

        // -----------------------------------------------------------------------
        // Club Roles
        // -----------------------------------------------------------------------

        // President — full club management
        $president = Role::firstOrCreate(['name' => 'President']);
        $president->syncPermissions([
            'users.view', 'users.create', 'users.update',
            'meetings.view', 'meetings.create', 'meetings.update',
            'meeting-roles.manage',
            'attendance.view', 'attendance.manage',
            'reports.view', 'reports.manage',
        ]);

        // VP Education
        $vpe = Role::firstOrCreate(['name' => 'VP Education']);
        $vpe->syncPermissions([
            'users.view',
            'meetings.view', 'meetings.create', 'meetings.update',
            'meeting-roles.manage',
            'attendance.view', 'attendance.manage',
            'reports.view',
        ]);

        // VP Membership
        $vpm = Role::firstOrCreate(['name' => 'VP Membership']);
        $vpm->syncPermissions([
            'users.view', 'users.create', 'users.update',
            'meetings.view',
            'attendance.view',
            'reports.view',
        ]);

        // VP Public Relations
        $vppr = Role::firstOrCreate(['name' => 'VP Public Relations']);
        $vppr->syncPermissions([
            'users.view',
            'meetings.view',
            'reports.view',
        ]);

        // Secretary
        $secretary = Role::firstOrCreate(['name' => 'Secretary']);
        $secretary->syncPermissions([
            'users.view',
            'meetings.view', 'meetings.create', 'meetings.update',
            'attendance.view', 'attendance.manage',
            'reports.view', 'reports.manage',
        ]);

        // Treasurer
        $treasurer = Role::firstOrCreate(['name' => 'Treasurer']);
        $treasurer->syncPermissions([
            'users.view',
            'meetings.view',
            'reports.view',
        ]);

        // Sergeant at Arms
        $saa = Role::firstOrCreate(['name' => 'Sergeant at Arms']);
        $saa->syncPermissions([
            'users.view',
            'meetings.view',
            'attendance.view', 'attendance.manage',
            'reports.view',
        ]);

        // Member — basic read access
        $member = Role::firstOrCreate(['name' => 'Member']);
        $member->syncPermissions([
            'meetings.view',
            'reports.view',
            'attendance.view',
        ]);

        $this->command->info('Roles and permissions seeded successfully.');
    }
}
