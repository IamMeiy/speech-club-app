<?php

namespace App\Livewire\Meetings;

use App\Livewire\Concerns\WithClubContext;
use App\Models\Meeting;
use App\Services\ClubContextService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Meeting Report')]
class MeetingReport extends Component
{
    use WithClubContext;

    public Meeting $meeting;

    public function mount(Meeting $meeting, ClubContextService $clubContext): void
    {
        $club = $clubContext->currentClub();
        if ($club && $meeting->club_id !== $club->id && ! auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $this->meeting = $meeting;
    }

    public function render()
    {
        $meeting = $this->meeting->load([
            'club',
            'roles.roleType',
            'roles.user',
            'speakers.user',
            'speakers.evaluation.evaluator',
            'ttmSpeakers.user',
            'evaluations.speaker.user',
            'evaluations.evaluator',
            'attendance.user',
        ]);

        // Attendance stats
        $stats = [
            'present' => $meeting->attendance->where('status', 'present')->count(),
            'absent'  => $meeting->attendance->where('status', 'absent')->count(),
            'late'    => $meeting->attendance->where('status', 'late')->count(),
            'excused' => $meeting->attendance->where('status', 'excused')->count(),
            'total'   => $meeting->attendance->count(),
        ];

        return view('livewire.meetings.meeting-report', compact('meeting', 'stats'))
            ->title('Report: Meeting #' . $meeting->meeting_number);
    }
}
