<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            ClubSeeder::class,
            MeetingRoleTypeSeeder::class,
            ProjectSeeder::class,
            DemoUsersSeeder::class,
            RealisticWorkflowSeeder::class,
        ]);
    }
}
