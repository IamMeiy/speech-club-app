<?php

namespace App\Livewire\Users;

use App\Livewire\Concerns\WithClubContext;
use App\Models\MeetingAhCounterLog;
use App\Models\MeetingGrammarianLog;
use App\Models\User;
use App\Services\ClubAccessService;
use App\Services\ClubContextService;
use App\Services\LocalAiService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Member Details')]
class UserShow extends Component
{
    use WithClubContext, WithPagination;

    public User $user;
    public string $activeTab = 'speeches'; // 'speeches' | 'evaluations' | 'table_topics' | 'roles' | 'attendance' | 'badges' | 'clubs' | 'ai_coach'

    // AI Coaching state
    public string $aiCoachingReport = '';
    public string $aiEngineLabel    = '';

    public int $speechesPerPage    = 10;
    public int $evaluationsPerPage = 10;
    public int $tableTopicsPerPage = 10;
    public int $rolesPerPage       = 10;
    public int $attendancePerPage  = 15;

    public function mount(User $user, ClubAccessService $access, LocalAiService $ai): void
    {
        $this->authorize('users.view');

        $currentUser = auth()->user();

        if ($currentUser->isClubUser()) {
            $club = $currentUser->primaryClub();
            if ($club && ! $user->belongsToClub($club->id)) {
                abort(403, 'This member does not belong to your club.');
            }
        } elseif (! $currentUser->isSuperAdmin()) {
            // Global user: must share at least one assigned club
            $commonClubs = $currentUser->clubs()->whereIn('clubs.id', $user->clubs()->pluck('clubs.id'))->exists();
            if (! $commonClubs) {
                abort(403, 'You do not have permission to view this member.');
            }
        }

        $this->user          = $user;
        $this->aiEngineLabel = $ai->engineLabel();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function deleteUser(): void
    {
        $this->authorize('users.delete');

        $currentUser = auth()->user();
        if ($currentUser->isClubUser()) {
            $club = $currentUser->primaryClub();
            if ($club && ! $this->user->belongsToClub($club->id)) {
                abort(403);
            }
        } elseif (! $currentUser->isSuperAdmin()) {
            $commonClubs = $currentUser->clubs()->whereIn('clubs.id', $this->user->clubs()->pluck('clubs.id'))->exists();
            if (! $commonClubs) {
                abort(403);
            }
        }

        $this->user->delete();

        $this->dispatch('flash', message: 'Member deleted successfully.', type: 'success');
        $this->redirect(route('members.index'), navigate: true);
    }

    public function render(ClubContextService $clubContext)
    {
        $currentClub = $clubContext->currentClub();
        $clubId = $currentClub?->id;

        $this->user->loadMissing([
            'clubs:id,name,code',
            'roles.permissions',
            'permissions',
        ]);

        // -------------------------------------------------------------------
        // 1. High-Performance SQL Aggregations (0 Eloquent models loaded)
        // Scalable to thousands of historical meetings without memory bloat.
        // -------------------------------------------------------------------
        $speechesBaseQuery = $this->user->meetingSpeakerSlots()
            ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)));
        $speechesCount = (clone $speechesBaseQuery)->count();

        $evaluationsBaseQuery = $this->user->evaluations()
            ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)));
        $evaluationsCount = (clone $evaluationsBaseQuery)->count();

        $tableTopicsBaseQuery = $this->user->meetingTtmSlots()
            ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)));
        $tableTopicsCount = (clone $tableTopicsBaseQuery)->count();

        $rolesBaseQuery = $this->user->meetingRoles()
            ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)));
        $rolesCount = (clone $rolesBaseQuery)->count();

        $attendanceBaseQuery = $this->user->attendance()
            ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)));
        $totalAttended = (clone $attendanceBaseQuery)->whereIn('status', ['present', 'late'])->count();
        $totalRecords  = (clone $attendanceBaseQuery)->count();
        $attendanceRate = $totalRecords > 0 ? (int) round(($totalAttended / $totalRecords) * 100) : 0;

        // Pre-computed badge metrics using pure integers
        $badgeCounts = [
            'speeches'          => $speechesCount,
            'table_topics'      => $tableTopicsCount,
            'evaluations'       => $evaluationsCount,
            'roles'             => $rolesCount,
            'meetings_attended' => $totalAttended,
        ];
        $badges = $this->user->getMilestoneBadges($clubId, $badgeCounts);
        $unlockedBadgesCount = collect($badges)->where('unlocked', true)->count();

        $stats = [
            'speeches'          => $speechesCount,
            'table_topics'      => $tableTopicsCount,
            'evaluations_given' => $evaluationsCount,
            'roles_served'      => $rolesCount,
            'total_attended'    => $totalAttended,
            'total_records'     => $totalRecords,
            'attendance_rate'   => $attendanceRate,
            'unlocked_badges'   => $unlockedBadgesCount,
            'total_badges'      => count($badges),
        ];

        // -------------------------------------------------------------------
        // 2. Paginated Data Sets (prevents loading 50+ or 100+ records at once)
        // -------------------------------------------------------------------

        // Attendance (15 per page)
        $attendanceRecords = (clone $attendanceBaseQuery)
            ->with([
                'meeting:id,club_id,meeting_number,meeting_date,status',
                'meeting.club:id,name',
            ])
            ->orderByDesc('id')
            ->paginate($this->attendancePerPage, ['*'], 'attendancePage');

        // Prepared Speeches (10 per page)
        $speeches = (clone $speechesBaseQuery)
            ->with([
                'meeting:id,club_id,meeting_number,meeting_date,status',
                'meeting.club:id,name',
                'projectModel:id,name,track,level,min_minutes,max_minutes',
                'evaluation.evaluator:id,name',
            ])
            ->orderByDesc('id')
            ->paginate($this->speechesPerPage, ['*'], 'speechesPage');

        // Attach Ah-Counter & Grammarian logs ONLY for the speeches on the current page
        $speechMeetingIds = $speeches->pluck('meeting_id')->filter()->unique();
        if ($speechMeetingIds->isNotEmpty()) {
            $ahLogs = MeetingAhCounterLog::whereIn('meeting_id', $speechMeetingIds)
                ->where('user_id', $this->user->id)
                ->get(['id', 'meeting_id', 'user_id', 'ah_count', 'um_count', 'like_count', 'repeats_count'])
                ->keyBy('meeting_id');

            $grammarLogs = MeetingGrammarianLog::whereIn('meeting_id', $speechMeetingIds)
                ->where('user_id', $this->user->id)
                ->get(['id', 'meeting_id', 'user_id', 'word_of_day_count', 'good_phrases'])
                ->keyBy('meeting_id');

            foreach ($speeches as $speech) {
                $speech->ah_log = $ahLogs->get($speech->meeting_id);
                $speech->grammar_log = $grammarLogs->get($speech->meeting_id);
            }
        }

        // Evaluations Given (10 per page)
        $evaluationsGiven = (clone $evaluationsBaseQuery)
            ->with([
                'meeting:id,club_id,meeting_number,meeting_date,status',
                'meeting.club:id,name',
                'speaker.user:id,name',
                'speaker.projectModel:id,name,track,level',
            ])
            ->orderByDesc('id')
            ->paginate($this->evaluationsPerPage, ['*'], 'evaluationsPage');

        // Table Topics (10 per page)
        $tableTopics = (clone $tableTopicsBaseQuery)
            ->with([
                'meeting:id,club_id,meeting_number,meeting_date,status',
                'meeting.club:id,name',
            ])
            ->orderByDesc('id')
            ->paginate($this->tableTopicsPerPage, ['*'], 'tableTopicsPage');

        // Meeting Roles (10 per page)
        $rolesServed = (clone $rolesBaseQuery)
            ->with([
                'meeting:id,club_id,meeting_number,meeting_date,status',
                'meeting.club:id,name',
                'roleType:id,name',
            ])
            ->orderByDesc('id')
            ->paginate($this->rolesPerPage, ['*'], 'rolesPage');

        return view('livewire.users.user-show', compact(
            'currentClub',
            'speeches',
            'evaluationsGiven',
            'tableTopics',
            'rolesServed',
            'attendanceRecords',
            'badges',
            'stats',
        ));
    }

    // =========================================================================
    // AI Speech Coach Actions
    // =========================================================================

    public function generateAiCoaching(): void
    {
        abort_unless(config('services.ai_assistant.enabled', true), 403, 'AI Assistant is currently disabled.');
        $ai     = app(LocalAiService::class);
        $clubId = auth()->user()->primaryClub()?->id ?? 0;
        $this->aiCoachingReport = $ai->generateMemberCoaching($this->user, $clubId);
        $this->aiEngineLabel    = $ai->engineLabel();
    }

    public function generateTmodIntro(): void
    {
        abort_unless(config('services.ai_assistant.enabled', true), 403, 'AI Assistant is currently disabled.');
        $ai = app(LocalAiService::class);
        $this->aiCoachingReport = $ai->generateTmodIntroduction($this->user);
        $this->aiEngineLabel    = $ai->engineLabel();
    }

    public function clearAiCoaching(): void
    {
        $this->aiCoachingReport = '';
    }
}
