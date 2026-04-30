<?php

use App\Http\Controllers\ServerAuthController;
use App\Livewire\Admin\BillingManager;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\ApplicationsList;
use App\Livewire\Admin\DeployWizard;
use App\Livewire\Admin\SiteDetail;
use App\Livewire\Clients\ClientManager;
use App\Livewire\Invoices\InvoiceBuilder;
use App\Livewire\Invoices\InvoicePreview;
use App\Livewire\Projects\ProjectPipeline;
use App\Livewire\Proposals\ProposalBuilder;
use App\Livewire\Proposals\ProposalPreview;
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
    Route::get('/billing', BillingManager::class)->name('billing');
    Route::get('/billing/settings', \App\Livewire\Admin\BillingSettings::class)->name('billing.settings');

    // Clients
    Route::get('/clients', ClientManager::class)->name('clients');

    // Projects
    Route::get('/projects', ProjectPipeline::class)->name('projects');

    // Proposals
    Route::get('/proposals/{proposal}', ProposalBuilder::class)->name('proposals.show');
    Route::get('/proposals/{proposal}/preview', ProposalPreview::class)->name('proposals.preview');

    // Invoices
    Route::get('/invoices/{invoice}', InvoiceBuilder::class)->name('invoices.show');
    Route::get('/invoices/{invoice}/preview', InvoicePreview::class)->name('invoices.preview');
});
