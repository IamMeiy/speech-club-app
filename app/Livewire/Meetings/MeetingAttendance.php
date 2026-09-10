<?php

namespace App\Livewire\Meetings;

use App\Livewire\Concerns\WithClubContext;
use App\Models\Meeting;
use App\Models\MeetingAttendance as MeetingAttendanceModel;
use App\Models\MeetingRole;
use App\Models\User;
use App\Services\ClubAccessService;
use App\Services\ClubContextService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Meeting Attendance')]
class MeetingAttendance extends Component
{
    use WithClubContext;

    public Meeting $meeting;

    // Attendance: keyed by user_id => status
    public array $attendance = [];

    // Role replacement: roleId => replacementUserId
    public array $roleReplacements = [];

    public function mount(Meeting $meeting, ClubContextService $clubContext): void
    {
        $club = $clubContext->currentClub();
        if ($club && $meeting->club_id !== $club->id) {
            abort(403);
        }

        $this->meeting = $meeting;

        // Initialize attendance for all club members
        $members = User::inClub($meeting->club_id)->active()->orderBy('name')->get();

        foreach ($members as $member) {
            $existing = $meeting->attendance->firstWhere('user_id', $member->id);
            $this->attendance[$member->id] = $existing?->status ?? 'present';
        }
    }

    public function saveAttendance(ClubAccessService $access): void
    {
        $this->authorize('attendance.manage');

        // Validate all users belong to the meeting's club
        $userIds = array_keys($this->attendance);
        if (! $access->validateAllUsersBelongToClub($userIds, $this->meeting->club_id)) {
            $this->addError('attendance', 'One or more users do not belong to this club.');
            return;
        }

        foreach ($this->attendance as $userId => $status) {
            MeetingAttendanceModel::updateOrCreate(
                ['meeting_id' => $this->meeting->id, 'user_id' => $userId],
                ['status' => $status]
            );
        }

        $this->dispatch('flash', message: 'Attendance saved successfully.', type: 'success');
    }

    public function replaceRole(int $meetingRoleId, ClubAccessService $access): void
    {
        $this->authorize('meeting-roles.manage');

        $replacementUserId = $this->roleReplacements[$meetingRoleId] ?? null;

        if (! $replacementUserId) {
            return;
        }

        // Validate replacement user belongs to the meeting's club
        if (! $access->validateUserBelongsToClub($replacementUserId, $this->meeting->club_id)) {
            $this->addError('roleReplacements.' . $meetingRoleId, 'Selected member does not belong to this club.');
            return;
        }

        MeetingRole::where('id', $meetingRoleId)->update(['user_id' => $replacementUserId]);

        // Reload meeting
        $this->meeting->refresh();

        $this->dispatch('flash', message: 'Role assignment updated.', type: 'success');
    }

    public function render()
    {
        $members = User::inClub($this->meeting->club_id)->active()->orderBy('name')->get();
        $meeting = $this->meeting->load(['roles.roleType', 'roles.user', 'attendance.user']);

        return view('livewire.meetings.meeting-attendance', compact('meeting', 'members'));
    }
}
