<?php

namespace App\Livewire\Meetings;

use App\Livewire\Concerns\WithClubContext;
use App\Models\Meeting;
use App\Services\ClubAccessService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Meeting Report')]
class MeetingReport extends Component
{
    use WithClubContext;

    public Meeting $meeting;

    public function mount(Meeting $meeting, ClubAccessService $access): void
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && ! $access->validateUserBelongsToClub($user->id, $meeting->club_id)) {
            abort(403, 'This report does not belong to your club.');
        }

        $this->meeting = $meeting;
    }

    public function downloadPdf(string $theme = 'indigo')
    {
        $validThemes = ['indigo', 'emerald', 'blue', 'purple', 'rose', 'amber', 'cyan'];
        $theme = in_array($theme, $validThemes, true) ? $theme : 'indigo';

        $meeting = $this->meeting->load([
            'club',
            'roles.roleType',
            'roles.user',
            'speakers.user',
            'speakers.projectModel',
            'speakers.evaluation.evaluator',
            'ttmSpeakers.user',
            'evaluations.speaker.user',
            'evaluations.evaluator',
            'attendance.user',
            'timerLogs.user',
        ]);

        $stats = [
            'present' => $meeting->attendance->where('status', 'present')->count(),
            'absent'  => $meeting->attendance->where('status', 'absent')->count(),
            'late'    => $meeting->attendance->where('status', 'late')->count(),
            'excused' => $meeting->attendance->where('status', 'excused')->count(),
            'total'   => $meeting->attendance->count(),
        ];

        $pdf = Pdf::loadView('pdf.meeting-report', compact('meeting', 'stats', 'theme'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'defaultFont' => 'sans-serif']);

        $fileName = sprintf(
            'Meeting-%d-Report-%s.pdf',
            $meeting->meeting_number,
            $meeting->meeting_date->format('Y-m-d')
        );

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function render()
    {
        $meeting = $this->meeting->load([
            'club',
            'roles.roleType',
            'roles.user',
            'speakers.user',
            'speakers.projectModel',
            'speakers.evaluation.evaluator',
            'ttmSpeakers.user',
            'evaluations.speaker.user',
            'evaluations.evaluator',
            'attendance.user',
            'timerLogs.user',
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
