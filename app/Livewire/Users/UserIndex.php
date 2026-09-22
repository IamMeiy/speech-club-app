<?php

namespace App\Livewire\Users;

use App\Livewire\Concerns\WithClubContext;
use App\Models\User;
use App\Services\ClubContextService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Lazy]
#[Layout('components.layouts.app')]
#[Title('Members')]
class UserIndex extends Component
{
    use WithPagination, WithClubContext;

    public function placeholder()
    {
        $club = $this->getCurrentClub();
        return view('livewire.users.user-index-skeleton', compact('club'));
    }

    public string $search   = '';
    public string $status   = '';
    public string $role     = '';
    public int    $perPage  = 15;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingRole(): void   { $this->resetPage(); }

    #[Renderless]
    public function getMemberRoles(int $userId): array
    {
        $currentUser = auth()->user();
        $targetUser  = User::with(['roles:id,name', 'clubs:id,name'])->findOrFail($userId);

        if ($currentUser->isClubUser()) {
            $club = $currentUser->primaryClub();
            if ($club && ! $targetUser->belongsToClub($club->id)) {
                abort(403, 'This member does not belong to your club.');
            }
        } elseif (! $currentUser->isSuperAdmin()) {
            $commonClubs = $currentUser->clubs()->whereIn('clubs.id', $targetUser->clubs()->pluck('clubs.id'))->exists();
            if (! $commonClubs) {
                abort(403, 'You do not have permission to view this member.');
            }
        }

        $club   = $this->getCurrentClub();
        $clubId = $club?->id;

        // 1. Get all active facilitator role types in the club
        $allRoleTypes = \App\Models\MeetingRoleType::active()->get();

        // 2. Query user's facilitator roles (only scheduled & completed meetings)
        $userMeetingRoles = $targetUser->meetingRoles()
            ->whereHas('meeting', fn ($m) => $m->whereIn('status', ['scheduled', 'completed'])->when($clubId, fn ($q) => $q->where('club_id', $clubId)))
            ->with([
                'roleType:id,name',
                'meeting:id,club_id,meeting_number,meeting_date,status',
                'meeting.club:id,name',
            ])
            ->get()
            ->sortByDesc(fn ($r) => $r->meeting?->meeting_date?->timestamp ?? $r->id)
            ->values();

        // 3. Query user's speaker slots
        $userSpeeches = $targetUser->meetingSpeakerSlots()
            ->whereHas('meeting', fn ($m) => $m->whereIn('status', ['scheduled', 'completed'])->when($clubId, fn ($q) => $q->where('club_id', $clubId)))
            ->with([
                'projectModel:id,name,track,level',
                'meeting:id,club_id,meeting_number,meeting_date,status',
                'meeting.club:id,name',
            ])
            ->get()
            ->sortByDesc(fn ($s) => $s->meeting?->meeting_date?->timestamp ?? $s->id)
            ->values();

        // 4. Query user's evaluations
        $userEvaluations = $targetUser->evaluations()
            ->whereHas('meeting', fn ($m) => $m->whereIn('status', ['scheduled', 'completed'])->when($clubId, fn ($q) => $q->where('club_id', $clubId)))
            ->with([
                'speaker.user:id,name',
                'speaker.projectModel:id,name,track,level',
                'meeting:id,club_id,meeting_number,meeting_date,status',
                'meeting.club:id,name',
            ])
            ->get()
            ->sortByDesc(fn ($e) => $e->meeting?->meeting_date?->timestamp ?? $e->id)
            ->values();

        // 5. Query user's table topics slots
        $userTableTopics = $targetUser->meetingTtmSlots()
            ->whereHas('meeting', fn ($m) => $m->whereIn('status', ['scheduled', 'completed'])->when($clubId, fn ($q) => $q->where('club_id', $clubId)))
            ->with([
                'meeting:id,club_id,meeting_number,meeting_date,status',
                'meeting.club:id,name',
            ])
            ->get()
            ->sortByDesc(fn ($t) => $t->meeting?->meeting_date?->timestamp ?? $t->id)
            ->values();

        // 6. Build Master Role Catalog
        $acronyms = [
            'TMOD' => 'Toastmaster of the Day',
            'GE'   => 'General Evaluator',
            'TTM'  => 'Table Topics Master',
        ];

        $catalog = [];

        foreach ($allRoleTypes as $type) {
            $assigned = $userMeetingRoles->where('meeting_role_type_id', $type->id);
            $last     = $assigned->first();
            $catalog[] = [
                'name'             => $type->name,
                'full_name'        => $acronyms[$type->name] ?? null,
                'category'         => 'Facilitator',
                'count'            => $assigned->count(),
                'last_date'        => $last?->meeting?->meeting_date?->format('M d, Y'),
                'last_meeting'     => $last?->meeting ? ('#' . $last->meeting->meeting_number) : null,
                'last_meeting_url' => $last?->meeting ? route('meetings.show', $last->meeting) : null,
            ];
        }

        $lastSpeech = $userSpeeches->first();
        $catalog[] = [
            'name'             => 'Prepared Speaker',
            'full_name'        => null,
            'category'         => 'Speaking',
            'count'            => $userSpeeches->count(),
            'last_date'        => $lastSpeech?->meeting?->meeting_date?->format('M d, Y'),
            'last_meeting'     => $lastSpeech?->meeting ? ('#' . $lastSpeech->meeting->meeting_number) : null,
            'last_meeting_url' => $lastSpeech?->meeting ? route('meetings.show', $lastSpeech->meeting) : null,
        ];

        $lastEval = $userEvaluations->first();
        $catalog[] = [
            'name'             => 'Speech Evaluator',
            'full_name'        => null,
            'category'         => 'Evaluation',
            'count'            => $userEvaluations->count(),
            'last_date'        => $lastEval?->meeting?->meeting_date?->format('M d, Y'),
            'last_meeting'     => $lastEval?->meeting ? ('#' . $lastEval->meeting->meeting_number) : null,
            'last_meeting_url' => $lastEval?->meeting ? route('meetings.show', $lastEval->meeting) : null,
        ];

        $lastTtm = $userTableTopics->first();
        $catalog[] = [
            'name'             => 'Table Topics Speaker',
            'full_name'        => null,
            'category'         => 'Impromptu',
            'count'            => $userTableTopics->count(),
            'last_date'        => $lastTtm?->meeting?->meeting_date?->format('M d, Y'),
            'last_meeting'     => $lastTtm?->meeting ? ('#' . $lastTtm->meeting->meeting_number) : null,
            'last_meeting_url' => $lastTtm?->meeting ? route('meetings.show', $lastTtm->meeting) : null,
        ];

        // 7. Split into Taken and Not Taken
        $takenRoles    = collect($catalog)->filter(fn ($r) => $r['count'] > 0)->sortByDesc('count')->values()->toArray();
        $notTakenRoles = collect($catalog)->filter(fn ($r) => $r['count'] === 0)->values()->toArray();

        // 8. Build Chronological Recently Taken Timeline (latest 12 across all activities)
        $timeline = collect();

        foreach ($userMeetingRoles as $r) {
            $timeline->push([
                'role_name'      => $r->roleType?->name ?? 'Meeting Facilitator',
                'type'           => 'Facilitator',
                'detail'         => 'Club Meeting Facilitator',
                'meeting_number' => $r->meeting?->meeting_number,
                'meeting_date'   => $r->meeting?->meeting_date?->format('M d, Y'),
                'sort_date'      => $r->meeting?->meeting_date?->timestamp ?? 0,
                'status'         => $r->meeting?->status ?? 'completed',
                'club_name'      => $r->meeting?->club?->name,
                'meeting_url'    => $r->meeting ? route('meetings.show', $r->meeting) : null,
                'badge_color'    => 'indigo',
            ]);
        }

        foreach ($userSpeeches as $s) {
            $timeline->push([
                'role_name'      => 'Prepared Speaker',
                'type'           => 'Speaker',
                'detail'         => $s->topic ?: ($s->projectModel?->name ?? $s->project ?? 'Prepared Speech'),
                'meeting_number' => $s->meeting?->meeting_number,
                'meeting_date'   => $s->meeting?->meeting_date?->format('M d, Y'),
                'sort_date'      => $s->meeting?->meeting_date?->timestamp ?? 0,
                'status'         => $s->meeting?->status ?? 'completed',
                'club_name'      => $s->meeting?->club?->name,
                'meeting_url'    => $s->meeting ? route('meetings.show', $s->meeting) : null,
                'badge_color'    => 'primary',
            ]);
        }

        foreach ($userEvaluations as $e) {
            $speakerName  = $e->speaker?->user?->name ?? 'Speaker';
            $speechDetail = $e->speaker?->topic ? ' ("' . $e->speaker->topic . '")' : '';
            $timeline->push([
                'role_name'      => 'Speech Evaluator',
                'type'           => 'Evaluator',
                'detail'         => 'Evaluated ' . $speakerName . $speechDetail,
                'meeting_number' => $e->meeting?->meeting_number,
                'meeting_date'   => $e->meeting?->meeting_date?->format('M d, Y'),
                'sort_date'      => $e->meeting?->meeting_date?->timestamp ?? 0,
                'status'         => $e->meeting?->status ?? 'completed',
                'club_name'      => $e->meeting?->club?->name,
                'meeting_url'    => $e->meeting ? route('meetings.show', $e->meeting) : null,
                'badge_color'    => 'emerald',
            ]);
        }

        foreach ($userTableTopics as $t) {
            $timeline->push([
                'role_name'      => 'Table Topics Speaker',
                'type'           => 'Table Topics',
                'detail'         => $t->topic ?: 'Table Topic Challenge',
                'meeting_number' => $t->meeting?->meeting_number,
                'meeting_date'   => $t->meeting?->meeting_date?->format('M d, Y'),
                'sort_date'      => $t->meeting?->meeting_date?->timestamp ?? 0,
                'status'         => $t->meeting?->status ?? 'completed',
                'club_name'      => $t->meeting?->club?->name,
                'meeting_url'    => $t->meeting ? route('meetings.show', $t->meeting) : null,
                'badge_color'    => 'purple',
            ]);
        }

        $recentlyTaken = $timeline->sortByDesc('sort_date')->take(12)->values()->toArray();

        return [
            'user' => [
                'id'          => $targetUser->id,
                'name'        => $targetUser->name,
                'initials'    => strtoupper(substr($targetUser->name, 0, 2)),
                'email'       => $targetUser->email,
                'role'        => $targetUser->roles->first()?->name ?? 'Member',
                'clubs'       => $targetUser->clubs->pluck('name')->implode(', '),
                'profile_url' => route('members.show', $targetUser),
            ],
            'taken_roles'       => $takenRoles,
            'not_taken_roles'   => $notTakenRoles,
            'recently_taken'    => $recentlyTaken,
            'total_taken_count' => count($takenRoles),
            'total_not_taken'   => count($notTakenRoles),
            'total_sessions'    => $timeline->count(),
        ];
    }

    public function deleteUser(int $userId): void
    {
        $this->authorize('users.delete');

        $currentUser = auth()->user();
        $user        = User::findOrFail($userId);

        if ($currentUser->isClubUser()) {
            $club = $currentUser->primaryClub();
            if ($club && ! $user->belongsToClub($club->id)) {
                abort(403);
            }
        } elseif (! $currentUser->isSuperAdmin()) {
            $commonClubs = $currentUser->clubs()->whereIn('clubs.id', $user->clubs()->pluck('clubs.id'))->exists();
            if (! $commonClubs) {
                abort(403);
            }
        }

        $user->delete();

        $this->dispatch('flash', message: 'Member deleted successfully.', type: 'success');
    }

    public function render(ClubContextService $clubContext)
    {
        $user = auth()->user();
        $club = $clubContext->currentClub();

        $query = User::select(['id', 'name', 'email', 'phone', 'status', 'created_at'])
            ->with(['roles:id,name', 'clubs:id,name'])
            ->when($club, fn ($q) => $q->inClub($club->id))
            ->when(! $club && ! $user->isSuperAdmin(), function ($q) use ($user) {
                $q->whereHas('clubs', fn ($c) => $c->whereIn('clubs.id', $user->clubs()->pluck('clubs.id')));
            })
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->role, fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $this->role)))
            ->orderBy('name');

        $users     = $query->paginate($this->perPage);
        $clubRoles = config('speech-club.club_roles', []);

        return view('livewire.users.user-index', compact('users', 'club', 'clubRoles'));
    }
}
