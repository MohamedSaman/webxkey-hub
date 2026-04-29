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
    public ?int $confirmDeleteId = null;
    public string $deleteFolder = '';

    // Import existing
    public bool $showImport = false;
    public array $unregistered = [];
    public string $importFolder = '';
    public string $importName = '';
    public string $importDomain = '';
    public string $importGitRepo = '';
    public string $importBranch = 'main';
    public string $importDbName = '';

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
        $this->confirmDeleteId = $appId;
        $this->deleteFolder = Application::find($appId)?->folder_path ?? '';
    }

    public function deleteApp(): void
    {
        if ($this->confirmDeleteId) {
            $app = Application::findOrFail($this->confirmDeleteId);
            (new ServerCommandService())->deleteFolder($app->folder_path);
            $app->delete();
            $this->confirmDeleteId = null;
            $this->deleteFolder = '';
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
        $this->deleteFolder = '';
    }

    public function openImport(): void
    {
        $cmd = new ServerCommandService();
        $registered = Application::pluck('folder_path')->toArray();
        $this->unregistered = $cmd->getUnregisteredFolders($registered);
        $this->importFolder = '';
        $this->importName = '';
        $this->importDomain = '';
        $this->importGitRepo = '';
        $this->importBranch = 'main';
        $this->importDbName = '';
        $this->showImport = true;
    }

    public function selectImportFolder(string $folder): void
    {
        $this->importFolder = $folder;
        $cmd = new ServerCommandService();
        $env = $cmd->readEnvFile($folder);
        $this->importName   = $env['APP_NAME'] ?? ucwords(str_replace(['-', '_'], ' ', $folder));
        $this->importDomain = $env['APP_URL'] ? ltrim(parse_url($env['APP_URL'], PHP_URL_HOST) ?? '', 'https://') : $folder . '.webxkey.store';
        $this->importDbName = $env['DB_DATABASE'] ?? '';
        // try to get git remote
        $remote = (new ServerCommandService())->runQuickPublic("/var/www/{$folder}", 'git remote get-url origin 2>/dev/null');
        $this->importGitRepo = trim($remote);
    }

    public function importApp(): void
    {
        $this->validate([
            'importFolder' => 'required|string',
            'importName'   => 'required|string',
            'importDomain' => 'required|string',
        ]);

        Application::create([
            'name'             => $this->importName,
            'domain'           => $this->importDomain,
            'folder_path'      => $this->importFolder,
            'git_repo'         => $this->importGitRepo ?: null,
            'branch'           => $this->importBranch,
            'db_name'          => $this->importDbName ?: null,
            'status'           => 'live',
            'php_version'      => '8.3',
            'last_deployed_at' => now(),
        ]);

        $this->showImport = false;
    }

    public function closeImport(): void
    {
        $this->showImport = false;
    }

    public function scanAndSyncAll(): void
    {
        $cmd = new ServerCommandService();
        $existing = Application::all()->keyBy('folder_path');

        // 1. Update existing records that have NULL data
        foreach ($existing as $folder => $app) {
            if ($app->git_repo && $app->db_name) continue; // already has data
            $env = $cmd->readEnvFile($folder);
            if (empty($env)) continue;

            $domain = $env['APP_URL'] ? (parse_url($env['APP_URL'], PHP_URL_HOST) ?? $app->domain) : $app->domain;
            $gitRepo = trim($cmd->runQuickPublic("/var/www/{$folder}", 'git remote get-url origin 2>/dev/null'));

            $app->update(array_filter([
                'name'        => $env['APP_NAME'] ?? $app->name,
                'domain'      => $domain ?: $app->domain,
                'db_name'     => $env['DB_DATABASE'] ?? $app->db_name,
                'git_repo'    => $gitRepo ?: $app->git_repo,
                'status'      => $app->status === 'deploying' ? 'live' : $app->status,
            ], fn($v) => $v !== null && $v !== ''));
        }

        // 2. Register any unregistered folders
        $registeredFolders = Application::pluck('folder_path')->toArray();
        $unregistered = $cmd->getUnregisteredFolders($registeredFolders);

        foreach ($unregistered as $folder) {
            $env = $cmd->readEnvFile($folder);
            $domain = isset($env['APP_URL']) ? (parse_url($env['APP_URL'], PHP_URL_HOST) ?? $folder . '.webxkey.store') : $folder . '.webxkey.store';
            $gitRepo = trim($cmd->runQuickPublic("/var/www/{$folder}", 'git remote get-url origin 2>/dev/null'));

            Application::create([
                'name'             => $env['APP_NAME'] ?? ucwords(str_replace(['-', '_'], ' ', $folder)),
                'domain'           => $domain,
                'folder_path'      => $folder,
                'git_repo'         => $gitRepo ?: null,
                'branch'           => 'main',
                'db_name'          => $env['DB_DATABASE'] ?? null,
                'status'           => 'live',
                'php_version'      => '8.3',
                'last_deployed_at' => now(),
            ]);
        }

        $this->showImport = false;
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
