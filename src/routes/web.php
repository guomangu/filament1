<?php

use App\Livewire\Circle\Profile as CircleProfile;
use App\Livewire\Circle\Create as CircleCreate;
use App\Livewire\Circle\Edit as CircleEdit;
use App\Livewire\Achievement\Create as AchievementCreate;
use App\Livewire\User\Profile as UserProfile;
use App\Livewire\User\Edit as UserEdit;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');

Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/circles/create', CircleCreate::class)->name('circles.create');
    Route::get('/circles/{circle}/edit', CircleEdit::class)->name('circles.edit');
    Route::get('/achievements/create', AchievementCreate::class)->name('achievements.create');
    Route::get('/profile/edit', UserEdit::class)->name('profile.edit');
});

// Public Show pages
Route::get('/mission/{skill}', \App\Livewire\Mission\Show::class)->name('mission.show');
Route::get('/réalisation/{project}', \App\Livewire\Project\Show::class)->name('projects.show');
Route::get('/realisation-preuve/{achievement}', \App\Livewire\Achievement\Show::class)->name('achievements.show');
Route::get('/realisation/{id}', function ($id) {
    $project = \App\Models\Project::find($id);
    if ($project) return redirect()->route('projects.show', $project);
    
    $achievement = \App\Models\Achievement::find($id);
    if ($achievement) return redirect()->route('achievements.show', $achievement);
    
    abort(404);
});

Route::get('/proches/claim/{token}', \App\Livewire\User\Claim::class)->name('proches.claim');

Route::get('/circles/{circle}', CircleProfile::class)->name('circles.show');
Route::get('/users/{user}', UserProfile::class)->name('users.show');

Route::get('/cv/u/{user}', \App\Livewire\Cv\Viewer::class)->name('cv.user');
Route::get('/cv/m/{skill}', \App\Livewire\Cv\Viewer::class)->name('cv.mission');
Route::get('/cv/p/{project}', \App\Livewire\Cv\Viewer::class)->name('cv.project');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');


Route::resource('circles', App\Http\Controllers\CircleController::class)->except(['create', 'show', 'edit']);

Route::resource('achievements', App\Http\Controllers\AchievementController::class)->except(['create', 'show', 'edit']);
