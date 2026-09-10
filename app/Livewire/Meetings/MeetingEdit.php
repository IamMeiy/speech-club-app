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
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Edit Meeting')]
class MeetingEdit extends Component
{
    use WithClubContext;

    public Meeting $meeting;

    public string $meeting_date   = '';
    public string $meeting_number = '';
    public string $theme          = '';
    public string $venue          = '';
    public string $status         = 'scheduled';
    public string $notes          = '';

    public array $roleAssignments = [];
    public array $speakers        = [];
    public array $ttmSpeakers     = [];
    public array $evaluations     = [];
    public array $membersList     = [];

    public function mount(Meeting $meeting, ClubAccessService $access): void
    {
        $user = auth()->user();
        // Security: user must belong to meeting's club or be Super Admin
        if (! $user->isSuperAdmin() && ! $access->validateUserBelongsToClub($user->id, $meeting->club_id)) {
            abort(403, 'This meeting does not belong to your club.');
        }

        $this->meeting        = $meeting;
        $this->meeting_date   = $meeting->meeting_date->format('Y-m-d');
        $this->meeting_number = (string) $meeting->meeting_number;
        $this->theme          = $meeting->theme ?? '';
        $this->venue          = $meeting->venue ?? '';
        $this->status         = $meeting->status;
        $this->notes          = $meeting->notes ?? '';

        // Load club members for dynamic dropdowns
        $this->membersList = User::inClub($meeting->club_id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($u) => ['id' => (string) $u->id, 'name' => $u->name])
            ->toArray();

        // Load role assignments
        $roleTypes = MeetingRoleType::active()->get();
        foreach ($roleTypes as $roleType) {
            $existingRole = $meeting->roles->firstWhere('meeting_role_type_id', $roleType->id);
            $this->roleAssignments[$roleType->id] = $existingRole?->user_id ? (string) $existingRole->user_id : '';
        }

        // Load speakers
        $this->speakers = $meeting->speakers->map(fn ($s) => [
            'user_id'     => $s->user_id ? (string) $s->user_id : '',
            'topic'       => $s->topic ?? '',
            'speech_type' => $s->speech_type ?? '',
            'project'     => $s->project ?? '',
            'duration'    => $s->duration ?? '',
        ])->toArray();

        if (empty($this->speakers)) {
            $this->speakers = [['user_id' => '', 'topic' => '', 'speech_type' => '', 'project' => '', 'duration' => '']];
        }

        // Load TTM speakers
        $this->ttmSpeakers = $meeting->ttmSpeakers->map(fn ($s) => [
            'user_id'  => $s->user_id ? (string) $s->user_id : '',
            'topic'    => $s->topic ?? '',
            'duration' => $s->duration ?? '',
        ])->toArray();

        if (empty($this->ttmSpeakers)) {
            $this->ttmSpeakers = [['user_id' => '', 'topic' => '', 'duration' => '']];
        }

        // Load evaluations
        $speakerIds = $meeting->speakers->pluck('id')->toArray();
        $this->evaluations = $meeting->evaluations->map(fn ($e) => [
            'speaker_index'     => array_search($e->speaker_id, $speakerIds) !== false ? array_search($e->speaker_id, $speakerIds) : 0,
            'evaluator_user_id' => $e->evaluator_user_id ? (string) $e->evaluator_user_id : '',
        ])->toArray();

        if (empty($this->evaluations)) {
            $this->evaluations = [['speaker_index' => 0, 'evaluator_user_id' => '']];
        }
    }

    public function save(ClubAccessService $access): void
    {
        $this->authorize('meetings.update');

        $this->validate([
            'meeting_date'   => 'required|date',
            'meeting_number' => 'required|integer|min:1|unique:meetings,meeting_number,' . $this->meeting->id . ',id,club_id,' . $this->meeting->club_id,
            'theme'          => 'nullable|string|max:255',
            'venue'          => 'nullable|string|max:255',
            'status'         => 'required|in:draft,scheduled,completed,cancelled',
            'notes'          => 'nullable|string',
        ]);

        // Backend validation: all users must belong to meeting's club
        $allIds = array_merge(
            array_filter(array_values($this->roleAssignments)),
            array_filter(array_column($this->speakers, 'user_id')),
            array_filter(array_column($this->ttmSpeakers, 'user_id')),
            array_filter(array_column($this->evaluations, 'evaluator_user_id'))
        );

        if (! empty($allIds) && ! $access->validateAllUsersBelongToClub($allIds, $this->meeting->club_id)) {
            $this->addError('meeting_date', 'One or more selected members do not belong to this club.');
            return;
        }

        $this->meeting->update([
            'meeting_number' => (int) $this->meeting_number,
            'meeting_date'   => $this->meeting_date,
            'theme'          => $this->theme ?: null,
            'venue'          => $this->venue ?: null,
            'status'         => $this->status,
            'notes'          => $this->notes ?: null,
        ]);

        // Sync fixed roles
        $this->meeting->roles()->delete();
        foreach ($this->roleAssignments as $roleTypeId => $userId) {
            if ($userId) {
                MeetingRole::create([
                    'meeting_id'           => $this->meeting->id,
                    'meeting_role_type_id' => $roleTypeId,
                    'user_id'              => $userId,
                ]);
            }
        }

        // Sync speakers
        $this->meeting->speakers()->delete();
        foreach ($this->speakers as $slot => $s) {
            if (! empty($s['user_id'])) {
                MeetingSpeaker::create([
                    'meeting_id'  => $this->meeting->id,
                    'user_id'     => $s['user_id'],
                    'slot'        => $slot + 1,
                    'speech_type' => $s['speech_type'] ?? null,
                    'project'     => $s['project'] ?? null,
                    'topic'       => $s['topic'] ?? null,
                    'duration'    => $s['duration'] ?? null,
                ]);
            }
        }

        // Sync TTM speakers
        $this->meeting->ttmSpeakers()->delete();
        foreach ($this->ttmSpeakers as $slot => $s) {
            if (! empty($s['user_id'])) {
                MeetingTtmSpeaker::create([
                    'meeting_id' => $this->meeting->id,
                    'user_id'    => $s['user_id'],
                    'slot'       => $slot + 1,
                    'topic'      => $s['topic'] ?? null,
                    'duration'   => $s['duration'] ?? null,
                ]);
            }
        }

        // Sync evaluations
        $this->meeting->evaluations()->delete();
        $savedSpeakers = $this->meeting->speakers()->get();
        foreach ($this->evaluations as $e) {
            if (! empty($e['evaluator_user_id'])) {
                $sp = $savedSpeakers->get((int) ($e['speaker_index'] ?? 0));
                if ($sp) {
                    MeetingEvaluation::create([
                        'meeting_id'        => $this->meeting->id,
                        'speaker_id'        => $sp->id,
                        'evaluator_user_id' => $e['evaluator_user_id'],
                    ]);
                }
            }
        }

        $this->dispatch('flash', message: 'Meeting updated successfully.', type: 'success');
        $this->redirect(route('meetings.show', $this->meeting), navigate: true);
    }

    public function render()
    {
        $club      = $this->meeting->club;
        $members   = User::inClub($this->meeting->club_id)->active()->orderBy('name')->get();
        $roleTypes = MeetingRoleType::active()->get();

        return view('livewire.meetings.meeting-edit', compact('club', 'members', 'roleTypes'));
    }
}
