<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithClubContext;
use App\Models\Club;
use App\Models\Meeting;
use App\Models\User;
use App\Services\ClubContextService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    use WithClubContext;

    public function render(ClubContextService $clubContext)
    {
        $user        = auth()->user();
        $currentClub = $clubContext->currentClub();

        // Club-scoped dashboard data
        if ($currentClub) {
            $memberCount      = $currentClub->users()->where('users.status', 'active')->count();
            $upcomingMeetings = Meeting::forClub($currentClub->id)->upcoming()->limit(3)->get();
            $recentMeetings   = Meeting::forClub($currentClub->id)->past()->limit(5)->get();
            $totalMeetings    = Meeting::forClub($currentClub->id)->count();

            // Last meeting attendance
            $lastMeeting    = Meeting::forClub($currentClub->id)->where('status', 'completed')->latest('meeting_date')->first();
            $lastAttendance = $lastMeeting
                ? $lastMeeting->attendance()->where('status', 'present')->count()
                : null;

            return view('livewire.dashboard', compact(
                'currentClub',
                'memberCount',
                'upcomingMeetings',
                'recentMeetings',
                'totalMeetings',
                'lastMeeting',
                'lastAttendance',
            ))->title('Dashboard — ' . $currentClub->name);
        }

        // Global / All Clubs dashboard
        $clubs         = $clubContext->availableClubs();
        $totalClubs    = $clubs->count();
        $totalUsers    = User::whereHas('clubs', fn ($q) => $q->whereIn('clubs.id', $clubs->pluck('id')))->count();
        $upcomingCount = Meeting::whereIn('club_id', $clubs->pluck('id'))->upcoming()->count();
        $recentMeetings = Meeting::with('club')
            ->whereIn('club_id', $clubs->pluck('id'))
            ->latest('meeting_date')
            ->limit(5)
            ->get();

        $clubSummaries = $clubs->map(function (Club $club) {
            return [
                'club'            => $club,
                'member_count'    => $club->users()->where('users.status', 'active')->count(),
                'upcoming_count'  => Meeting::forClub($club->id)->upcoming()->count(),
                'total_meetings'  => Meeting::forClub($club->id)->count(),
            ];
        });

        return view('livewire.dashboard', compact(
            'currentClub',
            'clubs',
            'totalClubs',
            'totalUsers',
            'upcomingCount',
            'recentMeetings',
            'clubSummaries',
        ))->title('Dashboard — All Clubs');
    }
}
