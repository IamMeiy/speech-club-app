<?php

namespace App\Livewire\Meetings;

use App\Livewire\Concerns\WithClubContext;
use App\Models\Meeting;
use App\Models\MeetingAhCounterLog;
use App\Models\MeetingEvaluation;
use App\Models\MeetingGrammarianLog;
use App\Models\MeetingRole;
use App\Models\MeetingRoleType;
use App\Models\MeetingSpeaker;
use App\Models\MeetingTimerLog;
use App\Models\MeetingTtmSpeaker;
use App\Models\User;
use App\Services\ClubAccessService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Meeting Details')]
class MeetingShow extends Component
{
    use WithClubContext;

    public Meeting $meeting;

    // Self-service modal state for Speaker signup
    public bool $showSpeakerModal = false;
    public ?int $speakerProjectId = null;
    public string $speakerTopic = '';
    public string $speakerSpeechType = 'Pathways Project';
    public string $speakerProject = '';
    public string $speakerDuration = '5-7 mins';

    public function updatedSpeakerProjectId($value): void
    {
        if ($value) {
            $proj = \App\Models\Project::find($value);
            if ($proj) {
                $this->speakerProject = $proj->name;
                $this->speakerDuration = $proj->formattedTiming();
                $this->speakerSpeechType = $proj->track ?: 'Pathways Project';
            }
        }
    }

    // Self-service modal state for TTM signup
    public bool $showTtmModal = false;
    public string $ttmTopic = '';

    // Live Ah-Counter & Grammarian Tool modal state
    public bool $showLiveToolsModal = false;
    public string $activeToolTab = 'ah_counter'; // 'ah_counter' | 'grammarian'

    // Grammarian quick edit state
    public ?int $selectedGrammarUserId = null;
    public string $grammarGoodPhrases = '';
    public string $grammarAwkwardPhrases = '';
    public string $grammarNotes = '';

    public function mount(Meeting $meeting, ClubAccessService $access): void
    {
        $user = auth()->user();
        // Security check
        if (! $user->isSuperAdmin() && ! $access->validateUserBelongsToClub($user->id, $meeting->club_id)) {
            abort(403, 'This meeting does not belong to your club.');
        }

        $this->meeting = $meeting;
    }

    public function downloadAgenda(string $theme = 'indigo')
    {
        $validThemes = ['indigo', 'emerald', 'blue', 'purple', 'rose', 'amber', 'cyan'];
        $theme = in_array($theme, $validThemes, true) ? $theme : 'indigo';

        $meeting = $this->meeting->load([
            'club:id,name,code',
            'roles.roleType:id,name,sort_order',
            'roles.user:id,name,email',
            'speakers.user:id,name,email',
            'speakers.evaluation.evaluator:id,name,email',
            'ttmSpeakers.user:id,name,email',
        ]);

        $pdf = Pdf::loadView('pdf.meeting-agenda', compact('meeting', 'theme'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'defaultFont' => 'sans-serif']);

        $fileName = sprintf(
            'Meeting-%d-Agenda-%s.pdf',
            $meeting->meeting_number,
            $meeting->meeting_date->format('Y-m-d')
        );

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName,
            ['Content-Type' => 'application/pdf']
        );
    }

    private function validateCanVolunteer(): void
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && ! $user->belongsToClub($this->meeting->club_id)) {
            abort(403, 'You do not belong to this club.');
        }

        if (in_array($this->meeting->status, ['completed', 'cancelled'])) {
            abort(403, 'Role signups are closed for this meeting.');
        }
    }

    public function signUpForRole(int $roleTypeId): void
    {
        $this->validateCanVolunteer();
        $user = auth()->user();

        // Check if role is already filled
        $existingRole = MeetingRole::where('meeting_id', $this->meeting->id)
            ->where('meeting_role_type_id', $roleTypeId)
            ->first();

        if ($existingRole && $existingRole->user_id && $existingRole->user_id !== $user->id) {
            $this->dispatch('flash', message: 'This role has already been filled by another member.', type: 'error');
            return;
        }

        if ($existingRole) {
            $existingRole->update(['user_id' => $user->id]);
        } else {
            MeetingRole::create([
                'meeting_id'           => $this->meeting->id,
                'meeting_role_type_id' => $roleTypeId,
                'user_id'              => $user->id,
            ]);
        }

        $this->dispatch('flash', message: 'You have signed up for this role!', type: 'success');
    }

    public function relinquishRole(int $meetingRoleId): void
    {
        $role = MeetingRole::findOrFail($meetingRoleId);
        $user = auth()->user();

        if (! $user->isSuperAdmin() && ! $user->can('meetings.update') && $role->user_id !== $user->id) {
            abort(403, 'You can only step down from your own role.');
        }

        $role->delete();
        $this->dispatch('flash', message: 'You have stepped down from this role.', type: 'info');
    }

    public function signUpAsSpeaker(): void
    {
        $this->validateCanVolunteer();
        $user = auth()->user();

        $this->validate([
            'speakerTopic'      => 'nullable|string|max:255',
            'speakerSpeechType' => 'nullable|string|max:100',
            'speakerProject'    => 'nullable|string|max:255',
            'speakerDuration'   => 'nullable|string|max:50',
        ]);

        $maxSlot = (int) MeetingSpeaker::where('meeting_id', $this->meeting->id)->max('slot');

        MeetingSpeaker::create([
            'meeting_id'  => $this->meeting->id,
            'user_id'     => $user->id,
            'project_id'  => $this->speakerProjectId ?: null,
            'slot'        => $maxSlot + 1,
            'topic'       => $this->speakerTopic ?: null,
            'speech_type' => $this->speakerSpeechType ?: 'Prepared Speech',
            'project'     => $this->speakerProject ?: null,
            'duration'    => $this->speakerDuration ?: '5-7 mins',
        ]);

        $this->showSpeakerModal = false;
        $this->reset(['speakerTopic', 'speakerProject', 'speakerProjectId']);
        $this->dispatch('speaker-signed-up');
        $this->dispatch('flash', message: 'You have signed up as a prepared speaker!', type: 'success');
    }

    public function relinquishSpeaker(int $speakerId): void
    {
        $speaker = MeetingSpeaker::findOrFail($speakerId);
        $user = auth()->user();

        if (! $user->isSuperAdmin() && ! $user->can('meetings.update') && $speaker->user_id !== $user->id) {
            abort(403, 'You can only remove your own speaker slot.');
        }

        $speaker->evaluation()?->delete();
        $speaker->delete();
        $this->dispatch('flash', message: 'Speaker slot removed.', type: 'info');
    }

    public function signUpForTtm(): void
    {
        $this->validateCanVolunteer();
        $user = auth()->user();

        $maxSlot = (int) MeetingTtmSpeaker::where('meeting_id', $this->meeting->id)->max('slot');

        MeetingTtmSpeaker::create([
            'meeting_id' => $this->meeting->id,
            'user_id'    => $user->id,
            'slot'       => $maxSlot + 1,
            'topic'      => $this->ttmTopic ?: null,
        ]);

        $this->showTtmModal = false;
        $this->reset('ttmTopic');
        $this->dispatch('ttm-signed-up');
        $this->dispatch('flash', message: 'You have signed up for Table Topics!', type: 'success');
    }

    public function relinquishTtm(int $ttmId): void
    {
        $ttm = MeetingTtmSpeaker::findOrFail($ttmId);
        $user = auth()->user();

        if (! $user->isSuperAdmin() && ! $user->can('meetings.update') && $ttm->user_id !== $user->id) {
            abort(403, 'You can only remove your own Table Topics slot.');
        }

        $ttm->delete();
        $this->dispatch('flash', message: 'Table Topics slot removed.', type: 'info');
    }

    // =========================================================================
    // Live Ah-Counter & Grammarian Facilitator Tools
    // =========================================================================

    public function incrementFiller(int $userId, string $fillerType): void
    {
        $allowed = ['ah_count', 'um_count', 'er_count', 'like_count', 'you_know_count', 'so_count', 'repeats_count', 'other_count'];
        if (! in_array($fillerType, $allowed, true)) {
            return;
        }

        $log = MeetingAhCounterLog::firstOrCreate(
            ['meeting_id' => $this->meeting->id, 'user_id' => $userId],
            [$fillerType => 0]
        );
        $log->increment($fillerType);
    }

    public function decrementFiller(int $userId, string $fillerType): void
    {
        $allowed = ['ah_count', 'um_count', 'er_count', 'like_count', 'you_know_count', 'so_count', 'repeats_count', 'other_count'];
        if (! in_array($fillerType, $allowed, true)) {
            return;
        }

        $log = MeetingAhCounterLog::where('meeting_id', $this->meeting->id)->where('user_id', $userId)->first();
        if ($log && $log->{$fillerType} > 0) {
            $log->decrement($fillerType);
        }
    }

    public function incrementWordOfDay(int $userId): void
    {
        $log = MeetingGrammarianLog::firstOrCreate(
            ['meeting_id' => $this->meeting->id, 'user_id' => $userId],
            ['word_of_day_count' => 0]
        );
        $log->increment('word_of_day_count');
    }

    public function decrementWordOfDay(int $userId): void
    {
        $log = MeetingGrammarianLog::where('meeting_id', $this->meeting->id)->where('user_id', $userId)->first();
        if ($log && $log->word_of_day_count > 0) {
            $log->decrement('word_of_day_count');
        }
    }

    public function openGrammarModal(int $userId): void
    {
        $this->selectedGrammarUserId = $userId;
        $log = MeetingGrammarianLog::where('meeting_id', $this->meeting->id)->where('user_id', $userId)->first();
        $this->grammarGoodPhrases = $log?->good_phrases ?? '';
        $this->grammarAwkwardPhrases = $log?->awkward_phrases ?? '';
        $this->grammarNotes = $log?->notes ?? '';
    }

    public function saveGrammarNotes(): void
    {
        if (! $this->selectedGrammarUserId) {
            return;
        }

        $log = MeetingGrammarianLog::firstOrCreate(
            ['meeting_id' => $this->meeting->id, 'user_id' => $this->selectedGrammarUserId],
            ['word_of_day_count' => 0]
        );

        $log->update([
            'good_phrases'    => $this->grammarGoodPhrases ?: null,
            'awkward_phrases' => $this->grammarAwkwardPhrases ?: null,
            'notes'           => $this->grammarNotes ?: null,
        ]);

        $this->selectedGrammarUserId = null;
        $this->dispatch('flash', message: 'Grammarian notes saved.', type: 'success');
    }

    public function saveAhCounterCounts(array $ahCounts): void
    {
        $this->saveAllCounts($ahCounts, null, 'ah_counter');
    }

    public function saveGrammarianCounts(array $grammarCounts): void
    {
        $this->saveAllCounts(null, $grammarCounts, 'grammarian');
    }

    public function saveAllCounts(?array $ahCounts = null, ?array $grammarCounts = null, ?string $role = null): void
    {
        $shouldProcessAh = ($role === 'ah_counter') || ($role === null && $ahCounts !== null && ! empty($ahCounts));
        $shouldProcessGrammar = ($role === 'grammarian') || ($role === null && $grammarCounts !== null && ! empty($grammarCounts));

        if ($shouldProcessAh && ! empty($ahCounts)) {
            $allowed = ['ah_count', 'um_count', 'er_count', 'like_count', 'you_know_count', 'so_count', 'repeats_count', 'other_count'];

            foreach ($ahCounts as $userId => $counts) {
                $userId = (int) $userId;
                if ($userId <= 0 || ! is_array($counts)) {
                    continue;
                }

                if (! User::where('id', $userId)->exists()) {
                    continue;
                }

                $data = [];
                $hasNonZero = false;
                foreach ($allowed as $f) {
                    if (array_key_exists($f, $counts)) {
                        $val = max(0, (int) $counts[$f]);
                        $data[$f] = $val;
                        if ($val > 0) {
                            $hasNonZero = true;
                        }
                    }
                }

                $existing = MeetingAhCounterLog::where('meeting_id', $this->meeting->id)->where('user_id', $userId)->first();
                if (! empty($data) && ($hasNonZero || ($role === 'ah_counter' && $existing))) {
                    MeetingAhCounterLog::updateOrCreate(
                        ['meeting_id' => $this->meeting->id, 'user_id' => $userId],
                        $data
                    );
                }
            }
        }

        if ($shouldProcessGrammar && ! empty($grammarCounts)) {
            foreach ($grammarCounts as $userId => $counts) {
                $userId = (int) $userId;
                if ($userId <= 0) {
                    continue;
                }

                if (! User::where('id', $userId)->exists()) {
                    continue;
                }

                if (is_array($counts)) {
                    $wodCount = max(0, (int) ($counts['word_of_day_count'] ?? 0));
                    $goodPhrases = isset($counts['good_phrases']) && trim((string)$counts['good_phrases']) !== '' ? trim((string)$counts['good_phrases']) : null;
                    $awkwardPhrases = isset($counts['awkward_phrases']) && trim((string)$counts['awkward_phrases']) !== '' ? trim((string)$counts['awkward_phrases']) : null;
                    $notes = isset($counts['notes']) && trim((string)$counts['notes']) !== '' ? trim((string)$counts['notes']) : null;

                    $hasData = ($wodCount > 0 || $goodPhrases !== null || $awkwardPhrases !== null || $notes !== null);
                    $existing = MeetingGrammarianLog::where('meeting_id', $this->meeting->id)->where('user_id', $userId)->first();

                    if ($hasData || ($role === 'grammarian' && $existing)) {
                        MeetingGrammarianLog::updateOrCreate(
                            ['meeting_id' => $this->meeting->id, 'user_id' => $userId],
                            [
                                'word_of_day_count' => $wodCount,
                                'good_phrases'      => $goodPhrases,
                                'awkward_phrases'   => $awkwardPhrases,
                                'notes'             => $notes,
                            ]
                        );
                    }
                } else {
                    $wodCount = max(0, (int) $counts);
                    $existing = MeetingGrammarianLog::where('meeting_id', $this->meeting->id)->where('user_id', $userId)->first();
                    if ($wodCount > 0 || ($role === 'grammarian' && $existing)) {
                        MeetingGrammarianLog::updateOrCreate(
                            ['meeting_id' => $this->meeting->id, 'user_id' => $userId],
                            ['word_of_day_count' => $wodCount]
                        );
                    }
                }
            }
        }

        $this->dispatch('counts-saved');
        $msg = match ($role) {
            'ah_counter' => 'Ah-Counter counts saved successfully!',
            'grammarian' => 'Grammarian report saved successfully!',
            default      => 'Live facilitator counts saved successfully!',
        };
        $this->dispatch('flash', message: $msg, type: 'success');
    }

    public function syncAhCounts(int $userId, array $counts): void
    {
        if ($userId <= 0 || ! User::where('id', $userId)->exists()) {
            return;
        }

        $allowed = ['ah_count', 'um_count', 'er_count', 'like_count', 'you_know_count', 'so_count', 'repeats_count', 'other_count'];
        $data = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $counts)) {
                $data[$f] = max(0, (int) $counts[$f]);
            }
        }

        if (! empty($data)) {
            MeetingAhCounterLog::updateOrCreate(
                ['meeting_id' => $this->meeting->id, 'user_id' => $userId],
                $data
            );
        }
    }

    public function syncGrammarCount(int $userId, int $count): void
    {
        if ($userId <= 0 || ! User::where('id', $userId)->exists()) {
            return;
        }

        MeetingGrammarianLog::updateOrCreate(
            ['meeting_id' => $this->meeting->id, 'user_id' => $userId],
            ['word_of_day_count' => max(0, (int) $count)]
        );
    }

    public function saveEvaluationNotes(int $evaluationId, string $notes): void
    {
        $evaluation = MeetingEvaluation::where('meeting_id', $this->meeting->id)
            ->where('id', $evaluationId)
            ->firstOrFail();

        $user = auth()->user();
        $isEvaluator = (int) $evaluation->evaluator_user_id === (int) $user->id;
        $canManageMeeting = $user->isSuperAdmin() || $user->can('meetings.update') || $user->hasRole('admin');

        if (! $isEvaluator && ! $canManageMeeting) {
            abort(403, 'You are not authorized to edit this evaluation.');
        }

        $evaluation->update([
            'notes' => trim($notes) !== '' ? trim($notes) : null,
        ]);

        $this->dispatch('flash', message: 'Evaluation feedback saved successfully!', type: 'success');
    }

    public function saveTimerLogs(array $timerData = []): void
    {
        foreach ($timerData as $item) {
            if (empty($item['speaker_type']) || empty($item['user_id'])) {
                continue;
            }

            $meetingId = $this->meeting->id;
            $speakerType = $item['speaker_type'];
            $refId = ! empty($item['reference_id']) ? (int) $item['reference_id'] : null;
            $userId = (int) $item['user_id'];
            $allotted = $item['allotted_time'] ?? null;
            $timeTaken = isset($item['time_taken']) && trim((string)$item['time_taken']) !== '' ? trim((string)$item['time_taken']) : null;
            $status = in_array($item['status'] ?? '', ['within_time', 'over_time', 'under_time', 'disqualified'], true)
                ? $item['status']
                : 'within_time';

            $existing = MeetingTimerLog::where('meeting_id', $meetingId)
                ->where('speaker_type', $speakerType)
                ->when($refId, fn ($q) => $q->where('reference_id', $refId), fn ($q) => $q->where('user_id', $userId))
                ->first();

            if ($timeTaken !== null || $existing) {
                MeetingTimerLog::updateOrCreate(
                    [
                        'meeting_id'   => $meetingId,
                        'speaker_type' => $speakerType,
                        'reference_id' => $refId,
                    ],
                    [
                        'user_id'       => $userId,
                        'allotted_time' => $allotted,
                        'time_taken'    => $timeTaken,
                        'status'        => $status,
                        'notes'         => ! empty($item['notes']) ? trim($item['notes']) : null,
                    ]
                );
            }
        }

        $this->dispatch('timer-logs-saved');
        $this->dispatch('flash', message: 'Timer sheet saved successfully!', type: 'success');
    }

    public function render()
    {
        $meeting = $this->meeting->load([
            'club:id,name,code',
            'creator:id,name',
            'roles.roleType:id,name,sort_order',
            'roles.user:id,name,email',
            'speakers.user:id,name,email',
            'speakers.projectModel:id,name,track,level,min_minutes,max_minutes',
            'speakers.evaluation.evaluator:id,name,email',
            'ttmSpeakers.user:id,name,email',
            'evaluations.speaker.user:id,name,email',
            'evaluations.evaluator:id,name,email',
            'attendance.user:id,name,email',
            'ahCounterLogs.user:id,name',
            'grammarianLogs.user:id,name',
            'timerLogs.user:id,name',
        ]);

        $allRoleTypes = MeetingRoleType::active()->get(['id', 'name', 'sort_order']);
        $currentUserId = auth()->id();
        $canVolunteer = in_array($meeting->status, ['draft', 'scheduled']) &&
                        (auth()->user()->isSuperAdmin() || auth()->user()->belongsToClub($meeting->club_id));

        $projects = \App\Models\Project::active()
            ->orderBy('level')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'level', 'min_minutes', 'max_minutes', 'track']);

        $initialAhLogs = [];
        foreach ($meeting->ahCounterLogs as $l) {
            $initialAhLogs[$l->user_id] = [
                'ah_count'       => (int) $l->ah_count,
                'um_count'       => (int) $l->um_count,
                'er_count'       => (int) $l->er_count,
                'like_count'     => (int) $l->like_count,
                'you_know_count' => (int) $l->you_know_count,
                'so_count'       => (int) $l->so_count,
                'repeats_count'  => (int) $l->repeats_count,
                'other_count'    => (int) $l->other_count,
            ];
        }

        $initialGrammarLogs = [];
        foreach ($meeting->grammarianLogs as $g) {
            $initialGrammarLogs[$g->user_id] = [
                'word_of_day_count' => (int) $g->word_of_day_count,
                'good_phrases'      => $g->good_phrases ?? '',
                'awkward_phrases'   => $g->awkward_phrases ?? '',
                'notes'             => $g->notes ?? '',
            ];
        }

        $initialTimerLogs = [];
        foreach ($meeting->timerLogs as $tl) {
            $key = $tl->speaker_type . '_' . ($tl->reference_id ?: $tl->user_id);
            $initialTimerLogs[$key] = [
                'time_taken' => $tl->time_taken ?? '',
                'status'     => $tl->status ?? 'within_time',
                'notes'      => $tl->notes ?? '',
            ];
        }

        // Extract active speakers & role players for live counter tools (Role players, Speakers, Evaluators, TTM)
        $activeParticipants = collect();

        foreach ($meeting->roles as $role) {
            if ($role->user) {
                $entry = $activeParticipants->get($role->user_id, [
                    'id'       => $role->user_id,
                    'name'     => $role->user->name,
                    'roles'    => [],
                    'category' => 'Role Player',
                ]);
                $entry['roles'][] = $role->roleType->name;
                $activeParticipants->put($role->user_id, $entry);
            }
        }

        foreach ($meeting->speakers as $sp) {
            if ($sp->user) {
                $entry = $activeParticipants->get($sp->user_id, [
                    'id'       => $sp->user_id,
                    'name'     => $sp->user->name,
                    'roles'    => [],
                    'category' => 'Speaker',
                ]);
                $entry['roles'][] = 'Speaker #' . $sp->slot;
                $activeParticipants->put($sp->user_id, $entry);
            }
        }

        foreach ($meeting->evaluations as $ev) {
            if ($ev->evaluator) {
                $entry = $activeParticipants->get($ev->evaluator_user_id, [
                    'id'       => $ev->evaluator_user_id,
                    'name'     => $ev->evaluator->name,
                    'roles'    => [],
                    'category' => 'Evaluator',
                ]);
                if (! in_array('Evaluator', $entry['roles'])) {
                    $entry['roles'][] = 'Evaluator';
                }
                $activeParticipants->put($ev->evaluator_user_id, $entry);
            }
        }

        foreach ($meeting->ttmSpeakers as $ttm) {
            if ($ttm->user) {
                $entry = $activeParticipants->get($ttm->user_id, [
                    'id'       => $ttm->user_id,
                    'name'     => $ttm->user->name,
                    'roles'    => [],
                    'category' => 'TTM Speaker',
                ]);
                $entry['roles'][] = 'TTM #' . $ttm->slot;
                $activeParticipants->put($ttm->user_id, $entry);
            }
        }

        foreach ($meeting->ahCounterLogs as $log) {
            if ($log->user && ! $activeParticipants->has($log->user_id)) {
                $activeParticipants->put($log->user_id, [
                    'id'       => $log->user_id,
                    'name'     => $log->user->name,
                    'roles'    => ['Speaker'],
                    'category' => 'Participant',
                ]);
            }
        }

        $meetingParticipants = $activeParticipants->values();

        return view('livewire.meetings.meeting-show', compact(
            'meeting',
            'allRoleTypes',
            'meetingParticipants',
            'currentUserId',
            'canVolunteer',
            'projects',
            'initialAhLogs',
            'initialGrammarLogs',
            'initialTimerLogs'
        ))->title('Meeting #' . $meeting->meeting_number . ' — ' . $meeting->club->name);
    }
}
