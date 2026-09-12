<?php

namespace App\Livewire\Progress;

use App\Livewire\Concerns\WithClubContext;
use App\Models\MeetingAhCounterLog;
use App\Models\MeetingGrammarianLog;
use App\Services\ClubContextService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('My Progress & Feedback')]
class ProgressIndex extends Component
{
    use WithClubContext;

    public string $activeTab = 'speeches'; // 'speeches' | 'evaluations_given' | 'table_topics' | 'roles' | 'badges'

    public function render(ClubContextService $clubContext)
    {
        $user = auth()->user();
        $currentClub = $clubContext->currentClub();
        $clubId = $currentClub?->id;

        // 1. Prepared Speeches Delivered by this user
        $speeches = $user->meetingSpeakerSlots()
            ->with([
                'meeting:id,club_id,meeting_number,meeting_date',
                'meeting.club:id,name',
                'projectModel:id,name,track,level,min_minutes,max_minutes',
                'evaluation.evaluator:id,name',
            ])
            ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)))
            ->orderByDesc('id')
            ->get();

        // Attach meeting Ah-Counter and Grammarian stats for each speech
        $meetingIds = $speeches->pluck('meeting_id')->unique();
        $ahLogs = MeetingAhCounterLog::whereIn('meeting_id', $meetingIds)
            ->where('user_id', $user->id)
            ->get(['id', 'meeting_id', 'user_id', 'ah_count', 'um_count', 'like_count', 'repeats_count'])
            ->keyBy('meeting_id');

        $grammarLogs = MeetingGrammarianLog::whereIn('meeting_id', $meetingIds)
            ->where('user_id', $user->id)
            ->get(['id', 'meeting_id', 'user_id', 'word_of_day_count', 'good_phrases'])
            ->keyBy('meeting_id');

        foreach ($speeches as $speech) {
            $speech->ah_log = $ahLogs->get($speech->meeting_id);
            $speech->grammar_log = $grammarLogs->get($speech->meeting_id);
        }

        // 2. Evaluations Given by this user to peer speakers
        $evaluationsGiven = $user->evaluations()
            ->with([
                'meeting:id,club_id,meeting_number,meeting_date',
                'meeting.club:id,name',
                'speaker.user:id,name',
                'speaker.projectModel:id,name,track,level',
            ])
            ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)))
            ->orderByDesc('id')
            ->get();

        // 3. Table Topics (Impromptu speaking history)
        $tableTopics = $user->meetingTtmSlots()
            ->with([
                'meeting:id,club_id,meeting_number,meeting_date',
                'meeting.club:id,name',
            ])
            ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)))
            ->orderByDesc('id')
            ->get();

        // 4. Meeting Facilitator Roles served
        $rolesServed = $user->meetingRoles()
            ->with([
                'meeting:id,club_id,meeting_number,meeting_date',
                'meeting.club:id,name',
                'roleType:id,name',
            ])
            ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)))
            ->orderByDesc('id')
            ->get();

        // 5. Milestone Badges with pre-computed counts to eliminate N+1 queries
        $counts = [
            'speeches'          => $speeches->count(),
            'table_topics'      => $tableTopics->count(),
            'evaluations'       => $evaluationsGiven->count(),
            'roles'             => $rolesServed->count(),
            'meetings_attended' => $user->attendance()
                ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)))
                ->whereIn('status', ['present', 'late'])
                ->count(),
        ];
        $badges = $user->getMilestoneBadges($clubId, $counts);
        $unlockedBadgesCount = collect($badges)->where('unlocked', true)->count();

        // 6. Summary metrics
        $stats = [
            'speeches'          => $speeches->count(),
            'table_topics'      => $tableTopics->count(),
            'evaluations_given' => $evaluationsGiven->count(),
            'roles_served'      => $rolesServed->count(),
            'unlocked_badges'   => $unlockedBadgesCount,
            'total_badges'      => count($badges),
        ];

        return view('livewire.progress.progress-index', compact(
            'user',
            'currentClub',
            'speeches',
            'evaluationsGiven',
            'tableTopics',
            'rolesServed',
            'badges',
            'stats',
        ));
    }
}
