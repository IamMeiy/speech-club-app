<?php

namespace Database\Seeders;

use App\Models\MeetingRoleType;
use Illuminate\Database\Seeder;

class MeetingRoleTypeSeeder extends Seeder
{
    public function run(): void
    {
        $roleTypes = [
            ['name' => 'TMOD',             'slug' => 'tmod',             'sort_order' => 1,  'is_active' => true],
            ['name' => 'GE',               'slug' => 'ge',               'sort_order' => 2,  'is_active' => true],
            ['name' => 'TTM',              'slug' => 'ttm',              'sort_order' => 3,  'is_active' => true],
            ['name' => 'Listening Master', 'slug' => 'listening-master', 'sort_order' => 4,  'is_active' => true],
            ['name' => 'Grammarian',       'slug' => 'grammarian',       'sort_order' => 5,  'is_active' => true],
            ['name' => 'Ah Counter',       'slug' => 'ah-counter',       'sort_order' => 6,  'is_active' => true],
            ['name' => 'Timer',            'slug' => 'timer',            'sort_order' => 7,  'is_active' => true],
        ];

        foreach ($roleTypes as $roleType) {
            MeetingRoleType::firstOrCreate(
                ['slug' => $roleType['slug']],
                $roleType
            );
        }

        $this->command->info('Meeting role types seeded successfully.');
    }
}
