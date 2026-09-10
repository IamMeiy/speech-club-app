<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Meeting;
use App\Models\User;
use App\Services\ClubContextService;
use Database\Seeders\ClubSeeder;
use Database\Seeders\MeetingRoleTypeSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubScopeTest extends TestCase
{
    use RefreshDatabase;

    protected Club $chola;
    protected Club $chera;
    protected User $cholaPresident;
    protected User $cheraPresident;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ClubSeeder::class);
        $this->seed(MeetingRoleTypeSeeder::class);

        $this->chola = Club::where('code', 'chola')->first();
        $this->chera = Club::where('code', 'chera')->first();

        // Chola President
        $this->cholaPresident = User::factory()->create(['name' => 'Arun', 'status' => 'active']);
        $this->cholaPresident->clubs()->attach($this->chola->id);
        $this->cholaPresident->assignRole('President');

        // Chera President
        $this->cheraPresident = User::factory()->create(['name' => 'Ravi', 'status' => 'active']);
        $this->cheraPresident->clubs()->attach($this->chera->id);
        $this->cheraPresident->assignRole('President');

        // Super Admin
        $this->superAdmin = User::factory()->create(['name' => 'Root Admin', 'status' => 'active']);
        $this->superAdmin->assignRole('Super Admin');
    }

    public function test_club_user_context_is_their_own_club(): void
    {
        $this->actingAs($this->cholaPresident);
        $service = app(ClubContextService::class);

        $this->assertEquals($this->chola->id, $service->currentClub()->id);
    }

    public function test_club_user_cannot_access_other_club_meetings(): void
    {
        // Meeting in Chera
        $cheraMeeting = Meeting::create([
            'club_id'        => $this->chera->id,
            'meeting_number' => 1,
            'meeting_date'   => now()->addDays(3),
            'status'         => 'scheduled',
            'created_by'     => $this->cheraPresident->id,
        ]);

        $response = $this->actingAs($this->cholaPresident)->get(route('meetings.show', $cheraMeeting));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_all_club_meetings(): void
    {
        $cheraMeeting = Meeting::create([
            'club_id'        => $this->chera->id,
            'meeting_number' => 1,
            'meeting_date'   => now()->addDays(3),
            'status'         => 'scheduled',
            'created_by'     => $this->cheraPresident->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('meetings.show', $cheraMeeting));
        $response->assertStatus(200);
    }

    public function test_global_user_can_switch_assigned_clubs(): void
    {
        $globalAdmin = User::factory()->create(['status' => 'active']);
        $globalAdmin->assignRole('Admin');
        $globalAdmin->clubs()->attach([$this->chola->id, $this->chera->id]);

        $this->actingAs($globalAdmin);

        $service = app(ClubContextService::class);
        $service->setCurrentClub($this->chola->id);
        $this->assertEquals($this->chola->id, $service->currentClub()->id);

        $service->setCurrentClub($this->chera->id);
        $this->assertEquals($this->chera->id, $service->currentClub()->id);
    }
}
