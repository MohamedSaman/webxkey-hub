<div>
    <div class="topbar">
        <div style="display:flex;align-items:center">
            <button class="hamburger" onclick="toggleSidebar()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            <div class="page-title">Dashboard</div>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <span style="font-size:11.5px;color:var(--text-muted)">Last check: {{ now()->format('H:i') }}</span>
            <a href="{{ route('deploy') }}" class="btn btn-primary">+ Deploy New App</a>
        </div>
    </div>

    <div class="content-area">

        {{-- Stats row --}}
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-label">Live Sites</div>
                <div class="stat-value" style="color:var(--green)">{{ $stats['live'] ?? 0 }}</div>
                <div class="stat-sub">All responding 200</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Warnings</div>
                <div class="stat-value" style="color:var(--yellow)">{{ $stats['warnings'] ?? 0 }}</div>
                <div class="stat-sub">SSL expiring soon</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Server Load</div>
                <div class="stat-value" style="color:{{ ($serverStats['cpu_pct'] ?? 0) > 80 ? 'var(--red)' : 'var(--text-white)' }}">
                    {{ $serverStats['cpu_pct'] ?? '—' }}@if(!empty($serverStats['cpu_pct']))%@endif
                </div>
                <div class="stat-sub">
                    CPU · {{ $serverStats['ram_used_mb'] ?? '—' }}/{{ $serverStats['ram_total_mb'] ?? '—' }} MB RAM
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Disk Used</div>
                <div class="stat-value" style="color:{{ ($serverStats['disk_pct'] ?? 0) > 85 ? 'var(--red)' : 'var(--text-white)' }}">
                    {{ $serverStats['disk_used'] ?? '—' }}
                </div>
                <div class="stat-sub">of 80 GB · {{ $serverStats['disk_pct'] ?? '—' }}%</div>
            </div>
        </div>

        {{-- Section header --}}
        <div class="section-header">
            <span class="section-title">Active Sites</span>
            <div style="display:flex;gap:6px;">
                <span class="badge badge-green">{{ $stats['live'] ?? 0 }} live</span>
                @if(($stats['warnings'] ?? 0) > 0)
                    <span class="badge badge-yellow">{{ $stats['warnings'] }} warnings</span>
                @endif
                @if(($stats['down'] ?? 0) > 0)
                    <span class="badge badge-red">{{ $stats['down'] }} offline</span>
                @endif
            </div>
        </div>

        {{-- Sites grid --}}
        <div class="sites-grid">
            @forelse($applications as $app)
                @php
                    $health   = $app->healthChecks->first();
                    $sslDays  = $health?->ssl_days_remaining;
                    $dotClass = match(true) {
                        $app->status === 'stopped'   => 'dot-gray',
                        $app->status === 'error'     => 'dot-red',
                        $app->status === 'deploying' => 'dot-yellow',
                        $health?->is_up === false    => 'dot-red',
                        $health?->response_ms > 500  => 'dot-yellow',
                        default                      => 'dot-green',
                    };
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
                                    <span style="color:{{ $health->is_up ? 'var(--green)' : 'var(--red)' }}">{{ $health->http_status }}</span>
                                    <span style="color:var(--text-muted);font-size:12px;font-weight:400"> · {{ $health->response_ms }}ms</span>
                                @else
                                    <span style="color:var(--text-muted)">—</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="meta-item">SSL</div>
                            <div class="meta-val" style="color:{{ $sslDays && $sslDays < 30 ? 'var(--yellow)' : 'var(--text-white)' }}">
                                {{ $sslDays ? $sslDays.' days' : '—' }}
                                @if($sslDays && $sslDays < 30) <span style="font-size:12px">⚠</span>@endif
                            </div>
                        </div>
                        <div>
                            <div class="meta-item">Last pull</div>
                            <div class="meta-val">{{ $app->lastPullHuman() }}</div>
                        </div>
                        <div>
                            <div class="meta-item">Branch</div>
                            <div class="meta-val" style="font-family:var(--font-mono);font-size:12.5px">{{ $app->branch }}</div>
                        </div>
                    </div>

                    @if(isset($quickOutputs[$app->id]))
                        <div class="terminal" style="min-height:36px;font-size:11px;margin-bottom:10px;max-height:100px;overflow-y:auto;">{{ $quickOutputs[$app->id] }}</div>
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
                        <a href="{{ route('applications.show', $app) }}" class="act-btn" style="text-decoration:none;">Details</a>
                    </div>
                </div>
            @empty
                <div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--text-muted);font-size:13px;">
                    No applications yet.
                    <a href="{{ route('deploy') }}" style="color:var(--accent)">Deploy your first app →</a>
                </div>
            @endforelse
        </div>

    </div>
</div>
