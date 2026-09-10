<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MeetingReportPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_download_meeting_report_pdf(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\ClubSeeder::class);
        $this->seed(\Database\Seeders\MeetingRoleTypeSeeder::class);

        $club = Club::first();
        $user = User::factory()->create(['email' => 'member@example.com']);
        $user->assignRole('Member');
        $user->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 101,
            'meeting_date' => now()->toDateString(),
            'theme' => 'Leadership Through Action',
            'venue' => 'Main Auditorium',
            'status' => 'completed',
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Meetings\MeetingReport::class, ['meeting' => $meeting])
            ->assertStatus(200)
            ->call('downloadPdf')
            ->assertFileDownloaded("Meeting-101-Report-" . now()->toDateString() . ".pdf");
    }
}
