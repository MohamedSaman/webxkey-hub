<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Services\HealthCheckService;
use Illuminate\Console\Command;

class HealthCheckCommand extends Command
{
    protected $signature = 'health:check {--app= : Check a specific app by ID}';
    protected $description = 'Ping all live sites and store health check results';

    public function handle(HealthCheckService $service): int
    {
        $appId = $this->option('app');

        if ($appId) {
            $app = Application::find($appId);
            if (!$app) {
                $this->error("App #{$appId} not found.");
                return self::FAILURE;
            }
            $check = $service->checkSite($app);
            $this->info("{$app->domain} — HTTP {$check->http_status} · {$check->response_ms}ms · SSL {$check->ssl_days_remaining}d");
            return self::SUCCESS;
        }

        $apps = Application::where('status', 'live')->get();
        $this->info("Checking {$apps->count()} live sites...");

        foreach ($apps as $app) {
            try {
                $check = $service->checkSite($app);
                $status = $check->is_up ? '✓' : '✗';
                $this->line("  {$status} {$app->domain} — {$check->http_status} · {$check->response_ms}ms · SSL {$check->ssl_days_remaining}d");
            } catch (\Throwable $e) {
                $this->error("  ✗ {$app->domain} — {$e->getMessage()}");
            }
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
