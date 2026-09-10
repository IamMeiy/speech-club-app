<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $chola   = Club::where('code', 'chola')->first();
        $chera   = Club::where('code', 'chera')->first();
        $pandiya = Club::where('code', 'pandiya')->first();

        // -----------------------------------------------------------------------
        // Super Admin
        // -----------------------------------------------------------------------
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@speechclub.local'],
            [
                'name'     => 'System Administrator',
                'password' => Hash::make('password'),
                'phone'    => '+91 98000 00001',
                'status'   => 'active',
            ]
        );
        $superAdmin->assignRole('Super Admin');

        // -----------------------------------------------------------------------
        // Admin (Global User)
        // -----------------------------------------------------------------------
        $admin = User::firstOrCreate(
            ['email' => 'admin@speechclub.local'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('password'),
                'phone'    => '+91 98000 00002',
                'status'   => 'active',
            ]
        );
        $admin->assignRole('Admin');
        // Assign to Chola, Chera, Pandiya
        if ($chola)   $admin->clubs()->syncWithoutDetaching([$chola->id]);
        if ($chera)   $admin->clubs()->syncWithoutDetaching([$chera->id]);
        if ($pandiya) $admin->clubs()->syncWithoutDetaching([$pandiya->id]);

        // -----------------------------------------------------------------------
        // Chola Club Users
        // -----------------------------------------------------------------------
        if ($chola) {
            $arun = User::firstOrCreate(
                ['email' => 'arun@speechclub.local'],
                ['name' => 'Arun Kumar', 'password' => Hash::make('password'), 'phone' => '+91 98000 00010', 'status' => 'active']
            );
            $arun->assignRole('President');
            $arun->clubs()->syncWithoutDetaching([$chola->id]);

            $kumar = User::firstOrCreate(
                ['email' => 'kumar@speechclub.local'],
                ['name' => 'Kumar Raj', 'password' => Hash::make('password'), 'phone' => '+91 98000 00011', 'status' => 'active']
            );
            $kumar->assignRole('VP Education');
            $kumar->clubs()->syncWithoutDetaching([$chola->id]);

            $priya = User::firstOrCreate(
                ['email' => 'priya@speechclub.local'],
                ['name' => 'Priya Devi', 'password' => Hash::make('password'), 'phone' => '+91 98000 00012', 'status' => 'active']
            );
            $priya->assignRole('Secretary');
            $priya->clubs()->syncWithoutDetaching([$chola->id]);

            $suresh = User::firstOrCreate(
                ['email' => 'suresh@speechclub.local'],
                ['name' => 'Suresh Babu', 'password' => Hash::make('password'), 'phone' => '+91 98000 00013', 'status' => 'active']
            );
            $suresh->assignRole('Member');
            $suresh->clubs()->syncWithoutDetaching([$chola->id]);

            $meena = User::firstOrCreate(
                ['email' => 'meena@speechclub.local'],
                ['name' => 'Meena Raj', 'password' => Hash::make('password'), 'phone' => '+91 98000 00014', 'status' => 'active']
            );
            $meena->assignRole('Member');
            $meena->clubs()->syncWithoutDetaching([$chola->id]);
        }

        // -----------------------------------------------------------------------
        // Chera Club Users
        // -----------------------------------------------------------------------
        if ($chera) {
            $ravi = User::firstOrCreate(
                ['email' => 'ravi@speechclub.local'],
                ['name' => 'Ravi Shankar', 'password' => Hash::make('password'), 'phone' => '+91 98000 00020', 'status' => 'active']
            );
            $ravi->assignRole('President');
            $ravi->clubs()->syncWithoutDetaching([$chera->id]);

            $karthik = User::firstOrCreate(
                ['email' => 'karthik@speechclub.local'],
                ['name' => 'Karthik Raja', 'password' => Hash::make('password'), 'phone' => '+91 98000 00021', 'status' => 'active']
            );
            $karthik->assignRole('Member');
            $karthik->clubs()->syncWithoutDetaching([$chera->id]);
        }

        $this->command->info('Demo users seeded successfully.');
        $this->command->table(
            ['Email', 'Role', 'Club(s)'],
            [
                ['superadmin@speechclub.local', 'Super Admin', 'All'],
                ['admin@speechclub.local',      'Admin',       'Chola, Chera, Pandiya'],
                ['arun@speechclub.local',       'President',   'Chola'],
                ['kumar@speechclub.local',      'VP Education','Chola'],
                ['priya@speechclub.local',      'Secretary',   'Chola'],
                ['suresh@speechclub.local',     'Member',      'Chola'],
                ['meena@speechclub.local',      'Member',      'Chola'],
                ['ravi@speechclub.local',       'President',   'Chera'],
                ['karthik@speechclub.local',    'Member',      'Chera'],
            ]
        );
        $this->command->info('Password for all demo users: password');
    }
}
