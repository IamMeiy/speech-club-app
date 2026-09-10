<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Edit Club')]
class ClubEdit extends Component
{
    public Club $club;

    public string $name        = '';
    public string $code        = '';
    public string $description = '';
    public string $status      = 'active';
    public string $meeting_day  = '';
    public string $meeting_time = '';
    public string $location    = '';

    public function mount(Club $club): void
    {
        $this->club        = $club;
        $this->name        = $club->name;
        $this->code        = $club->code;
        $this->description = $club->description ?? '';
        $this->status      = $club->status;
        $this->meeting_day  = $club->meeting_day ?? '';
        $this->meeting_time = $club->meeting_time ?? '';
        $this->location    = $club->location ?? '';
    }

    public function save(): void
    {
        $this->authorize('clubs.update');

        $this->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:clubs,code,' . $this->club->id,
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
            'meeting_day'  => 'nullable|string|max:100',
            'meeting_time' => 'nullable|string',
            'location'    => 'nullable|string|max:255',
        ]);

        $this->club->update([
            'name'         => $this->name,
            'code'         => $this->code,
            'description'  => $this->description ?: null,
            'status'       => $this->status,
            'meeting_day'  => $this->meeting_day ?: null,
            'meeting_time' => $this->meeting_time ?: null,
            'location'     => $this->location ?: null,
        ]);

        $this->dispatch('flash', message: 'Club updated successfully.', type: 'success');
        $this->redirect(route('clubs.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.clubs.club-edit');
    }
}
