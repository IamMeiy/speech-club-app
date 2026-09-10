<?php

namespace App\Livewire\Meetings;

use App\Livewire\Concerns\WithClubContext;
use App\Models\Meeting;
use App\Services\ClubAccessService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Meeting Details')]
class MeetingShow extends Component
{
    use WithClubContext;

    public Meeting $meeting;

    public function mount(Meeting $meeting, ClubAccessService $access): void
    {
        $user = auth()->user();
        // Security check
        if (! $user->isSuperAdmin() && ! $access->validateUserBelongsToClub($user->id, $meeting->club_id)) {
            abort(403, 'This meeting does not belong to your club.');
        }

        $this->meeting = $meeting;
    }

    public function render()
    {
        $meeting = $this->meeting->load([
            'club',
            'creator',
            'roles.roleType',
            'roles.user',
            'speakers.user',
            'speakers.evaluation.evaluator',
            'ttmSpeakers.user',
            'evaluations.speaker.user',
            'evaluations.evaluator',
            'attendance.user',
        ]);

        return view('livewire.meetings.meeting-show', compact('meeting'))
            ->title('Meeting #' . $meeting->meeting_number . ' — ' . $meeting->club->name);
    }
}
