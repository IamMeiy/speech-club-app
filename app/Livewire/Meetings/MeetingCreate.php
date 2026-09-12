<?php

namespace App\Livewire\Meetings;

use App\Livewire\Concerns\WithClubContext;
use App\Models\Club;
use App\Models\Meeting;
use App\Models\MeetingEvaluation;
use App\Models\MeetingRole;
use App\Models\MeetingRoleType;
use App\Models\MeetingSpeaker;
use App\Models\MeetingTtmSpeaker;
use App\Models\User;
use App\Services\ClubAccessService;
use App\Services\ClubContextService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Create Meeting')]
class MeetingCreate extends Component
{
    use WithClubContext;

    public ?int $selectedClubId = null;

    // Meeting information
    public string $meeting_date   = '';
    public string $meeting_number = '';
    public string $theme          = '';
    public string $venue          = '';
    public string $status         = 'scheduled';
    public string $notes          = '';
    public string $start_time     = '';
    public string $end_time       = '';
    public string $word_of_the_day = '';
    public string $word_part_of_speech = '';
    public string $word_definition = '';
    public string $word_example_sentence = '';

    // Fixed role assignments: keyed by meeting_role_type_id
    public array $roleAssignments = [];

    // Dynamic speakers
    public array $speakers = [
        ['user_id' => '', 'project_id' => '', 'topic' => '', 'speech_type' => '', 'project' => '', 'duration' => ''],
    ];

    // Dynamic TTM speakers
    public array $ttmSpeakers = [
        ['user_id' => '', 'topic' => '', 'duration' => ''],
    ];

    // Dynamic Evaluations
    public array $evaluations = [
        ['speaker_index' => 0, 'evaluator_user_id' => ''],
    ];

    // Members of currently selected club for reactive Alpine binding
    public array $membersList = [];

    public function mount(ClubContextService $clubContext, ClubAccessService $access): void
    {
        $user = auth()->user();

        if ($user->isClubUser()) {
            $club = $user->primaryClub();
            $this->selectedClubId = $club?->id;
        } else {
            // Global user / Super Admin
            $currentClub = $clubContext->currentClub();
            if ($currentClub) {
                $this->selectedClubId = $currentClub->id;
            } else {
                $accessible = $access->getAccessibleClubs($user);
                $this->selectedClubId = $accessible->first()?->id;
            }
        }

        $this->updateClubData();
    }

    public function updatedSelectedClubId(): void
    {
        // Reset role assignments and dynamic speaker/evaluator selections when club changes
        $this->roleAssignments = [];
        foreach ($this->speakers as $i => $s) {
            $this->speakers[$i]['user_id'] = '';
        }
        foreach ($this->ttmSpeakers as $i => $t) {
            $this->ttmSpeakers[$i]['user_id'] = '';
        }
        foreach ($this->evaluations as $i => $e) {
            $this->evaluations[$i]['evaluator_user_id'] = '';
        }

        $this->updateClubData();
    }

    public function updateClubData(): void
    {
        $activeClubId = auth()->user()->isClubUser() ? auth()->user()->primaryClub()?->id : $this->selectedClubId;
        $currentClub  = $activeClubId ? Club::find($activeClubId) : null;

        if ($currentClub) {
            $this->meeting_number = (string) $currentClub->nextMeetingNumber();
            $this->meeting_date   = now()->addDays(7)->format('Y-m-d');
            $this->membersList    = User::inClub($currentClub->id)
                ->active()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($u) => ['id' => (string) $u->id, 'name' => $u->name])
                ->toArray();
        } else {
            $this->membersList = [];
        }

        $roleTypes = MeetingRoleType::active()->get();
        foreach ($roleTypes as $roleType) {
            if (! isset($this->roleAssignments[$roleType->id])) {
                $this->roleAssignments[$roleType->id] = '';
            }
        }
    }

    public function save(ClubAccessService $access): void
    {
        $this->authorize('meetings.create');

        $user = auth()->user();
        $clubId = $user->isClubUser() ? $user->primaryClub()?->id : $this->selectedClubId;

        if (! $clubId) {
            $this->addError('selectedClubId', 'Please select a club before creating a meeting.');
            return;
        }

        $club = Club::findOrFail($clubId);

        // Security: verify user has access to this club
        if (! $user->isSuperAdmin() && ! $access->validateUserBelongsToClub($user->id, $club->id)) {
            abort(403, 'You do not have permission to create meetings for this club.');
        }

        $this->validate([
            'meeting_date'          => 'required|date',
            'meeting_number'        => 'required|integer|min:1|unique:meetings,meeting_number,NULL,id,club_id,' . $club->id,
            'theme'                 => 'nullable|string|max:255',
            'venue'                 => 'nullable|string|max:255',
            'status'                => 'required|in:draft,scheduled,completed,cancelled',
            'notes'                 => 'nullable|string',
            'start_time'            => 'nullable|date_format:H:i',
            'end_time'              => 'nullable|date_format:H:i',
            'word_of_the_day'       => 'nullable|string|max:100',
            'word_part_of_speech'   => 'nullable|string|max:50',
            'word_definition'       => 'nullable|string|max:500',
            'word_example_sentence' => 'nullable|string|max:500',
        ]);

        // Validate all assigned users belong to this club
        $allUserIds = array_filter(array_values($this->roleAssignments));
        $speakerIds = array_filter(array_column($this->speakers, 'user_id'));
        $ttmIds     = array_filter(array_column($this->ttmSpeakers, 'user_id'));
        $evalIds    = array_filter(array_column($this->evaluations, 'evaluator_user_id'));

        $allIds = array_merge($allUserIds, $speakerIds, $ttmIds, $evalIds);

        if (! empty($allIds) && ! $access->validateAllUsersBelongToClub($allIds, $club->id)) {
            $this->addError('meeting_date', 'One or more selected members do not belong to this club.');
            return;
        }

        // Create meeting
        $meeting = Meeting::create([
            'club_id'               => $club->id,
            'meeting_number'        => (int) $this->meeting_number,
            'meeting_date'          => $this->meeting_date,
            'theme'                 => $this->theme ?: null,
            'venue'                 => $this->venue ?: null,
            'status'                => $this->status,
            'notes'                 => $this->notes ?: null,
            'start_time'            => $this->start_time ?: null,
            'end_time'              => $this->end_time ?: null,
            'word_of_the_day'       => $this->word_of_the_day ?: null,
            'word_part_of_speech'   => $this->word_part_of_speech ?: null,
            'word_definition'       => $this->word_definition ?: null,
            'word_example_sentence' => $this->word_example_sentence ?: null,
            'created_by'            => auth()->id(),
        ]);

        // Save fixed role assignments
        foreach ($this->roleAssignments as $roleTypeId => $userId) {
            if ($userId) {
                MeetingRole::create([
                    'meeting_id'           => $meeting->id,
                    'meeting_role_type_id' => $roleTypeId,
                    'user_id'              => $userId,
                ]);
            }
        }

        // Save prepared speakers
        foreach ($this->speakers as $slot => $speakerData) {
            if (! empty($speakerData['user_id'])) {
                MeetingSpeaker::create([
                    'meeting_id'  => $meeting->id,
                    'user_id'     => $speakerData['user_id'],
                    'project_id'  => ! empty($speakerData['project_id']) ? $speakerData['project_id'] : null,
                    'slot'        => $slot + 1,
                    'speech_type' => $speakerData['speech_type'] ?? null,
                    'project'     => $speakerData['project'] ?? null,
                    'topic'       => $speakerData['topic'] ?? null,
                    'duration'    => $speakerData['duration'] ?? null,
                ]);
            }
        }

        // Save TTM speakers
        foreach ($this->ttmSpeakers as $slot => $ttmData) {
            if (! empty($ttmData['user_id'])) {
                MeetingTtmSpeaker::create([
                    'meeting_id' => $meeting->id,
                    'user_id'    => $ttmData['user_id'],
                    'slot'       => $slot + 1,
                    'topic'      => $ttmData['topic'] ?? null,
                    'duration'   => $ttmData['duration'] ?? null,
                ]);
            }
        }

        // Save evaluations (after speakers are created)
        $savedSpeakers = $meeting->speakers()->get();
        foreach ($this->evaluations as $evalData) {
            if (! empty($evalData['evaluator_user_id'])) {
                $speakerRecord = $savedSpeakers->get((int) ($evalData['speaker_index'] ?? 0));
                if ($speakerRecord) {
                    MeetingEvaluation::create([
                        'meeting_id'        => $meeting->id,
                        'speaker_id'        => $speakerRecord->id,
                        'evaluator_user_id' => $evalData['evaluator_user_id'],
                    ]);
                }
            }
        }

        $this->dispatch('flash', message: 'Meeting created successfully.', type: 'success');
        $this->redirect(route('meetings.show', $meeting), navigate: true);
    }

    public function updatedSpeakers($value, $key): void
    {
        // Handle speakers.{index}.project_id changes
        if (str_ends_with($key, '.project_id')) {
            $parts = explode('.', $key);
            $index = (int) ($parts[0] ?? 0);

            if ($value) {
                $proj = \App\Models\Project::find($value);
                if ($proj && isset($this->speakers[$index])) {
                    $this->speakers[$index]['project']     = $proj->name;
                    $this->speakers[$index]['duration']    = $proj->formattedTiming();
                    $this->speakers[$index]['speech_type'] = $proj->track ?: 'Pathways Project';
                }
            }
        }
    }

    public function render(ClubAccessService $access)
    {
        $user            = auth()->user();
        $isGlobal        = $user->isGlobalUser();
        $accessibleClubs = $access->getAccessibleClubs($user);

        $activeClubId = $user->isClubUser() ? $user->primaryClub()?->id : $this->selectedClubId;
        $currentClub  = $activeClubId ? Club::find($activeClubId) : null;
        $members      = $currentClub ? User::inClub($currentClub->id)->active()->orderBy('name')->get() : collect();
        $roleTypes    = MeetingRoleType::active()->get();
        $projects     = \App\Models\Project::active()->orderBy('level')->orderBy('sort_order')->orderBy('name')->get();

        return view('livewire.meetings.meeting-create', compact(
            'isGlobal',
            'accessibleClubs',
            'currentClub',
            'members',
            'roleTypes',
            'projects'
        ));
    }
}
