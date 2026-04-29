<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthCheck extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'application_id', 'http_status', 'response_ms',
        'ssl_days_remaining', 'is_up', 'checked_at',
    ];

    protected $casts = [
        'is_up' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function statusColor(): string
    {
        if (!$this->is_up) return 'red';
        if ($this->response_ms > 500) return 'yellow';
        return 'green';
    }

    public function sslWarning(): bool
    {
        return $this->ssl_days_remaining !== null && $this->ssl_days_remaining < 30;
    }
}
