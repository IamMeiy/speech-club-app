<?php

namespace App\Http\ViewComposers;

use App\Services\ClubContextService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppLayoutComposer
{
    public function __construct(
        private readonly ClubContextService $clubContext
    ) {}

    public function compose(View $view): void
    {
        if (! Auth::check()) {
            $view->with('currentClub', null);
            $view->with('availableClubs', collect());
            return;
        }

        $view->with('currentClub', $this->clubContext->currentClub());
        $view->with('availableClubs', $this->clubContext->availableClubs());
    }
}
