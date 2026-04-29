<?php

namespace App\Livewire\Admin;

use App\Models\Application;
use App\Models\DeploymentLog;
use App\Services\ServerCommandService;
use App\Services\NginxConfigService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app', ['title' => 'Deploy New App'])]
class DeployWizard extends Component
{
    // Wizard state
    public int $step = 0;
    public bool $stepRunning = false;
    public bool $stepDone = false;
    public bool $stepFailed = false;
    public ?int $currentLogId = null;
    public ?int $applicationId = null;

    // Step 1 – Git Clone
    public string $gitUrl = '';
    public string $folderName = '';
    public string $branch = 'main';

    // Step 4 – Env Setup
    public string $appName = '';
    public string $appUrl = '';
    public string $appEnv = 'production';
    public string $dbName = '';
    public string $dbUser = 'webxkey';
    public string $dbPassword = '';

    // Step 6 – Nginx
    public string $domainName = '';
    public string $nginxPreview = '';

    // Step 7 – Migrate
    public bool $migrationDone = false;
    public bool $seedersRun = false;

    // Step 8 – Done
    public bool $siteIsLive = false;

    private ServerCommandService $cmd;

    public function boot(): void
    {
        $this->cmd = new ServerCommandService();
    }

    // Poll terminal output from DB every 500ms while running
    public function getTerminalOutput(): string
    {
        if (!$this->currentLogId) return '';
        $log = DeploymentLog::find($this->currentLogId);
        return $log?->output ?? '';
    }

    public function getLogStatus(): string
    {
        if (!$this->currentLogId) return 'pending';
        return DeploymentLog::find($this->currentLogId)?->status ?? 'pending';
    }

    // ── Step 1: Clone ────────────────────────────────────────────────────
    public function runClone(): void
    {
        $this->validate([
            'gitUrl'     => 'required|url',
            'folderName' => 'required|alpha_dash',
            'branch'     => 'required|string',
        ]);

        // Create Application record
        $app = Application::create([
            'name'        => $this->appName ?: $this->folderName,
            'domain'      => $this->domainName ?: $this->folderName . '.webxkey.store',
            'folder_path' => $this->folderName,
            'git_repo'    => $this->gitUrl,
            'branch'      => $this->branch,
            'status'      => 'deploying',
        ]);
        $this->applicationId = $app->id;

        $log = $app->deploymentLogs()->create([
            'step'      => 1,
            'step_name' => 'Git Clone',
            'command'   => "git clone {$this->gitUrl} /var/www/{$this->folderName}",
            'status'    => 'pending',
        ]);
        $this->currentLogId = $log->id;
        $this->stepRunning = true;
        $this->stepDone = false;
        $this->stepFailed = false;

        $success = $this->cmd->cloneRepo($this->gitUrl, $this->folderName, $this->branch, $log);
        $this->stepRunning = false;
        $this->stepDone = $success;
        $this->stepFailed = !$success;

        // Pre-fill env fields from step 1 data
        if ($success) {
            if (!$this->appName)   $this->appName   = ucwords(str_replace(['-', '_'], ' ', $this->folderName));
            if (!$this->domainName) $this->domainName = $this->folderName . '.webxkey.store';
            if (!$this->appUrl)    $this->appUrl    = 'https://' . $this->domainName;
            if (!$this->dbName)    $this->dbName    = str_replace('-', '_', $this->folderName) . '_db';
        }
    }

    // ── Step 2: Permissions ─────────────────────────────────────────────
    public function runPermissions(): void
    {
        $app = Application::findOrFail($this->applicationId);
        $log = $app->deploymentLogs()->create([
            'step'      => 2,
            'step_name' => 'Permissions',
            'command'   => "chown -R webxkey:www-data + chmod 775 storage/bootstrap",
            'status'    => 'pending',
        ]);
        $this->currentLogId = $log->id;
        $this->stepRunning = true;

        $success = $this->cmd->setPermissions($this->folderName, $log);
        $this->stepRunning = false;
        $this->stepDone = $success;
        $this->stepFailed = !$success;
    }

    // ── Step 3: Packages ─────────────────────────────────────────────────
    public function runPackages(): void
    {
        $app = Application::findOrFail($this->applicationId);
        $log = $app->deploymentLogs()->create([
            'step'      => 3,
            'step_name' => 'Install Packages',
            'command'   => 'composer update && npm install && npm run build',
            'status'    => 'pending',
        ]);
        $this->currentLogId = $log->id;
        $this->stepRunning = true;

        $this->cmd->composerUpdate($this->folderName, $log);
        $success = $this->cmd->npmBuild($this->folderName, $log);
        $this->stepRunning = false;
        $this->stepDone = $success;
        $this->stepFailed = !$success;
    }

    // ── Step 4: Env Setup ────────────────────────────────────────────────
    public function runEnvSetup(): void
    {
        $this->validate([
            'appName' => 'required|string',
            'appUrl'  => 'required|url',
            'dbName'  => 'required|alpha_dash',
            'dbUser'  => 'required|string',
        ]);

        $success = $this->cmd->writeEnvFile($this->folderName, [
            'app_name'    => $this->appName,
            'app_url'     => $this->appUrl,
            'app_env'     => $this->appEnv,
            'db_name'     => $this->dbName,
            'db_user'     => $this->dbUser,
            'db_password' => $this->dbPassword,
        ]);

        if ($success) {
            $this->cmd->generateAppKey($this->folderName);
            Application::where('id', $this->applicationId)->update(['name' => $this->appName]);
        }

        $this->stepDone = $success;
        $this->stepFailed = !$success;
    }

    // ── Step 5: Database ─────────────────────────────────────────────────
    public function runDatabaseCreate(): void
    {
        $this->validate(['dbName' => 'required|alpha_dash']);

        $app = Application::findOrFail($this->applicationId);
        $log = $app->deploymentLogs()->create([
            'step'      => 5,
            'step_name' => 'Create Database',
            'command'   => "CREATE DATABASE IF NOT EXISTS `{$this->dbName}`",
            'status'    => 'pending',
        ]);
        $this->currentLogId = $log->id;
        $this->stepRunning = true;

        $success = $this->cmd->createDatabase($this->dbName, $log, $this->dbUser, $this->dbPassword);
        Application::where('id', $this->applicationId)->update(['db_name' => $this->dbName]);
        $this->stepRunning = false;
        $this->stepDone = $success;
        $this->stepFailed = !$success;
    }

    public function skipDatabase(): void
    {
        Application::where('id', $this->applicationId)->update(['db_name' => $this->dbName]);
        $this->stepDone = true;
        $this->stepFailed = false;
        $this->currentLogId = null;
    }

    // ── Step 6: Nginx ────────────────────────────────────────────────────
    public function runNginx(): void
    {
        $this->validate(['domainName' => 'required|string']);

        $nginxService = new NginxConfigService();
        $this->nginxPreview = $nginxService->generateConfig($this->domainName, $this->folderName);

        $this->cmd->writeNginxConfig($this->domainName, $this->folderName);
        $this->cmd->enableNginxSite($this->domainName);
        $nginxOk = $this->cmd->testNginx();
        $this->cmd->reloadNginx();

        Application::where('id', $this->applicationId)->update([
            'domain'       => $this->domainName,
            'nginx_config' => "/etc/nginx/sites-available/{$this->domainName}",
        ]);

        $this->stepDone = !str_contains(strtolower($nginxOk), 'failed');
        $this->stepFailed = !$this->stepDone;
    }

    public function previewNginx(): void
    {
        if ($this->domainName) {
            $this->nginxPreview = (new NginxConfigService())
                ->generateConfig($this->domainName, $this->folderName);
        }
    }

    // ── Step 7: Migrate ──────────────────────────────────────────────────
    public function runMigrate(): void
    {
        $app = Application::findOrFail($this->applicationId);
        $log = $app->deploymentLogs()->create([
            'step'      => 7,
            'step_name' => 'Database Migrate',
            'command'   => 'php artisan migrate --force',
            'status'    => 'pending',
        ]);
        $this->currentLogId = $log->id;
        $this->stepRunning = true;

        $success = $this->cmd->runMigrate($this->folderName, $log);
        $this->stepRunning = false;
        $this->migrationDone = $success;
        $this->stepDone = $success;
        $this->stepFailed = !$success;
    }

    public function runMigrateFresh(): void
    {
        $app = Application::findOrFail($this->applicationId);
        $log = $app->deploymentLogs()->create([
            'step'      => 7,
            'step_name' => 'Database Migrate Fresh',
            'command'   => 'php artisan migrate:fresh --force',
            'status'    => 'pending',
        ]);
        $this->currentLogId = $log->id;
        $this->stepRunning = true;

        $success = $this->cmd->runMigrateFresh($this->folderName, $log);
        $this->stepRunning = false;
        $this->migrationDone = $success;
        $this->stepDone = $success;
        $this->stepFailed = !$success;
    }

    public function skipMigrate(): void
    {
        $this->migrationDone = true;
        $this->stepDone = true;
        $this->stepFailed = false;
        $this->currentLogId = null;
    }

    public function runSeeders(): void
    {
        $app = Application::findOrFail($this->applicationId);
        $log = $app->deploymentLogs()->create([
            'step'      => 7,
            'step_name' => 'Database Seed',
            'command'   => 'php artisan db:seed --force',
            'status'    => 'pending',
        ]);
        $this->currentLogId = $log->id;
        $this->stepRunning = true;

        $this->cmd->runSeeders($this->folderName, $log);
        $this->stepRunning = false;
        $this->seedersRun = true;
    }

    // ── Step 8: SSL + Go Live ────────────────────────────────────────────
    public function runSSL(): void
    {
        $app = Application::findOrFail($this->applicationId);
        $log = $app->deploymentLogs()->create([
            'step'      => 8,
            'step_name' => 'SSL Certificate',
            'command'   => "certbot --nginx -d {$this->domainName}",
            'status'    => 'pending',
        ]);
        $this->currentLogId = $log->id;
        $this->stepRunning = true;

        $success = $this->cmd->installSSL($this->domainName, $log);
        $this->stepRunning = false;

        $app->update([
            'status'           => $success ? 'live' : 'error',
            'last_deployed_at' => now(),
        ]);

        $this->siteIsLive = $success;
        $this->stepDone = $success;
        $this->stepFailed = !$success;
    }

    public function skipSSLGoLive(): void
    {
        Application::where('id', $this->applicationId)->update([
            'status'           => 'live',
            'last_deployed_at' => now(),
        ]);
        $this->siteIsLive = true;
        $this->stepDone = true;
    }

    public function nextStep(): void
    {
        $this->stepDone = false;
        $this->stepFailed = false;
        $this->currentLogId = null;
        $this->step = min(7, $this->step + 1);
    }

    public function prevStep(): void
    {
        $this->stepDone = false;
        $this->stepFailed = false;
        $this->step = max(0, $this->step - 1);
    }

    public function resetWizard(): void
    {
        $this->step = 0;
        $this->gitUrl = '';
        $this->folderName = '';
        $this->branch = 'main';
        $this->appName = '';
        $this->appUrl = '';
        $this->dbName = '';
        $this->dbUser = 'root';
        $this->dbPassword = '';
        $this->domainName = '';
        $this->applicationId = null;
        $this->currentLogId = null;
        $this->stepDone = false;
        $this->stepFailed = false;
        $this->migrationDone = false;
        $this->siteIsLive = false;
        $this->nginxPreview = '';
    }

    public function render()
    {
        $terminalOutput = $this->getTerminalOutput();
        $logStatus = $this->getLogStatus();

        return view('livewire.admin.deploy-wizard', [
            'terminalOutput' => $terminalOutput,
            'logStatus'      => $logStatus,
            'stepNames'      => [
                0 => 'Git Clone',
                1 => 'Permissions',
                2 => 'Packages',
                3 => 'Env Setup',
                4 => 'Database',
                5 => 'Nginx',
                6 => 'Migrate',
                7 => 'Go Live',
            ],
        ]);
    }
}
