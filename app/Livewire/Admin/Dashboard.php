<?php

namespace App\Livewire\Admin;

use App\Models\Application;
use App\Services\ServerCommandService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app', ['title' => 'Dashboard'])]
class Dashboard extends Component
{
    public array $stats = [];
    public array $quickOutputs = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    private function loadStats(): void
    {
        $apps = Application::all();

        $this->stats = [
            'total'    => $apps->count(),
            'live'     => $apps->where('status', 'live')->count(),
            'warnings' => 0,
            'down'     => $apps->whereIn('status', ['error', 'stopped'])->count(),
        ];
    }

    public function quickPull(int $appId): void
    {
        $app = Application::findOrFail($appId);

        $log = $app->deploymentLogs()->create([
            'step'      => 0,
            'step_name' => 'git pull',
            'command'   => "cd /var/www/{$app->folder_path} && git pull origin {$app->branch}",
            'status'    => 'pending',
        ]);

        $service = new ServerCommandService();
        $service->gitPull($app->folder_path, $app->branch, $log);
        $app->update(['last_pull_at' => now()]);
        $this->quickOutputs[$appId] = $log->fresh()->output ?? '✓ Done';
    }

    public function quickCacheClean(int $appId): void
    {
        $app = Application::findOrFail($appId);
        $service = new ServerCommandService();
        $this->quickOutputs[$appId] = $service->optimizeClear($app->folder_path);
    }

    public function render()
    {
        $applications = Application::with([
            'healthChecks' => fn($q) => $q->latest('checked_at')->limit(1),
        ])->orderBy('name')->get();

        return view('livewire.admin.dashboard', [
            'applications' => $applications,
        ]);
    }
}
