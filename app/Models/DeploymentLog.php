<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentLog extends Model
{
    protected $fillable = [
        'application_id', 'step', 'step_name', 'command', 'output', 'status',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function appendOutput(string $line): void
    {
        $this->output = ($this->output ?? '') . $line . "\n";
        $this->save();
    }

    public function markRunning(): void
    {
        $this->update(['status' => 'running']);
    }

    public function markSuccess(): void
    {
        $this->update(['status' => 'success']);
    }

    public function markFailed(): void
    {
        $this->update(['status' => 'failed']);
    }
}
