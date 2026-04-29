<?php

namespace App\Services;

use App\Models\Application;
use App\Models\HealthCheck;
use Illuminate\Support\Facades\Http;

class HealthCheckService
{
    public function checkSite(Application $app): HealthCheck
    {
        $url = "https://{$app->domain}";
        $httpStatus = null;
        $responseMs = null;
        $isUp = false;

        try {
            $start = microtime(true);
            $response = Http::timeout(10)->get($url);
            $responseMs = (int) ((microtime(true) - $start) * 1000);
            $httpStatus = $response->status();
            $isUp = $response->successful() || $httpStatus < 500;
        } catch (\Exception $e) {
            // Try HTTP fallback
            try {
                $fallbackUrl = "http://{$app->domain}";
                $start = microtime(true);
                $response = Http::timeout(10)->get($fallbackUrl);
                $responseMs = (int) ((microtime(true) - $start) * 1000);
                $httpStatus = $response->status();
                $isUp = $response->successful() || $httpStatus < 500;
            } catch (\Exception $e2) {
                $isUp = false;
            }
        }

        $sslDays = $this->getSslDays($app->domain);

        return HealthCheck::create([
            'application_id'    => $app->id,
            'http_status'       => $httpStatus,
            'response_ms'       => $responseMs,
            'ssl_days_remaining' => $sslDays,
            'is_up'             => $isUp,
            'checked_at'        => now(),
        ]);
    }

    public function checkAll(): void
    {
        Application::where('status', 'live')->each(function (Application $app) {
            $this->checkSite($app);
        });
    }

    public function getSslDays(string $domain): ?int
    {
        try {
            $safeDomain = escapeshellarg($domain);
            $output = shell_exec(
                "echo | openssl s_client -connect {$safeDomain}:443 -servername {$safeDomain} 2>/dev/null"
                . " | openssl x509 -noout -enddate 2>/dev/null"
            );

            if (!$output) return null;

            preg_match('/notAfter=(.+)/', $output, $matches);
            if (empty($matches[1])) return null;

            $expiry = strtotime(trim($matches[1]));
            if (!$expiry) return null;

            return (int) ceil(($expiry - time()) / 86400);
        } catch (\Exception $e) {
            return null;
        }
    }
}
