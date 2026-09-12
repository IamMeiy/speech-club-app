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
            $upcomingMeetings = Meeting::select(['id', 'club_id', 'meeting_number', 'meeting_date', 'start_time', 'end_time', 'theme', 'status', 'venue'])
                ->forClub($currentClub->id)
                ->upcoming()
                ->limit(3)
                ->get();
            $recentMeetings   = Meeting::select(['id', 'club_id', 'meeting_number', 'meeting_date', 'start_time', 'end_time', 'theme', 'status', 'venue'])
                ->forClub($currentClub->id)
                ->past()
                ->limit(5)
                ->get();
            $totalMeetings    = Meeting::forClub($currentClub->id)->count();

            // Last meeting attendance
            $lastMeeting    = Meeting::select(['id', 'club_id', 'meeting_number', 'meeting_date', 'status'])
                ->forClub($currentClub->id)
                ->where('status', 'completed')
                ->latest('meeting_date')
                ->first();
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
        $recentMeetings = Meeting::select(['id', 'club_id', 'meeting_number', 'meeting_date', 'start_time', 'end_time', 'theme', 'status', 'venue'])
            ->with('club:id,name')
            ->whereIn('club_id', $clubs->pluck('id'))
            ->latest('meeting_date')
            ->limit(5)
            ->get();

        // Batch aggregated counts in 3 queries instead of 3x N+1 loop
        $clubs->loadCount([
            'users as member_count' => fn ($q) => $q->where('users.status', 'active'),
            'meetings as upcoming_count' => fn ($q) => $q->upcoming(),
            'meetings as total_meetings',
        ]);

        $clubSummaries = $clubs->map(function (Club $club) {
            return [
                'club'            => $club,
                'member_count'    => (int) $club->member_count,
                'upcoming_count'  => (int) $club->upcoming_count,
                'total_meetings'  => (int) $club->total_meetings,
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
