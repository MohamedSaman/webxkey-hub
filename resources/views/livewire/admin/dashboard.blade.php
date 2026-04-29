<div>
    {{-- Topbar --}}
    <div class="topbar">
        <div class="page-title">Dashboard</div>
        <div style="display:flex;gap:8px;align-items:center">
            <span style="font-size:11px;color:var(--color-text-tertiary)">{{ now()->format('d M Y, H:i') }}</span>
            <a href="{{ route('deploy') }}" class="btn btn-primary">+ Deploy New App</a>
        </div>
    </div>

    <div class="content-area">

        {{-- Stats row --}}
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-label">Live Sites</div>
                <div class="stat-value" style="color:#3B6D11">{{ $stats['live'] ?? 0 }}</div>
                <div class="stat-sub">of {{ $stats['total'] ?? 0 }} registered</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Warnings</div>
                <div class="stat-value" style="color:#BA7517">{{ $stats['warnings'] ?? 0 }}</div>
                <div class="stat-sub">SSL expiring soon</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Offline</div>
                <div class="stat-value" style="color:{{ ($stats['down'] ?? 0) > 0 ? '#E24B4A' : 'var(--color-text-tertiary)' }}">{{ $stats['down'] ?? 0 }}</div>
                <div class="stat-sub">stopped / error</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Apps</div>
                <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-sub">managed sites</div>
            </div>
        </div>

        {{-- Sites grid --}}
        <div class="section-header">
            <span class="section-title">Active Sites</span>
            <div style="display:flex;gap:6px">
                <span class="badge badge-green">{{ $stats['live'] ?? 0 }} live</span>
                @if(($stats['warnings'] ?? 0) > 0)
                    <span class="badge badge-yellow">{{ $stats['warnings'] }} warnings</span>
                @endif
                @if(($stats['down'] ?? 0) > 0)
                    <span class="badge badge-red">{{ $stats['down'] }} offline</span>
                @endif
            </div>
        </div>

        <div class="sites-grid">
            @forelse($applications as $app)
                @php
                    $health = $app->healthChecks->first();
                    $dotClass = match(true) {
                        $app->status === 'stopped' => 'dot-gray',
                        $app->status === 'error'   => 'dot-red',
                        $app->status === 'deploying' => 'dot-yellow',
                        $health?->is_up === false  => 'dot-red',
                        $health?->response_ms > 500 => 'dot-yellow',
                        default => 'dot-green',
                    };
                    $sslDays = $health?->ssl_days_remaining;
                @endphp
                <div class="site-card">
                    <div class="site-card-header">
                        <div>
                            <div class="site-name">{{ $app->name }}</div>
                            <div class="site-domain">{{ $app->domain }}</div>
                        </div>
                        <div class="status-dot {{ $dotClass }}"></div>
                    </div>
                    <div class="site-meta">
                        <div>
                            <div class="meta-item">HTTP</div>
                            <div class="meta-val">
                                @if($health)
                                    {{ $health->http_status }} · {{ $health->response_ms }}ms
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="meta-item">SSL</div>
                            <div class="meta-val" @if($sslDays && $sslDays < 30) style="color:#BA7517" @endif>
                                {{ $sslDays ? $sslDays.' days' : '—' }}
                                @if($sslDays && $sslDays < 30) ⚠ @endif
                            </div>
                        </div>
                        <div>
                            <div class="meta-item">Last pull</div>
                            <div class="meta-val">{{ $app->lastPullHuman() }}</div>
                        </div>
                        <div>
                            <div class="meta-item">Branch</div>
                            <div class="meta-val">{{ $app->branch }}</div>
                        </div>
                    </div>

                    @if(isset($quickOutputs[$app->id]))
                        <div class="terminal" style="min-height:40px;font-size:11px;margin-bottom:8px;">{{ $quickOutputs[$app->id] }}</div>
                    @endif

                    <div class="site-actions">
                        <button class="act-btn" wire:click="quickPull({{ $app->id }})" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="quickPull({{ $app->id }})">Pull</span>
                            <span wire:loading wire:target="quickPull({{ $app->id }})">...</span>
                        </button>
                        <button class="act-btn" wire:click="quickCacheClean({{ $app->id }})" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="quickCacheClean({{ $app->id }})">Cache</span>
                            <span wire:loading wire:target="quickCacheClean({{ $app->id }})">...</span>
                        </button>
                        <a href="{{ route('applications.show', $app) }}" class="act-btn" style="text-align:center;text-decoration:none;">Details</a>
                    </div>
                </div>
            @empty
                <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--color-text-tertiary);font-size:13px;">
                    No applications yet. <a href="{{ route('deploy') }}" style="color:#185FA5">Deploy your first app →</a>
                </div>
            @endforelse
        </div>

    </div>
</div>
