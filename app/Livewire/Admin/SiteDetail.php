<?php

namespace App\Livewire\Admin;

use App\Models\Application;
use App\Models\DeploymentLog;
use App\Models\Payment;
use App\Services\ServerCommandService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class SiteDetail extends Component
{
    public Application $application;
    public string $actionOutput = '';
    public bool $isRunning = false;
    public bool $inMaintenance = false;
    public bool $debugMode = false;
    public array $serverStats = [];
    public ?Payment $currentMonthPayment = null;

    // Log viewer
    public string $logOutput = '';
    public int $logLines = 100;
    public bool $logLoaded = false;

    public function mount(Application $application): void
    {
        $this->application = $application;
        $this->inMaintenance = false;
        $this->serverStats = [];
        $raw = $this->cmd()->getEnvValue($application->folder_path, 'APP_DEBUG');
        $this->debugMode = strtolower($raw) === 'true';
        $this->currentMonthPayment = Payment::where('application_id', $this->application->id)
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->first();
    }

    private function cmd(): ServerCommandService
    {
        return new ServerCommandService();
    }

    private function runAction(string $label, callable $action): void
    {
        $this->isRunning = true;
        $this->actionOutput = "$ Running: {$label}...\n";
        try {
            $output = $action();
            $this->actionOutput .= $output ?? '✓ Done.';
        } catch (\Throwable $e) {
            $this->actionOutput .= "[error] " . $e->getMessage();
        }
        $this->isRunning = false;
    }

    private function runStreamAction(string $stepName, string $command, callable $action): void
    {
        $this->isRunning = true;
        $this->actionOutput = "$ {$command}\n";

        $log = $this->application->deploymentLogs()->create([
            'step'      => 0,
            'step_name' => $stepName,
            'command'   => $command,
            'status'    => 'pending',
        ]);

        $action($log);
        $this->actionOutput = $log->fresh()->output ?? '✓ Done.';
        $this->isRunning = false;
        $this->application->refresh();
    }

    public function gitPull(): void
    {
        $app = $this->application;
        $this->runStreamAction(
            'git pull',
            "git pull origin {$app->branch}",
            fn($log) => $this->cmd()->gitPull($app->folder_path, $app->branch, $log)
        );
        $this->application->update(['last_pull_at' => now()]);
    }

    public function clearCache(): void
    {
        $this->runAction('optimize:clear', fn() =>
            $this->cmd()->optimizeClear($this->application->folder_path)
        );
    }

    public function restartQueue(): void
    {
        $this->runAction('queue:restart', fn() =>
            $this->cmd()->queueRestart($this->application->folder_path)
        );
    }

    public function runMigrate(): void
    {
        $app = $this->application;
        $this->runStreamAction(
            'migrate',
            'php artisan migrate --force',
            fn($log) => $this->cmd()->runMigrate($app->folder_path, $log)
        );
    }

    public function toggleDebug(): void
    {
        $this->debugMode = !$this->debugMode;
        $this->cmd()->setEnvValue(
            $this->application->folder_path,
            'APP_DEBUG',
            $this->debugMode ? 'true' : 'false'
        );
        $this->cmd()->optimizeClear($this->application->folder_path);
        $this->actionOutput = '✓ APP_DEBUG set to ' . ($this->debugMode ? 'true' : 'false') . ' — cache cleared.';
    }

    public function toggleMaintenance(): void
    {
        if ($this->inMaintenance) {
            $this->runAction('artisan up', fn() =>
                $this->cmd()->maintenanceUp($this->application->folder_path)
            );
            $this->inMaintenance = false;
        } else {
            $this->runAction('artisan down', fn() =>
                $this->cmd()->maintenanceDown($this->application->folder_path)
            );
            $this->inMaintenance = true;
        }
    }

    public function checkGitStatus(): void
    {
        $this->runAction('git status', fn() =>
            $this->cmd()->gitStatus($this->application->folder_path)
        );
    }

    public function stopSite(): void
    {
        $this->cmd()->disableNginxSite($this->application->domain);
        $this->cmd()->reloadNginx();
        $this->application->update(['status' => 'stopped']);
        $this->application->refresh();
        $this->actionOutput = "✓ Site stopped — Nginx config disabled.";
    }

    public function startSite(): void
    {
        $this->cmd()->enableNginxSite($this->application->domain);
        $this->cmd()->reloadNginx();
        $this->application->update(['status' => 'live']);
        $this->application->refresh();
        $this->actionOutput = "✓ Site started — Nginx config enabled.";
    }

    public function refreshStats(): void
    {
        $this->serverStats = $this->cmd()->getServerStats();
    }

    public function loadLog(): void
    {
        $this->logOutput = $this->cmd()->readLog($this->application->folder_path, $this->logLines);
        $this->logLoaded = true;
    }

    public function clearLog(): void
    {
        $this->cmd()->clearLog($this->application->folder_path);
        $this->logOutput = '(log cleared)';
    }

    public function updatedLogLines(): void
    {
        if ($this->logLoaded) {
            $this->loadLog();
        }
    }

    public function render()
    {
        $healthHistory = $this->application->healthChecks()
            ->latest('checked_at')
            ->limit(30)
            ->get()
            ->reverse()
            ->values();

        $latestHealth = $healthHistory->last();

        return view('livewire.admin.site-detail', [
            'healthHistory' => $healthHistory,
            'latestHealth'  => $latestHealth,
        ])->title($this->application->name . ' — Site Detail');
    }
}
