<?php

namespace App\Livewire\Meetings;

use App\Livewire\Concerns\WithClubContext;
use App\Models\Meeting;
use App\Services\ClubAccessService;
use App\Services\ClubContextService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Lazy]
#[Layout('components.layouts.app')]
#[Title('Meetings')]
class MeetingIndex extends Component
{
    use WithPagination, WithClubContext;

    public function placeholder()
    {
        $club = $this->getCurrentClub();
        return view('livewire.meetings.meeting-index-skeleton', compact('club'));
    }

    public string $search  = '';
    public string $status  = '';
    public string $filter  = 'all'; // all, upcoming, past
    public int    $perPage = 15;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingFilter(): void { $this->resetPage(); }

    public function deleteMeeting(int $meetingId, ClubAccessService $access): void
    {
        $this->authorize('meetings.delete');
        $user    = auth()->user();
        $meeting = Meeting::findOrFail($meetingId);

        // Security check
        if (! $user->isSuperAdmin() && ! $access->validateUserBelongsToClub($user->id, $meeting->club_id)) {
            abort(403);
        }

        $meeting->delete();
        $this->dispatch('flash', message: 'Meeting deleted.', type: 'success');
    }

    public function render(ClubContextService $clubContext)
    {
        $user = auth()->user();
        $club = $clubContext->currentClub();

        $query = Meeting::select(['id', 'club_id', 'meeting_number', 'meeting_date', 'theme', 'venue', 'status'])
            ->with('club:id,name')
            ->when($club, fn ($q) => $q->forClub($club->id))
            ->when(! $club && ! $user->isSuperAdmin(), function ($q) use ($user) {
                $q->whereIn('club_id', $user->clubs()->pluck('clubs.id'));
            })
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('theme', 'like', "%{$this->search}%")
                  ->orWhere('meeting_number', 'like', "%{$this->search}%");
            }))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->filter === 'upcoming', fn ($q) => $q->upcoming())
            ->when($this->filter === 'past', fn ($q) => $q->past())
            ->orderByDesc('meeting_date');

        $meetings = $query->paginate($this->perPage);

        return view('livewire.meetings.meeting-index', compact('meetings', 'club'));
    }
}
