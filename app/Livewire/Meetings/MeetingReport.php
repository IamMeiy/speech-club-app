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
            'club:id,name,code',
            'roles.roleType:id,name,sort_order',
            'roles.user:id,name,email',
            'speakers.user:id,name,email',
            'speakers.projectModel:id,name,track,level,min_minutes,max_minutes',
            'speakers.evaluation.evaluator:id,name,email',
            'ttmSpeakers.user:id,name,email',
            'evaluations.speaker.user:id,name,email',
            'evaluations.evaluator:id,name,email',
            'attendance.user:id,name,email',
            'timerLogs.user:id,name',
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
            'club:id,name,code',
            'roles.roleType:id,name,sort_order',
            'roles.user:id,name,email',
            'speakers.user:id,name,email',
            'speakers.projectModel:id,name,track,level,min_minutes,max_minutes',
            'speakers.evaluation.evaluator:id,name,email',
            'ttmSpeakers.user:id,name,email',
            'evaluations.speaker.user:id,name,email',
            'evaluations.evaluator:id,name,email',
            'attendance.user:id,name,email',
            'timerLogs.user:id,name',
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
