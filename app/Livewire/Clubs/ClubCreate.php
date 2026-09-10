<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Create Club')]
class ClubCreate extends Component
{
    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|string|max:50|unique:clubs,code')]
    public string $code = '';

    #[Rule('nullable|string')]
    public string $description = '';

    #[Rule('required|in:active,inactive')]
    public string $status = 'active';

    #[Rule('nullable|string|max:100')]
    public string $meeting_day = '';

    #[Rule('nullable|string')]
    public string $meeting_time = '';

    #[Rule('nullable|string|max:255')]
    public string $location = '';

    public function updatedName(): void
    {
        // Auto-generate code from name
        if ($this->code === '') {
            $this->code = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $this->name));
            $this->code = trim(preg_replace('/-+/', '-', $this->code), '-');
        }
    }

    public function save(): void
    {
        $this->authorize('clubs.create');
        $this->validate();

        Club::create([
            'name'         => $this->name,
            'code'         => $this->code,
            'description'  => $this->description ?: null,
            'status'       => $this->status,
            'meeting_day'  => $this->meeting_day ?: null,
            'meeting_time' => $this->meeting_time ?: null,
            'location'     => $this->location ?: null,
            'timezone'     => 'Asia/Kolkata',
        ]);

        $this->dispatch('flash', message: 'Club created successfully.', type: 'success');
        $this->redirect(route('clubs.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.clubs.club-create');
    }
}
