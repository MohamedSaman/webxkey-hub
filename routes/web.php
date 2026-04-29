<?php

use App\Http\Controllers\ServerAuthController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\ApplicationsList;
use App\Livewire\Admin\DeployWizard;
use App\Livewire\Admin\SiteDetail;
use Illuminate\Support\Facades\Route;

// ── Server password gate ──────────────────────────────────────────────
Route::get('/login', [ServerAuthController::class, 'show'])->name('login');
Route::post('/login', [ServerAuthController::class, 'store'])->name('server-auth.store');
Route::post('/logout', [ServerAuthController::class, 'logout'])->name('logout');

// ── Protected pages (server password required) ────────────────────────
Route::middleware('server-auth')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/applications', ApplicationsList::class)->name('applications');
    Route::get('/deploy', DeployWizard::class)->name('deploy');
    Route::get('/applications/{application}', SiteDetail::class)->name('applications.show');
});
