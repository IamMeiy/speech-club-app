<?php

namespace Database\Seeders;

use App\Models\Club;
use Illuminate\Database\Seeder;

class ClubSeeder extends Seeder
{
    public function run(): void
    {
        $clubs = [
            [
                'name'        => 'Chola Speech Club',
                'code'        => 'chola',
                'description' => 'The Chola Speech Club, where great speakers are made.',
                'status'      => 'active',
                'meeting_day' => 'Monday',
                'meeting_time' => '18:00:00',
                'location'    => 'Conference Room A',
                'timezone'    => 'Asia/Kolkata',
            ],
            [
                'name'        => 'Chera Speech Club',
                'code'        => 'chera',
                'description' => 'The Chera Speech Club, building confidence through communication.',
                'status'      => 'active',
                'meeting_day' => 'Wednesday',
                'meeting_time' => '18:00:00',
                'location'    => 'Conference Room B',
                'timezone'    => 'Asia/Kolkata',
            ],
            [
                'name'        => 'Pandiya Speech Club',
                'code'        => 'pandiya',
                'description' => 'The Pandiya Speech Club, empowering voices across the organization.',
                'status'      => 'active',
                'meeting_day' => 'Friday',
                'meeting_time' => '17:30:00',
                'location'    => 'Conference Room C',
                'timezone'    => 'Asia/Kolkata',
            ],
            [
                'name'        => 'Pallava Speech Club',
                'code'        => 'pallava',
                'description' => 'The Pallava Speech Club, nurturing future leaders.',
                'status'      => 'active',
                'meeting_day' => 'Thursday',
                'meeting_time' => '18:30:00',
                'location'    => 'Training Hall',
                'timezone'    => 'Asia/Kolkata',
            ],
        ];

        foreach ($clubs as $club) {
            Club::firstOrCreate(['code' => $club['code']], $club);
        }

        $this->command->info('Clubs seeded successfully.');
    }
}
