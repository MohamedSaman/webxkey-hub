<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = [
        'name', 'domain', 'folder_path', 'git_repo', 'branch',
        'db_name', 'nginx_config', 'status', 'php_version',
        'last_pull_at', 'last_deployed_at',
    ];

    protected $casts = [
        'last_pull_at' => 'datetime',
        'last_deployed_at' => 'datetime',
    ];

    public function deploymentLogs(): HasMany
    {
        return $this->hasMany(DeploymentLog::class);
    }

    public function healthChecks(): HasMany
    {
        return $this->hasMany(HealthCheck::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestHealth(): ?HealthCheck
    {
        return $this->healthChecks()->latest('checked_at')->first();
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    public function sslDaysRemaining(): ?int
    {
        return $this->latestHealth()?->ssl_days_remaining;
    }

    public function lastPullHuman(): string
    {
        return $this->last_pull_at ? $this->last_pull_at->diffForHumans() : 'Never';
    }
}
