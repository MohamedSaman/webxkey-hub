<?php

namespace App\Livewire\Admin;

use App\Models\Application;
use App\Services\ServerCommandService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app', ['title' => 'Client Systems'])]
class ApplicationsList extends Component
{
    public string $search = '';
    public ?int $confirmDelete = null;
    public string $deleteFolder = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function resetPage(): void {}

    public function quickPull(int $appId): void
    {
        $app = Application::findOrFail($appId);
        $log = $app->deploymentLogs()->create([
            'step'      => 0,
            'step_name' => 'git pull',
            'command'   => "git pull origin {$app->branch}",
            'status'    => 'pending',
        ]);
        (new ServerCommandService())->gitPull($app->folder_path, $app->branch, $log);
        $app->update(['last_pull_at' => now()]);
    }

    public function confirmDelete(int $appId): void
    {
        $this->confirmDelete = $appId;
        $this->deleteFolder = Application::find($appId)?->folder_path ?? '';
    }

    public function deleteApp(): void
    {
        if ($this->confirmDelete) {
            $app = Application::findOrFail($this->confirmDelete);
            (new ServerCommandService())->deleteFolder($app->folder_path);
            $app->delete();
            $this->confirmDelete = null;
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmDelete = null;
        $this->deleteFolder = '';
    }

    public function render()
    {
        $applications = Application::with([
            'healthChecks' => fn($q) => $q->latest('checked_at')->limit(1),
        ])
        ->when($this->search, fn($q) =>
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('domain', 'like', "%{$this->search}%")
        )
        ->orderBy('name')
        ->get();

        return view('livewire.admin.applications-list', [
            'applications' => $applications,
        ]);
    }
}
