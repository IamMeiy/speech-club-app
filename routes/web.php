<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Logout;
use App\Livewire\Clubs\ClubCreate;
use App\Livewire\Clubs\ClubEdit;
use App\Livewire\Clubs\ClubIndex;
use App\Livewire\Clubs\ClubRoleManager;
use App\Livewire\Dashboard;
use App\Livewire\GlobalUsers\GlobalUserCreate;
use App\Livewire\GlobalUsers\GlobalUserEdit;
use App\Livewire\GlobalUsers\GlobalUserIndex;
use App\Livewire\Meetings\MeetingAttendance;
use App\Livewire\Meetings\MeetingCreate;
use App\Livewire\Meetings\MeetingEdit;
use App\Livewire\Meetings\MeetingIndex;
use App\Livewire\Meetings\MeetingReport;
use App\Livewire\Meetings\MeetingShow;
use App\Livewire\Permissions\PermissionIndex;
use App\Livewire\Profile\ProfileEdit;
use App\Livewire\Roles\RoleCreate;
use App\Livewire\Roles\RoleEdit;
use App\Livewire\Roles\RoleIndex;
use App\Livewire\Users\UserCreate;
use App\Livewire\Users\UserEdit;
use App\Livewire\Users\UserIndex;
use App\Livewire\Users\UserShow;
use Illuminate\Support\Facades\Route;

// -----------------------------------------------------------------------
// Guest routes (unauthenticated)
// -----------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// -----------------------------------------------------------------------
// Auth routes
// -----------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', Logout::class)->name('logout');

    // Dashboard
    Route::get('/', Dashboard::class)->name('dashboard');

    // Club Switcher (global users only) — AJAX/POST
    Route::post('/switch-club', function () {
        $clubId = request()->input('club_id');
        app(\App\Services\ClubContextService::class)->setCurrentClub($clubId ? (int) $clubId : null);
        return redirect()->back();
    })->name('switch-club');

    // -----------------------------------------------------------------------
    // Members (club-scoped users)
    // -----------------------------------------------------------------------
    Route::prefix('members')->name('members.')->group(function () {
        Route::get('/', UserIndex::class)->name('index');
        Route::get('/create', UserCreate::class)->name('create');
        Route::get('/{user}', UserShow::class)->name('show');
        Route::get('/{user}/edit', UserEdit::class)->name('edit');
    });

    // -----------------------------------------------------------------------
    // Global Users (global admin users)
    // -----------------------------------------------------------------------
    Route::prefix('global-users')->name('global-users.')->group(function () {
        Route::get('/', GlobalUserIndex::class)->name('index');
        Route::get('/create', GlobalUserCreate::class)->name('create');
        Route::get('/{user}/edit', GlobalUserEdit::class)->name('edit');
    });

    // -----------------------------------------------------------------------
    // Clubs
    // -----------------------------------------------------------------------
    Route::prefix('clubs')->name('clubs.')->group(function () {
        Route::get('/', ClubIndex::class)->name('index');
        Route::get('/create', ClubCreate::class)->name('create');
        Route::get('/{club}/edit', ClubEdit::class)->name('edit');
        Route::get('/{club}/roles', ClubRoleManager::class)->name('roles');
    });

    // -----------------------------------------------------------------------
    // Meetings
    // -----------------------------------------------------------------------
    Route::prefix('meetings')->name('meetings.')->group(function () {
        Route::get('/', MeetingIndex::class)->name('index');
        Route::get('/create', MeetingCreate::class)->name('create');
        Route::get('/{meeting}', MeetingShow::class)->name('show');
        Route::get('/{meeting}/edit', MeetingEdit::class)->name('edit');
        Route::get('/{meeting}/attendance', MeetingAttendance::class)->name('attendance');
        Route::get('/{meeting}/report', MeetingReport::class)->name('report');
    });

    // -----------------------------------------------------------------------
    // Speech Projects
    // -----------------------------------------------------------------------
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', \App\Livewire\Projects\ProjectIndex::class)->name('index');
    });

    // -----------------------------------------------------------------------
    // Roles & Permissions
    // -----------------------------------------------------------------------
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', RoleIndex::class)->name('index');
        Route::get('/create', RoleCreate::class)->name('create');
        Route::get('/{role}/edit', RoleEdit::class)->name('edit');
    });

    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', PermissionIndex::class)->name('index');
    });

    // My Progress & Feedback
    Route::get('/my-progress', \App\Livewire\Progress\ProgressIndex::class)->name('progress.index');

    // Profile
    Route::get('/profile', ProfileEdit::class)->name('profile');
});
