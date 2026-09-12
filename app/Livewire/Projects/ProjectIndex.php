<?php

namespace App\Livewire\Projects;

use App\Livewire\Concerns\WithClubContext;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Speech Projects Catalog')]
class ProjectIndex extends Component
{
    use WithClubContext, WithPagination;

    public string $search = '';
    public string $selectedTrack = '';
    public string $selectedLevel = '';
    public ?int $viewingProjectId = null;

    protected $queryString = [
        'search'        => ['except' => ''],
        'selectedTrack' => ['except' => ''],
        'selectedLevel' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedTrack(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedLevel(): void
    {
        $this->resetPage();
    }

    public function openProject(int $id): void
    {
        $this->viewingProjectId = $id;
    }

    public function closeProject(): void
    {
        $this->viewingProjectId = null;
    }

    public function render()
    {
        $allProjects = Project::active()
            ->select(['id', 'name', 'slug', 'track', 'level', 'min_minutes', 'max_minutes', 'overview', 'objectives', 'evaluator_notes'])
            ->orderBy('sort_order')
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id'              => $p->id,
                'name'            => $p->name,
                'slug'            => $p->slug,
                'track'           => $p->track,
                'level'           => $p->level,
                'level_badge'     => $p->levelBadge(),
                'min_minutes'     => $p->min_minutes,
                'max_minutes'     => $p->max_minutes,
                'duration'        => $p->formattedTiming(),
                'overview'        => $p->overview,
                'objectives'      => $p->objectives,
                'evaluator_notes' => $p->evaluator_notes,
            ]);

        $tracks = $allProjects->pluck('track')->filter()->unique()->sort()->values();
        $levels = $allProjects->pluck('level')->filter()->unique()->sort()->values();

        $viewingProject = $this->viewingProjectId ? Project::find($this->viewingProjectId) : null;

        return view('livewire.projects.project-index', compact('allProjects', 'tracks', 'levels', 'viewingProject'));
    }
}
