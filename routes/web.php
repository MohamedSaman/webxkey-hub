<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\ApplicationsList;
use App\Livewire\Admin\DeployWizard;
use App\Livewire\Admin\SiteDetail;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/applications', ApplicationsList::class)->name('applications');
    Route::get('/deploy', DeployWizard::class)->name('deploy');
    Route::get('/applications/{application}', SiteDetail::class)->name('applications.show');
});
