<?php

namespace App\Livewire\Meetings;

use App\Livewire\Concerns\WithClubContext;
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

    // Meeting information
    public string $meeting_date   = '';
    public string $meeting_number = '';
    public string $theme          = '';
    public string $venue          = '';
    public string $status         = 'scheduled';
    public string $notes          = '';

    // Fixed role assignments: keyed by meeting_role_type_id
    public array $roleAssignments = [];

    // Dynamic speakers (each: ['user_id' => '', 'topic' => '', 'speech_type' => '', 'project' => '', 'duration' => ''])
    public array $speakers = [
        ['user_id' => '', 'topic' => '', 'speech_type' => '', 'project' => '', 'duration' => ''],
    ];

    // TTM speakers (each: ['user_id' => '', 'topic' => '', 'duration' => ''])
    public array $ttmSpeakers = [
        ['user_id' => '', 'topic' => '', 'duration' => ''],
    ];

    // Evaluations (each: ['speaker_index' => 0, 'evaluator_user_id' => ''])
    public array $evaluations = [
        ['speaker_index' => 0, 'evaluator_user_id' => ''],
    ];

    public function mount(ClubContextService $clubContext): void
    {
        $club = $clubContext->currentClub();
        if (! $club) {
            // Global user with no club selected
            session()->flash('error', 'Please select a club before creating a meeting.');
            $this->redirect(route('dashboard'));
            return;
        }

        // Auto-suggest next meeting number
        $this->meeting_number = (string) $club->nextMeetingNumber();
        $this->meeting_date   = now()->addDays(7)->format('Y-m-d');

        // Initialize roleAssignments array keyed by role type ID
        $roleTypes = MeetingRoleType::active()->get();
        foreach ($roleTypes as $roleType) {
            $this->roleAssignments[$roleType->id] = '';
        }
    }

    // -----------------------------------------------------------------------
    // Speakers management
    // -----------------------------------------------------------------------

    public function addSpeaker(): void
    {
        $this->speakers[] = ['user_id' => '', 'topic' => '', 'speech_type' => '', 'project' => '', 'duration' => ''];
    }

    public function removeSpeaker(int $index): void
    {
        if (count($this->speakers) > 1) {
            array_splice($this->speakers, $index, 1);
            $this->speakers = array_values($this->speakers);
        }
    }

    // -----------------------------------------------------------------------
    // TTM speakers management
    // -----------------------------------------------------------------------

    public function addTtmSpeaker(): void
    {
        $this->ttmSpeakers[] = ['user_id' => '', 'topic' => '', 'duration' => ''];
    }

    public function removeTtmSpeaker(int $index): void
    {
        if (count($this->ttmSpeakers) > 1) {
            array_splice($this->ttmSpeakers, $index, 1);
            $this->ttmSpeakers = array_values($this->ttmSpeakers);
        }
    }

    // -----------------------------------------------------------------------
    // Evaluations management
    // -----------------------------------------------------------------------

    public function addEvaluation(): void
    {
        $this->evaluations[] = ['speaker_index' => 0, 'evaluator_user_id' => ''];
    }

    public function removeEvaluation(int $index): void
    {
        if (count($this->evaluations) > 1) {
            array_splice($this->evaluations, $index, 1);
            $this->evaluations = array_values($this->evaluations);
        }
    }

    // -----------------------------------------------------------------------
    // Save
    // -----------------------------------------------------------------------

    public function save(ClubContextService $clubContext, ClubAccessService $access): void
    {
        $this->authorize('meetings.create');

        $club = $clubContext->currentClub();
        if (! $club) {
            abort(403, 'No club context.');
        }

        $this->validate([
            'meeting_date'   => 'required|date',
            'meeting_number' => 'required|integer|min:1|unique:meetings,meeting_number,NULL,id,club_id,' . $club->id,
            'theme'          => 'nullable|string|max:255',
            'venue'          => 'nullable|string|max:255',
            'status'         => 'required|in:draft,scheduled,completed,cancelled',
            'notes'          => 'nullable|string',
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
            'club_id'        => $club->id,
            'meeting_number' => (int) $this->meeting_number,
            'meeting_date'   => $this->meeting_date,
            'theme'          => $this->theme ?: null,
            'venue'          => $this->venue ?: null,
            'status'         => $this->status,
            'notes'          => $this->notes ?: null,
            'created_by'     => auth()->id(),
        ]);

        // Save fixed role assignments
        foreach ($this->roleAssignments as $roleTypeId => $userId) {
            if ($userId) {
                MeetingRole::create([
                    'meeting_id'          => $meeting->id,
                    'meeting_role_type_id' => $roleTypeId,
                    'user_id'             => $userId,
                ]);
            }
        }

        // Save prepared speakers
        foreach ($this->speakers as $slot => $speakerData) {
            if ($speakerData['user_id']) {
                MeetingSpeaker::create([
                    'meeting_id'  => $meeting->id,
                    'user_id'     => $speakerData['user_id'],
                    'slot'        => $slot + 1,
                    'speech_type' => $speakerData['speech_type'] ?: null,
                    'project'     => $speakerData['project'] ?: null,
                    'topic'       => $speakerData['topic'] ?: null,
                    'duration'    => $speakerData['duration'] ?: null,
                ]);
            }
        }

        // Save TTM speakers
        foreach ($this->ttmSpeakers as $slot => $ttmData) {
            if ($ttmData['user_id']) {
                MeetingTtmSpeaker::create([
                    'meeting_id' => $meeting->id,
                    'user_id'    => $ttmData['user_id'],
                    'slot'       => $slot + 1,
                    'topic'      => $ttmData['topic'] ?: null,
                    'duration'   => $ttmData['duration'] ?: null,
                ]);
            }
        }

        // Save evaluations (after speakers are created)
        $savedSpeakers = $meeting->speakers()->get();
        foreach ($this->evaluations as $evalData) {
            if ($evalData['evaluator_user_id']) {
                $speakerRecord = $savedSpeakers->get($evalData['speaker_index']);
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

    public function render(ClubContextService $clubContext)
    {
        $club      = $clubContext->currentClub();
        $members   = $club ? User::inClub($club->id)->active()->orderBy('name')->get() : collect();
        $roleTypes = MeetingRoleType::active()->get();

        return view('livewire.meetings.meeting-create', compact('club', 'members', 'roleTypes'));
    }
}
