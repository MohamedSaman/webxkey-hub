<?php

namespace App\Services;

use App\Models\DeploymentLog;
use Illuminate\Support\Facades\Crypt;

class ServerCommandService
{
    private string $wwwPath = '/var/www';
    private string $nginxAvailable = '/etc/nginx/sites-available';
    private string $nginxEnabled = '/etc/nginx/sites-enabled';

    // Build a sudo prefix. www-data is configured with NOPASSWD in sudoers,
    // so no password needs to be piped. Optional $asUser runs the command
    // as a different OS user (e.g. 'webxkey').
    private function sudo(string $asUser = ''): string
    {
        $userFlag = $asUser ? " -u {$asUser}" : '';
        return "sudo{$userFlag}";
    }

    // Run a command and stream output line-by-line into a DeploymentLog record
    private function streamCommand(string $cmd, DeploymentLog $log): bool
    {
        $log->markRunning();

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptors, $pipes);

        if (!is_resource($process)) {
            $log->appendOutput("ERROR: Failed to start process");
            $log->markFailed();
            return false;
        }

        fclose($pipes[0]);

        while (!feof($pipes[1])) {
            $line = fgets($pipes[1]);
            if ($line !== false) {
                $log->appendOutput(rtrim($line));
            }
        }

        while (!feof($pipes[2])) {
            $line = fgets($pipes[2]);
            if ($line !== false) {
                $log->appendOutput('[err] ' . rtrim($line));
            }
        }

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            $log->markFailed();
            return false;
        }

        $log->markSuccess();
        return true;
    }

    // Run a quick one-shot command and return combined output string (no log record)
    private function runQuick(string $cmd): string
    {
        $output = [];
        exec($cmd . ' 2>&1', $output);
        return implode("\n", $output);
    }

    public function cloneRepo(string $repo, string $folder, string $branch, DeploymentLog $log): bool
    {
        $safeRepo   = escapeshellarg($repo);
        $safeFolder = escapeshellarg("{$this->wwwPath}/{$folder}");
        $safeBranch = escapeshellarg($branch);
        $sudo = $this->sudo('webxkey');
        $cmd = "{$sudo} git clone --branch {$safeBranch} {$safeRepo} {$safeFolder} 2>&1";
        return $this->streamCommand($cmd, $log);
    }

    public function setPermissions(string $folder, DeploymentLog $log): bool
    {
        $path        = escapeshellarg("{$this->wwwPath}/{$folder}");
        $storagePath = escapeshellarg("{$this->wwwPath}/{$folder}/storage");
        $cachePath   = escapeshellarg("{$this->wwwPath}/{$folder}/bootstrap/cache");
        $sudo = $this->sudo();

        $cmd = "{$sudo} chown -R webxkey:www-data {$path} 2>&1 && "
             . "{$sudo} chmod -R 775 {$storagePath} 2>&1 && "
             . "{$sudo} chmod -R 775 {$cachePath} 2>&1";

        return $this->streamCommand($cmd, $log);
    }

    public function composerUpdate(string $folder, DeploymentLog $log): bool
    {
        $path = escapeshellarg("{$this->wwwPath}/{$folder}");
        $cmd  = "cd {$path} && composer update --no-interaction 2>&1";
        return $this->streamCommand($cmd, $log);
    }

    public function npmBuild(string $folder, DeploymentLog $log): bool
    {
        $path = escapeshellarg("{$this->wwwPath}/{$folder}");
        $cmd = "cd {$path} && npm install --cache /tmp/.npm-cache 2>&1 "
             . "&& PATH=\"\$(pwd)/node_modules/.bin:\$PATH\" npm run build 2>&1";
        return $this->streamCommand($cmd, $log);
    }

    public function writeEnvFile(string $folder, array $config): bool
    {
        $envPath     = "{$this->wwwPath}/{$folder}/.env";
        $examplePath = "{$this->wwwPath}/{$folder}/.env.example";

        // Start from .env.example if available, otherwise build from scratch
        $content = file_exists($examplePath) ? file_get_contents($examplePath) : '';

        // Full set of defaults — merged with any values already in .env.example
        $defaults = [
            'APP_NAME'                => '"' . ($config['app_name'] ?? 'Laravel') . '"',
            'APP_ENV'                 => 'production',
            'APP_KEY'                 => '',
            'APP_DEBUG'               => 'false',
            'APP_URL'                 => $config['app_url'] ?? 'http://localhost',
            'APP_LOCALE'              => 'en',
            'APP_FALLBACK_LOCALE'     => 'en',
            'APP_FAKER_LOCALE'        => 'en_US',
            'APP_MAINTENANCE_DRIVER'  => 'file',
            'BCRYPT_ROUNDS'           => '12',
            'LOG_CHANNEL'             => 'stack',
            'LOG_STACK'               => 'single',
            'LOG_DEPRECATIONS_CHANNEL'=> 'null',
            'LOG_LEVEL'               => 'debug',
            'DB_CONNECTION'           => 'mysql',
            'DB_HOST'                 => '127.0.0.1',
            'DB_PORT'                 => '3306',
            'DB_DATABASE'             => $config['db_name'] ?? '',
            'DB_USERNAME'             => $config['db_user'] ?? 'webxkey',
            'DB_PASSWORD'             => $config['db_password'] ?? '',
            'SESSION_DRIVER'          => 'database',
            'SESSION_LIFETIME'        => '120',
            'SESSION_ENCRYPT'         => 'false',
            'SESSION_PATH'            => '/',
            'SESSION_DOMAIN'          => 'null',
            'BROADCAST_CONNECTION'    => 'log',
            'FILESYSTEM_DISK'         => 'local',
            'QUEUE_CONNECTION'        => 'database',
            'CACHE_STORE'             => 'database',
            'MEMCACHED_HOST'          => '127.0.0.1',
            'REDIS_CLIENT'            => 'phpredis',
            'REDIS_HOST'              => '127.0.0.1',
            'REDIS_PASSWORD'          => 'null',
            'REDIS_PORT'              => '6379',
            'MAIL_MAILER'             => 'log',
            'MAIL_SCHEME'             => 'null',
            'MAIL_HOST'               => '127.0.0.1',
            'MAIL_PORT'               => '2525',
            'MAIL_USERNAME'           => 'null',
            'MAIL_PASSWORD'           => 'null',
            'MAIL_FROM_ADDRESS'       => '"hello@' . ($config['app_url'] ? parse_url($config['app_url'], PHP_URL_HOST) : 'example.com') . '"',
            'MAIL_FROM_NAME'          => '"${APP_NAME}"',
            'VITE_APP_NAME'           => '"${APP_NAME}"',
        ];

        foreach ($defaults as $key => $value) {
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        return file_put_contents($envPath, $content) !== false;
    }

    public function generateAppKey(string $folder): string
    {
        $path = escapeshellarg("{$this->wwwPath}/{$folder}");
        return $this->runQuick("cd {$path} && {$this->cleanEnv} php artisan key:generate --show 2>&1");
    }

    public function writeAppKey(string $folder, string $key): bool
    {
        $envPath = "{$this->wwwPath}/{$folder}/.env";
        $content = file_exists($envPath) ? file_get_contents($envPath) : '';
        $safeKey = escapeshellarg($key);
        if (preg_match('/^APP_KEY=/m', $content)) {
            $content = preg_replace('/^APP_KEY=.*/m', "APP_KEY={$key}", $content);
        } else {
            $content .= "\nAPP_KEY={$key}";
        }
        return file_put_contents($envPath, $content) !== false;
    }

    public function createDatabase(string $dbName, DeploymentLog $log, string $dbUser = 'webxkey', string $dbPassword = ''): bool
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);
        $userFlag = "-u " . escapeshellarg($dbUser);
        $passFlag = $dbPassword ? "-p" . escapeshellarg($dbPassword) : '';
        $cmd = "mysql {$userFlag} {$passFlag} -e " . escapeshellarg("CREATE DATABASE IF NOT EXISTS `{$safeName}`;") . " 2>&1";
        return $this->streamCommand($cmd, $log);
    }

    public function writeNginxConfig(string $domain, string $folder, string $phpVersion = '8.3'): bool
    {
        $service     = new NginxConfigService();
        $config      = $service->generateConfig($domain, $folder, $phpVersion);
        $configPath  = $service->getConfigPath($domain);
        $safeConfig  = escapeshellarg($config);
        $safeConfigPath = escapeshellarg($configPath);
        $sudo = $this->sudo();
        $result = $this->runQuick("echo {$safeConfig} | {$sudo} tee {$safeConfigPath}");
        return !str_contains(strtolower($result), 'error');
    }

    public function enableNginxSite(string $domain): bool
    {
        $service   = new NginxConfigService();
        $available = escapeshellarg($service->getConfigPath($domain));
        $enabled   = escapeshellarg($service->getEnabledPath($domain));
        $sudo = $this->sudo();
        $this->runQuick("{$sudo} ln -sf {$available} {$enabled}");
        return true;
    }

    public function disableNginxSite(string $domain): bool
    {
        $service = new NginxConfigService();
        $enabled = escapeshellarg($service->getEnabledPath($domain));
        $sudo = $this->sudo();
        $this->runQuick("{$sudo} rm -f {$enabled}");
        return true;
    }

    public function testNginx(): string
    {
        return $this->runQuick("{$this->sudo()} nginx -t");
    }

    public function reloadNginx(): string
    {
        return $this->runQuick("{$this->sudo()} systemctl reload nginx");
    }

    private string $cleanEnv = 'env -i HOME=/tmp PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin';

    public function runMigrate(string $folder, DeploymentLog $log, bool $seed = false): bool
    {
        $path     = escapeshellarg("{$this->wwwPath}/{$folder}");
        $seedFlag = $seed ? ' --seed' : '';
        $cmd = "cd {$path} && {$this->cleanEnv} php artisan migrate --force{$seedFlag} 2>&1";
        return $this->streamCommand($cmd, $log);
    }

    public function runMigrateFresh(string $folder, DeploymentLog $log): bool
    {
        $path = escapeshellarg("{$this->wwwPath}/{$folder}");
        $cmd  = "cd {$path} && {$this->cleanEnv} php artisan migrate:fresh --force 2>&1";
        return $this->streamCommand($cmd, $log);
    }

    public function runSeeders(string $folder, DeploymentLog $log): bool
    {
        $path = escapeshellarg("{$this->wwwPath}/{$folder}");
        $cmd = "cd {$path} && {$this->cleanEnv} php artisan db:seed --force 2>&1";
        return $this->streamCommand($cmd, $log);
    }

    public function installSSL(string $domain, DeploymentLog $log): bool
    {
        $safeDomain = escapeshellarg($domain);
        $sudo = $this->sudo();
        $cmd = "{$sudo} certbot --nginx -d {$safeDomain} --non-interactive --agree-tos -m admin@webxkey.com 2>&1";
        return $this->streamCommand($cmd, $log);
    }

    public function deleteFolder(string $folder): bool
    {
        $path = escapeshellarg("{$this->wwwPath}/{$folder}");
        $sudo = $this->sudo();
        $output = $this->runQuick("{$sudo} rm -rf {$path} 2>&1");
        return empty($output) || !str_contains(strtolower($output), 'error');
    }

    public function gitPull(string $folder, string $branch, DeploymentLog $log): bool
    {
        $path       = escapeshellarg("{$this->wwwPath}/{$folder}");
        $safeBranch = escapeshellarg($branch);
        $cmd = "cd {$path} && git pull origin {$safeBranch} 2>&1";
        return $this->streamCommand($cmd, $log);
    }

    public function gitStatus(string $folder): string
    {
        $path = escapeshellarg("{$this->wwwPath}/{$folder}");
        return $this->runQuick("cd {$path} && git status");
    }

    public function gitLog(string $folder, int $lines = 5): string
    {
        $path = escapeshellarg("{$this->wwwPath}/{$folder}");
        return $this->runQuick("cd {$path} && git log --oneline -{$lines}");
    }

    public function optimizeClear(string $folder): string
    {
        $path = escapeshellarg("{$this->wwwPath}/{$folder}");
        return $this->runQuick("cd {$path} && php artisan optimize:clear 2>&1");
    }

    public function queueRestart(string $folder): string
    {
        $path = escapeshellarg("{$this->wwwPath}/{$folder}");
        return $this->runQuick("cd {$path} && php artisan queue:restart 2>&1");
    }

    public function maintenanceDown(string $folder): string
    {
        $path = escapeshellarg("{$this->wwwPath}/{$folder}");
        return $this->runQuick("cd {$path} && php artisan down 2>&1");
    }

    public function maintenanceUp(string $folder): string
    {
        $path = escapeshellarg("{$this->wwwPath}/{$folder}");
        return $this->runQuick("cd {$path} && php artisan up 2>&1");
    }

    public function getServerStats(): array
    {
        $cpuLine  = $this->runQuick("top -bn1 | grep 'Cpu(s)' | awk '{print $2}' | cut -d'%' -f1");
        $cpu      = (float) trim($cpuLine);

        $memInfo  = $this->runQuick("free -m | grep Mem");
        $memParts = preg_split('/\s+/', trim($memInfo));
        $ramTotal = (int) ($memParts[1] ?? 1);
        $ramUsed  = (int) ($memParts[2] ?? 0);
        $ramPct   = $ramTotal > 0 ? round($ramUsed / $ramTotal * 100) : 0;

        $diskLine  = $this->runQuick("df -h /var/www | tail -1");
        $diskParts = preg_split('/\s+/', trim($diskLine));
        $diskUsed  = $diskParts[2] ?? '0G';
        $diskPct   = (int) trim($diskParts[4] ?? '0', '%');

        return [
            'cpu_pct'      => round($cpu),
            'ram_pct'      => $ramPct,
            'ram_used_mb'  => $ramUsed,
            'ram_total_mb' => $ramTotal,
            'disk_pct'     => $diskPct,
            'disk_used'    => $diskUsed,
        ];
    }
}
