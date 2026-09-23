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
use Livewire\Attributes\Renderless;
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
    public string $speakerSpeechType = 'Speech Project';
    public string $speakerProject = '';
    public string $speakerDuration = '5-7 mins';

    #[Renderless]
    public function updatedSpeakerProjectId($value): void
    {
        if ($value) {
            $proj = \App\Models\Project::find($value);
            if ($proj) {
                $this->speakerProject = $proj->name;
                $this->speakerDuration = $proj->formattedTiming();
                $this->speakerSpeechType = $proj->track ?: 'Speech Project';
            }
        } else {
            $this->speakerProject = '';
            $this->speakerDuration = '5-7 mins';
            $this->speakerSpeechType = 'Speech Project';
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

    // Listening Master Report state
    public ?string $listeningMasterReport = null;

    // Minutes of Meeting state
    public ?string $minutesOfMeeting = null;

    public function mount(Meeting $meeting, ClubAccessService $access): void
    {
        $user = auth()->user();
        // Security check
        if (! $user || (! $user->isSuperAdmin() && ! $access->validateUserBelongsToClub($user->id, $meeting->club_id))) {
            abort(403, 'This meeting does not belong to your club.');
        }

        $this->meeting = $meeting;
        $this->listeningMasterReport = $meeting->listening_master_report;
        $this->minutesOfMeeting = $meeting->minutes_of_meeting;
    }

    public function downloadAgenda(string $theme = 'indigo')
    {
        $validThemes = ['indigo', 'emerald', 'blue', 'purple', 'rose', 'amber', 'cyan'];
        $theme = in_array($theme, $validThemes, true) ? $theme : 'indigo';

        $meeting = $this->meeting->load([
            'club:id,name,code',
            'roles.roleType:id,name,slug,sort_order',
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

    public function isMeetingLocked(): bool
    {
        return in_array($this->meeting->status, ['completed', 'cancelled'], true);
    }

    /**
     * Update meeting status directly from the view page.
     */
    public function updateStatus(string $newStatus, ?ClubAccessService $access = null): void
    {
        $access = $access ?? app(ClubAccessService::class);
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        $canUpdate = $user->isSuperAdmin() || 
            ($access->validateUserBelongsToClub($user->id, $this->meeting->club_id) && $user->can('meetings.update'));

        if (! $canUpdate) {
            abort(403, 'You do not have permission to change the meeting status.');
        }

        $validStatuses = array_keys(Meeting::statuses());
        if (! in_array($newStatus, $validStatuses, true)) {
            session()->flash('error', 'Invalid meeting status selected.');
            return;
        }

        $this->meeting->update(['status' => $newStatus]);
        $this->meeting->refresh();

        $statusLabel = Meeting::statuses()[$newStatus] ?? ucfirst($newStatus);
        session()->flash('success', "Meeting status successfully updated to {$statusLabel}.");
        $this->dispatch('flash', message: "Meeting status successfully updated to {$statusLabel}.", type: 'success');
    }

    /**
     * Save Minutes of Meeting directly from the view page.
     */
    public function saveMinutesOfMeeting(?string $content = null, ?ClubAccessService $access = null): void
    {
        $access = $access ?? app(ClubAccessService::class);
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        $canManage = $user->isSuperAdmin() || 
            ($access->validateUserBelongsToClub($user->id, $this->meeting->club_id) && 
             ($user->can('meetings.update') || $user->hasRole(['Secretary', 'President', 'VP Education'])));

        if (! $canManage) {
            abort(403, 'You do not have permission to manage Minutes of Meeting.');
        }

        $raw = $content !== null ? $content : (string) $this->minutesOfMeeting;
        $trimmed = trim($raw);
        $this->meeting->update([
            'minutes_of_meeting' => $trimmed !== '' ? $trimmed : null,
        ]);
        $this->minutesOfMeeting = $this->meeting->minutes_of_meeting;

        session()->flash('success', 'Minutes of Meeting saved successfully.');
        $this->dispatch('flash', message: 'Minutes of Meeting saved successfully.', type: 'success');
        $this->dispatch('minutes-of-meeting-saved');
    }

    private function validateCanVolunteer(): void
    {
        if ($this->isMeetingLocked()) {
            abort(403, 'This meeting is finalized and signups are closed.');
        }

        $user = auth()->user();
        if (! $user) {
            abort(401, 'Please sign in to volunteer.');
        }

        if (! $user->isSuperAdmin() && ! $user->belongsToClub($this->meeting->club_id)) {
            abort(403, 'You do not belong to this club.');
        }
    }

    public function signUpForRole(int $roleTypeId): void
    {
        $this->validateCanVolunteer();
        $user = auth()->user();

        $roleType = MeetingRoleType::where('is_active', true)->findOrFail($roleTypeId);

        // Check if role is already filled
        $existingRole = MeetingRole::where('meeting_id', $this->meeting->id)
            ->where('meeting_role_type_id', $roleType->id)
            ->first();

        if ($existingRole && $existingRole->user_id && (int) $existingRole->user_id !== (int) $user->id) {
            $this->dispatch('flash', message: 'This role has already been filled by another member.', type: 'error');
            return;
        }

        if ($existingRole) {
            $existingRole->update(['user_id' => $user->id]);
        } else {
            MeetingRole::create([
                'meeting_id'           => $this->meeting->id,
                'meeting_role_type_id' => $roleType->id,
                'user_id'              => $user->id,
            ]);
        }

        $this->dispatch('flash', message: 'You have signed up for this role!', type: 'success');
    }

    public function relinquishRole(int $meetingRoleId): void
    {
        if ($this->isMeetingLocked()) {
            abort(403, 'This meeting is finalized and roles cannot be modified.');
        }

        $role = MeetingRole::where('meeting_id', $this->meeting->id)->findOrFail($meetingRoleId);
        $user = auth()->user();

        if (! $user) {
            abort(401);
        }

        if (! $user->isSuperAdmin() && ! $user->can('meetings.update') && (int) $role->user_id !== (int) $user->id) {
            abort(403, 'You can only step down from your own role.');
        }

        $role->delete();
        $this->dispatch('flash', message: 'You have stepped down from this role.', type: 'info');
    }

    public function signUpAsSpeaker(): void
    {
        $this->validateCanVolunteer();
        $user = auth()->user();

        $alreadySpeaker = MeetingSpeaker::where('meeting_id', $this->meeting->id)
            ->where('user_id', $user->id)
            ->exists();
        if ($alreadySpeaker) {
            $this->dispatch('flash', message: 'You have already registered a speech for this meeting.', type: 'warning');
            return;
        }

        if ($this->speakerProjectId) {
            $proj = \App\Models\Project::find($this->speakerProjectId);
            if ($proj) {
                if (empty($this->speakerProject)) {
                    $this->speakerProject = $proj->name;
                }
                if (empty($this->speakerDuration) || $this->speakerDuration === '5-7 mins') {
                    $this->speakerDuration = $proj->formattedTiming();
                }
                if (empty($this->speakerSpeechType) || $this->speakerSpeechType === 'Speech Project') {
                    $this->speakerSpeechType = $proj->track ?: 'Speech Project';
                }
            }
        }

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
        $this->speakerDuration = '5-7 mins';
        $this->speakerSpeechType = 'Speech Project';
        $this->dispatch('speaker-signed-up');
        $this->dispatch('flash', message: 'You have signed up as a prepared speaker!', type: 'success');
    }

    public function relinquishSpeaker(int $speakerId): void
    {
        if ($this->isMeetingLocked()) {
            abort(403, 'This meeting is finalized and speaker slots cannot be modified.');
        }

        $speaker = MeetingSpeaker::where('meeting_id', $this->meeting->id)->findOrFail($speakerId);
        $user = auth()->user();

        if (! $user) {
            abort(401);
        }

        if (! $user->isSuperAdmin() && ! $user->can('meetings.update') && (int) $speaker->user_id !== (int) $user->id) {
            abort(403, 'You can only remove your own speaker slot.');
        }

        // Clean up linked evaluation and timer records
        $speaker->evaluation()?->delete();
        MeetingTimerLog::where('meeting_id', $this->meeting->id)
            ->where('speaker_type', 'prepared_speaker')
            ->where('reference_id', $speaker->id)
            ->delete();

        $speaker->delete();
        $this->dispatch('flash', message: 'Speaker slot removed.', type: 'info');
    }

    public function signUpForTtm(): void
    {
        $this->validateCanVolunteer();
        $user = auth()->user();

        $alreadyTtm = MeetingTtmSpeaker::where('meeting_id', $this->meeting->id)
            ->where('user_id', $user->id)
            ->exists();
        if ($alreadyTtm) {
            $this->dispatch('flash', message: 'You have already signed up for Table Topics in this meeting.', type: 'warning');
            return;
        }

        $this->validate([
            'ttmTopic' => 'nullable|string|max:255',
        ]);

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
        if ($this->isMeetingLocked()) {
            abort(403, 'This meeting is finalized and Table Topics cannot be modified.');
        }

        $ttm = MeetingTtmSpeaker::where('meeting_id', $this->meeting->id)->findOrFail($ttmId);
        $user = auth()->user();

        if (! $user) {
            abort(401);
        }

        if (! $user->isSuperAdmin() && ! $user->can('meetings.update') && (int) $ttm->user_id !== (int) $user->id) {
            abort(403, 'You can only remove your own Table Topics slot.');
        }

        MeetingTimerLog::where('meeting_id', $this->meeting->id)
            ->where('speaker_type', 'ttm_speaker')
            ->where('reference_id', $ttm->id)
            ->delete();

        $ttm->delete();
        $this->dispatch('flash', message: 'Table Topics slot removed.', type: 'info');
    }

    // =========================================================================
    // Authorization Helpers for Meeting Tools
    // =========================================================================

    public function canManageMeeting(): bool
    {
        if ($this->isMeetingLocked()) {
            return false;
        }

        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin() || $user->can('meetings.update') || $user->hasRole('admin');
    }

    public function canManageTimer(): bool
    {
        if ($this->isMeetingLocked()) {
            return false;
        }

        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($this->canManageMeeting()) {
            return true;
        }

        $this->meeting->loadMissing('roles.roleType');

        $isFacilitator = $this->meeting->roles->contains(function ($role) use ($user) {
            $slug = \Illuminate\Support\Str::slug($role->roleType?->slug ?: ($role->roleType?->name ?? ''));
            return (int) $role->user_id === (int) $user->id
                && in_array($slug, ['tmod', 'ge', 'general-evaluator', 'toastmaster'], true);
        });
        if ($isFacilitator) {
            return true;
        }

        $timerRole = $this->meeting->roles->first(function ($r) {
            $slug = \Illuminate\Support\Str::slug($r->roleType?->slug ?: ($r->roleType?->name ?? ''));
            return $slug === 'timer';
        });
        if ($timerRole && (int) $timerRole->user_id === (int) $user->id) {
            return true;
        }

        return false;
    }

    public function canManageAhCounter(): bool
    {
        if ($this->isMeetingLocked()) {
            return false;
        }

        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($this->canManageMeeting()) {
            return true;
        }

        $this->meeting->loadMissing('roles.roleType');

        $isFacilitator = $this->meeting->roles->contains(function ($role) use ($user) {
            $slug = \Illuminate\Support\Str::slug($role->roleType?->slug ?: ($role->roleType?->name ?? ''));
            return (int) $role->user_id === (int) $user->id
                && in_array($slug, ['tmod', 'ge', 'general-evaluator', 'toastmaster'], true);
        });
        if ($isFacilitator) {
            return true;
        }

        $ahRole = $this->meeting->roles->first(function ($r) {
            $slug = \Illuminate\Support\Str::slug($r->roleType?->slug ?: ($r->roleType?->name ?? ''));
            return in_array($slug, ['ah-counter', 'ah-count'], true);
        });
        if ($ahRole && (int) $ahRole->user_id === (int) $user->id) {
            return true;
        }

        return false;
    }

    public function canManageGrammarian(): bool
    {
        if ($this->isMeetingLocked()) {
            return false;
        }

        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($this->canManageMeeting()) {
            return true;
        }

        $this->meeting->loadMissing('roles.roleType');

        $isFacilitator = $this->meeting->roles->contains(function ($role) use ($user) {
            $slug = \Illuminate\Support\Str::slug($role->roleType?->slug ?: ($role->roleType?->name ?? ''));
            return (int) $role->user_id === (int) $user->id
                && in_array($slug, ['tmod', 'ge', 'general-evaluator', 'toastmaster'], true);
        });
        if ($isFacilitator) {
            return true;
        }

        $grammarianRole = $this->meeting->roles->first(function ($r) {
            $slug = \Illuminate\Support\Str::slug($r->roleType?->slug ?: ($r->roleType?->name ?? ''));
            return $slug === 'grammarian';
        });
        if ($grammarianRole && (int) $grammarianRole->user_id === (int) $user->id) {
            return true;
        }

        return false;
    }

    public function canManageListeningMaster(): bool
    {
        if ($this->isMeetingLocked()) {
            return false;
        }

        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($this->canManageMeeting()) {
            return true;
        }

        $this->meeting->loadMissing('roles.roleType');

        $isFacilitator = $this->meeting->roles->contains(function ($role) use ($user) {
            $slug = \Illuminate\Support\Str::slug($role->roleType?->slug ?: ($role->roleType?->name ?? ''));
            return (int) $role->user_id === (int) $user->id
                && in_array($slug, ['tmod', 'ge', 'general-evaluator', 'toastmaster'], true);
        });
        if ($isFacilitator) {
            return true;
        }

        $listeningRole = $this->meeting->roles->first(function ($r) {
            $slug = \Illuminate\Support\Str::slug($r->roleType?->slug ?: ($r->roleType?->name ?? ''));
            return in_array($slug, ['listening-master', 'hark-master', 'listening-post'], true);
        });
        if ($listeningRole && (int) $listeningRole->user_id === (int) $user->id) {
            return true;
        }

        return false;
    }

    // =========================================================================
    // Live Ah-Counter & Grammarian Facilitator Tools
    // =========================================================================

    #[Renderless]
    public function incrementFiller(int $userId, string $fillerType): void
    {
        if (! $this->canManageAhCounter()) {
            abort(403, 'You are not authorized to modify Ah-Counter counts.');
        }

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

    #[Renderless]
    public function decrementFiller(int $userId, string $fillerType): void
    {
        if (! $this->canManageAhCounter()) {
            abort(403, 'You are not authorized to modify Ah-Counter counts.');
        }

        $allowed = ['ah_count', 'um_count', 'er_count', 'like_count', 'you_know_count', 'so_count', 'repeats_count', 'other_count'];
        if (! in_array($fillerType, $allowed, true)) {
            return;
        }

        $log = MeetingAhCounterLog::where('meeting_id', $this->meeting->id)->where('user_id', $userId)->first();
        if ($log && $log->{$fillerType} > 0) {
            $log->decrement($fillerType);
        }
    }

    #[Renderless]
    public function incrementWordOfDay(int $userId): void
    {
        if (! $this->canManageGrammarian()) {
            abort(403, 'You are not authorized to modify Grammarian counts.');
        }

        $log = MeetingGrammarianLog::firstOrCreate(
            ['meeting_id' => $this->meeting->id, 'user_id' => $userId],
            ['word_of_day_count' => 0]
        );
        $log->increment('word_of_day_count');
    }

    #[Renderless]
    public function decrementWordOfDay(int $userId): void
    {
        if (! $this->canManageGrammarian()) {
            abort(403, 'You are not authorized to modify Grammarian counts.');
        }

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

    #[Renderless]
    public function saveGrammarNotes(): void
    {
        if (! $this->canManageGrammarian()) {
            abort(403, 'You are not authorized to edit Grammarian notes.');
        }

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

    #[Renderless]
    public function saveAhCounterCounts(array $ahCounts): void
    {
        if (! $this->canManageAhCounter()) {
            abort(403, 'You are not authorized to save Ah-Counter counts.');
        }

        $this->saveAllCounts($ahCounts, null, 'ah_counter');
    }

    #[Renderless]
    public function saveGrammarianCounts(array $grammarCounts): void
    {
        if (! $this->canManageGrammarian()) {
            abort(403, 'You are not authorized to save Grammarian counts.');
        }

        $this->saveAllCounts(null, $grammarCounts, 'grammarian');
    }

    #[Renderless]
    public function saveAllCounts(?array $ahCounts = null, ?array $grammarCounts = null, ?string $role = null): void
    {
        if ($this->isMeetingLocked()) {
            abort(403, 'This meeting is finalized and facilitator counts cannot be modified.');
        }

        $shouldProcessAh = ($role === 'ah_counter') || ($role === null && $ahCounts !== null && ! empty($ahCounts));
        $shouldProcessGrammar = ($role === 'grammarian') || ($role === null && $grammarCounts !== null && ! empty($grammarCounts));

        if ($shouldProcessAh && ! $this->canManageAhCounter()) {
            abort(403, 'You are not authorized to save Ah-Counter counts.');
        }

        if ($shouldProcessGrammar && ! $this->canManageGrammarian()) {
            abort(403, 'You are not authorized to save Grammarian counts.');
        }

        if ($shouldProcessAh && ! empty($ahCounts)) {
            $allowed = ['ah_count', 'um_count', 'er_count', 'like_count', 'you_know_count', 'so_count', 'repeats_count', 'other_count'];

            $candidateUserIds = array_filter(array_map('intval', array_keys($ahCounts)), fn ($id) => $id > 0);
            $validUserIds = ! empty($candidateUserIds)
                ? User::whereIn('id', $candidateUserIds)->pluck('id')->flip()->toArray()
                : [];

            $existingLogs = ! empty($validUserIds)
                ? MeetingAhCounterLog::where('meeting_id', $this->meeting->id)
                    ->whereIn('user_id', array_keys($validUserIds))
                    ->get()
                    ->keyBy('user_id')
                : collect();

            foreach ($ahCounts as $userId => $counts) {
                $userId = (int) $userId;
                if ($userId <= 0 || ! is_array($counts) || ! isset($validUserIds[$userId])) {
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

                $existing = $existingLogs->get($userId);
                if (! empty($data) && ($hasNonZero || $existing)) {
                    if ($existing) {
                        $existing->update($data);
                    } else {
                        MeetingAhCounterLog::create(array_merge(
                            ['meeting_id' => $this->meeting->id, 'user_id' => $userId],
                            $data
                        ));
                    }
                }
            }
        }

        if ($shouldProcessGrammar && ! empty($grammarCounts)) {
            $candidateUserIds = array_filter(array_map('intval', array_keys($grammarCounts)), fn ($id) => $id > 0);
            $validUserIds = ! empty($candidateUserIds)
                ? User::whereIn('id', $candidateUserIds)->pluck('id')->flip()->toArray()
                : [];

            $existingLogs = ! empty($validUserIds)
                ? MeetingGrammarianLog::where('meeting_id', $this->meeting->id)
                    ->whereIn('user_id', array_keys($validUserIds))
                    ->get()
                    ->keyBy('user_id')
                : collect();

            foreach ($grammarCounts as $userId => $counts) {
                $userId = (int) $userId;
                if ($userId <= 0 || ! isset($validUserIds[$userId])) {
                    continue;
                }

                $existing = $existingLogs->get($userId);

                if (is_array($counts)) {
                    $wodCount = max(0, (int) ($counts['word_of_day_count'] ?? 0));
                    $goodPhrases = isset($counts['good_phrases']) && trim((string)$counts['good_phrases']) !== '' ? trim((string)$counts['good_phrases']) : null;
                    $awkwardPhrases = isset($counts['awkward_phrases']) && trim((string)$counts['awkward_phrases']) !== '' ? trim((string)$counts['awkward_phrases']) : null;
                    $notes = isset($counts['notes']) && trim((string)$counts['notes']) !== '' ? trim((string)$counts['notes']) : null;

                    $hasData = ($wodCount > 0 || $goodPhrases !== null || $awkwardPhrases !== null || $notes !== null);

                    if ($hasData || $existing) {
                        $updateData = [
                            'word_of_day_count' => $wodCount,
                            'good_phrases'      => $goodPhrases,
                            'awkward_phrases'   => $awkwardPhrases,
                            'notes'             => $notes,
                        ];
                        if ($existing) {
                            $existing->update($updateData);
                        } else {
                            MeetingGrammarianLog::create(array_merge(
                                ['meeting_id' => $this->meeting->id, 'user_id' => $userId],
                                $updateData
                            ));
                        }
                    }
                } else {
                    $wodCount = max(0, (int) $counts);
                    if ($wodCount > 0 || $existing) {
                        if ($existing) {
                            $existing->update(['word_of_day_count' => $wodCount]);
                        } else {
                            MeetingGrammarianLog::create([
                                'meeting_id'        => $this->meeting->id,
                                'user_id'           => $userId,
                                'word_of_day_count' => $wodCount,
                            ]);
                        }
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

    #[Renderless]
    public function syncAhCounts(int $userId, array $counts): void
    {
        if (! $this->canManageAhCounter()) {
            return;
        }

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

    #[Renderless]
    public function syncGrammarCount(int $userId, int $count): void
    {
        if (! $this->canManageGrammarian()) {
            return;
        }

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
        if ($this->isMeetingLocked()) {
            abort(403, 'This meeting is finalized and evaluation notes cannot be modified.');
        }

        $evaluation = MeetingEvaluation::where('meeting_id', $this->meeting->id)
            ->where('id', $evaluationId)
            ->firstOrFail();

        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        $isEvaluator = (int) $evaluation->evaluator_user_id === (int) $user->id;
        $canManageMeeting = $this->canManageMeeting();

        if (! $isEvaluator && ! $canManageMeeting) {
            abort(403, 'You are not authorized to edit this evaluation.');
        }

        $evaluation->update([
            'notes' => trim($notes) !== '' ? trim($notes) : null,
        ]);

        $this->dispatch('flash', message: 'Evaluation feedback saved successfully!', type: 'success');
    }

    #[Renderless]
    public function saveTimerLogs(array $timerData = []): void
    {
        if ($this->isMeetingLocked() || ! $this->canManageTimer()) {
            abort(403, 'This meeting is finalized or you are not authorized to edit the Timer sheet.');
        }

        $meetingId = $this->meeting->id;
        $existingTimerLogs = MeetingTimerLog::where('meeting_id', $meetingId)->get();

        $candidateUserIds = array_filter(array_map('intval', array_column($timerData, 'user_id')), fn ($id) => $id > 0);
        $validUserIds = ! empty($candidateUserIds)
            ? User::whereIn('id', $candidateUserIds)->pluck('id')->flip()->toArray()
            : [];

        foreach ($timerData as $item) {
            if (empty($item['speaker_type']) || empty($item['user_id'])) {
                continue;
            }

            $userId = (int) $item['user_id'];
            if (! isset($validUserIds[$userId])) {
                continue;
            }

            $speakerType = $item['speaker_type'];
            $refId = ! empty($item['reference_id']) ? (int) $item['reference_id'] : null;
            $allotted = $item['allotted_time'] ?? null;
            $timeTaken = isset($item['time_taken']) && trim((string)$item['time_taken']) !== '' ? trim((string)$item['time_taken']) : null;
            $status = in_array($item['status'] ?? '', ['within_time', 'over_time', 'under_time', 'disqualified'], true)
                ? $item['status']
                : 'within_time';

            $existing = $existingTimerLogs->first(function ($l) use ($speakerType, $refId, $userId) {
                if ($l->speaker_type !== $speakerType) {
                    return false;
                }
                return $refId ? (int) $l->reference_id === (int) $refId : (int) $l->user_id === (int) $userId;
            });

            if ($timeTaken !== null || $existing) {
                $data = [
                    'user_id'       => $userId,
                    'allotted_time' => $allotted,
                    'time_taken'    => $timeTaken,
                    'status'        => $status,
                    'notes'         => ! empty($item['notes']) ? trim($item['notes']) : null,
                ];

                if ($existing) {
                    $existing->update($data);
                } else {
                    $newLog = MeetingTimerLog::create(array_merge([
                        'meeting_id'   => $meetingId,
                        'speaker_type' => $speakerType,
                        'reference_id' => $refId,
                    ], $data));
                    $existingTimerLogs->push($newLog);
                }
            }
        }

        $this->dispatch('timer-logs-saved');
        $this->dispatch('flash', message: 'Timer sheet saved successfully!', type: 'success');
    }

    public function saveListeningMasterReport(): void
    {
        if ($this->isMeetingLocked()) {
            abort(403, 'This meeting is finalized and the Listening Master report cannot be modified.');
        }

        if (! $this->canManageListeningMaster()) {
            abort(403, 'You are not authorized to edit the Listening Master report.');
        }

        $reportText = trim((string) $this->listeningMasterReport);
        $cleanText = strip_tags($reportText);
        $finalReport = ($cleanText !== '' || str_contains($reportText, '<img') || str_contains($reportText, '<hr'))
            ? $reportText
            : null;

        $this->meeting->update([
            'listening_master_report' => $finalReport,
        ]);

        $this->listeningMasterReport = $finalReport;
        $this->dispatch('listening-master-report-saved');
        $this->dispatch('flash', message: 'Listening Master report saved successfully!', type: 'success');
    }

    public function render(ClubAccessService $access)
    {
        $meeting = $this->meeting->load([
            'club:id,name,code',
            'creator:id,name',
            'roles.roleType:id,name,slug,sort_order',
            'roles.user:id,name,email',
            'speakers.user:id,name,email',
            'speakers.projectModel:id,name,track,level,min_minutes,max_minutes,default_duration',
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
        $currentUser = auth()->user();
        $currentUserId = $currentUser?->id;
        $canVolunteer = in_array($meeting->status, ['draft', 'scheduled']) &&
                        ($currentUser && ($currentUser->isSuperAdmin() || $currentUser->belongsToClub($meeting->club_id)));

        $canUpdateStatus = $currentUser && ($currentUser->isSuperAdmin() || ($access->validateUserBelongsToClub($currentUser->id, $meeting->club_id) && $currentUser->can('meetings.update')));
        $canManageMinutes = $currentUser && ($currentUser->isSuperAdmin() || ($access->validateUserBelongsToClub($currentUser->id, $meeting->club_id) && ($currentUser->can('meetings.update') || $currentUser->hasRole(['Secretary', 'President', 'VP Education']))));

        $projects = \App\Models\Project::active()
            ->orderBy('sort_order')
            ->orderBy('level')
            ->orderBy('name')
            ->get(['id', 'name', 'level', 'min_minutes', 'max_minutes', 'track', 'default_duration']);

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
                $entry['roles'][] = $role->roleType?->name ?? 'Role Player';
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
                    'roles'    => ['Participant'],
                    'category' => 'Participant',
                ]);
            }
        }

        foreach ($meeting->grammarianLogs as $log) {
            if ($log->user && ! $activeParticipants->has($log->user_id)) {
                $activeParticipants->put($log->user_id, [
                    'id'       => $log->user_id,
                    'name'     => $log->user->name,
                    'roles'    => ['Participant'],
                    'category' => 'Participant',
                ]);
            }
        }

        $meetingParticipants = $activeParticipants->values();

        $canManageTimer = $this->canManageTimer();
        $canManageAhCounter = $this->canManageAhCounter();
        $canManageGrammarian = $this->canManageGrammarian();
        $canManageListeningMaster = $this->canManageListeningMaster();

        $assignedTimer = $meeting->roles->first(fn ($r) => \Illuminate\Support\Str::slug($r->roleType?->slug ?: ($r->roleType?->name ?? '')) === 'timer');
        $assignedAhCounter = $meeting->roles->first(fn ($r) => in_array(\Illuminate\Support\Str::slug($r->roleType?->slug ?: ($r->roleType?->name ?? '')), ['ah-counter', 'ah-count'], true));
        $assignedGrammarian = $meeting->roles->first(fn ($r) => \Illuminate\Support\Str::slug($r->roleType?->slug ?: ($r->roleType?->name ?? '')) === 'grammarian');
        $assignedListeningMaster = $meeting->roles->first(fn ($r) => in_array(\Illuminate\Support\Str::slug($r->roleType?->slug ?: ($r->roleType?->name ?? '')), ['listening-master', 'hark-master', 'listening-post'], true));

        $assignedTimerName = $assignedTimer?->user?->name ?? 'Unassigned';
        $assignedAhCounterName = $assignedAhCounter?->user?->name ?? 'Unassigned';
        $assignedGrammarianName = $assignedGrammarian?->user?->name ?? 'Unassigned';
        $assignedListeningMasterName = $assignedListeningMaster?->user?->name ?? 'Unassigned';
        $isMeetingLocked = $this->isMeetingLocked();

        return view('livewire.meetings.meeting-show', compact(
            'meeting',
            'allRoleTypes',
            'meetingParticipants',
            'currentUserId',
            'canVolunteer',
            'projects',
            'initialAhLogs',
            'initialGrammarLogs',
            'initialTimerLogs',
            'canManageTimer',
            'canManageAhCounter',
            'canManageGrammarian',
            'canManageListeningMaster',
            'canUpdateStatus',
            'canManageMinutes',
            'assignedTimerName',
            'assignedAhCounterName',
            'assignedGrammarianName',
            'assignedListeningMasterName',
            'isMeetingLocked'
        ))->title('Meeting #' . $meeting->meeting_number . ' — ' . ($meeting->club?->name ?? 'Speech Club'));
    }
}
