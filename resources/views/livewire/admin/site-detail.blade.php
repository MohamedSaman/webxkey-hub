<div>
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:10px;">
            <a href="{{ route('dashboard') }}" class="btn">← Back</a>
            <div class="page-title">{{ $application->name }}</div>
            @switch($application->status)
                @case('live')    <span class="badge badge-green">Live</span> @break
                @case('stopped') <span class="badge badge-gray">Stopped</span> @break
                @case('error')   <span class="badge badge-red">Error</span> @break
                @case('deploying')<span class="badge badge-blue">Deploying</span> @break
            @endswitch
        </div>
        <div style="display:flex;gap:8px;">
            <a href="https://{{ $application->domain }}" target="_blank" class="btn">Open Site ↗</a>
            @if($application->status === 'live')
                <button wire:click="stopSite" class="btn btn-danger" onclick="return confirm('Stop {{ $application->domain }}?')">Stop Site</button>
            @else
                <button wire:click="startSite" class="btn btn-success">Start Site</button>
            @endif
        </div>
    </div>

    <div class="content-area">
        <div style="display:grid;grid-template-columns:1fr 340px;gap:16px;">

            {{-- Left column --}}
            <div>
                {{-- Quick Actions --}}
                <div class="panel" style="margin-bottom:12px;">
                    <div class="panel-title">Quick Actions</div>
                    <div class="actions-grid">
                        <div class="action-tile" wire:click="gitPull">
                            <div class="action-tile-icon">↓</div>
                            <div class="action-tile-label">Git Pull</div>
                            <div class="action-tile-desc">Fetch latest code</div>
                        </div>
                        <div class="action-tile" wire:click="clearCache">
                            <div class="action-tile-icon">⟳</div>
                            <div class="action-tile-label">Clear Cache</div>
                            <div class="action-tile-desc">optimize:clear</div>
                        </div>
                        <div class="action-tile" wire:click="restartQueue">
                            <div class="action-tile-icon">↺</div>
                            <div class="action-tile-label">Restart Queue</div>
                            <div class="action-tile-desc">queue:restart</div>
                        </div>
                        <div class="action-tile" wire:click="runMigrate">
                            <div class="action-tile-icon">⬆</div>
                            <div class="action-tile-label">Run Migrate</div>
                            <div class="action-tile-desc">migrate --force</div>
                        </div>
                        <div class="action-tile" wire:click="toggleMaintenance">
                            <div class="action-tile-icon">⚙</div>
                            <div class="action-tile-label">{{ $inMaintenance ? 'Bring Online' : 'Maintenance' }}</div>
                            <div class="action-tile-desc">artisan {{ $inMaintenance ? 'up' : 'down' }}</div>
                        </div>
                        <div class="action-tile" wire:click="checkGitStatus">
                            <div class="action-tile-icon">⊞</div>
                            <div class="action-tile-label">Git Status</div>
                            <div class="action-tile-desc">Check changes</div>
                        </div>
                    </div>

                    @if($isRunning)
                        <div style="display:flex;align-items:center;gap:8px;margin-top:12px;font-size:12px;color:var(--color-text-tertiary);">
                            <span class="spinner"></span> Running...
                        </div>
                    @endif
                </div>

                {{-- Action Output --}}
                <div class="panel">
                    <div class="panel-title">Action Output</div>
                    <div class="terminal" style="min-height:160px;max-height:300px;overflow-y:auto;">
                        @if($actionOutput)
                            {{ $actionOutput }}
                        @else
                            <span style="color:#888780;">Click any action above to see live output...</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right column --}}
            <div>
                {{-- Site Info --}}
                <div class="panel" style="margin-bottom:12px;">
                    <div class="panel-title">Site Info</div>
                    <div class="info-row"><span class="info-key">Domain</span><span>{{ $application->domain }}</span></div>
                    <div class="info-row"><span class="info-key">Folder</span><span class="mono" style="font-size:11px;">/var/www/{{ $application->folder_path }}</span></div>
                    <div class="info-row"><span class="info-key">Branch</span><span>{{ $application->branch }}</span></div>
                    <div class="info-row"><span class="info-key">Database</span><span>{{ $application->db_name ?? '—' }}</span></div>
                    <div class="info-row"><span class="info-key">PHP version</span><span>{{ $application->php_version }}</span></div>
                    <div class="info-row">
                        <span class="info-key">SSL expires</span>
                        @php $sslDays = $latestHealth?->ssl_days_remaining; @endphp
                        <span @if($sslDays && $sslDays < 30) style="color:#BA7517;font-weight:500;" @elseif($sslDays) style="color:#3B6D11;" @endif>
                            {{ $sslDays ? $sslDays.' days' : '—' }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">HTTP status</span>
                        <span style="color:{{ $latestHealth?->is_up ? '#3B6D11' : '#A32D2D' }}">
                            {{ $latestHealth?->http_status ?? '—' }}
                            @if($latestHealth?->response_ms) · {{ $latestHealth->response_ms }}ms @endif
                        </span>
                    </div>
                    <div class="info-row"><span class="info-key">Last pull</span><span>{{ $application->lastPullHuman() }}</span></div>
                    <div class="info-row">
                        <span class="info-key">Last deployed</span>
                        <span>{{ $application->last_deployed_at ? $application->last_deployed_at->diffForHumans() : 'Unknown' }}</span>
                    </div>
                    @if($application->git_repo)
                        <div class="info-row">
                            <span class="info-key">Git repo</span>
                            <span class="mono" style="font-size:10px;word-break:break-all;">{{ Str::limit($application->git_repo, 35) }}</span>
                        </div>
                    @endif
                </div>

                {{-- Uptime bar --}}
                <div class="panel" style="margin-bottom:12px;">
                    <div class="panel-title">Uptime (last {{ $healthHistory->count() }} checks)</div>
                    @php
                        $upCount = $healthHistory->where('is_up', true)->count();
                        $total = $healthHistory->count();
                        $uptimePct = $total > 0 ? round($upCount / $total * 100, 1) : 0;
                    @endphp
                    <div style="font-size:20px;font-weight:500;margin-bottom:8px;color:{{ $uptimePct >= 99 ? '#3B6D11' : ($uptimePct >= 90 ? '#BA7517' : '#A32D2D') }}">
                        {{ $uptimePct }}%
                    </div>
                    <div class="health-bar">
                        @if($healthHistory->isEmpty())
                            @for($i = 0; $i < 30; $i++)
                                <div class="hb-seg empty"></div>
                            @endfor
                        @else
                            @foreach($healthHistory as $check)
                                <div class="hb-seg {{ !$check->is_up ? 'err' : ($check->response_ms > 500 ? 'warn' : '') }}"
                                     title="{{ $check->checked_at->format('d M H:i') }} — {{ $check->http_status }} {{ $check->response_ms }}ms"></div>
                            @endforeach
                            @for($i = $healthHistory->count(); $i < 30; $i++)
                                <div class="hb-seg empty"></div>
                            @endfor
                        @endif
                    </div>
                    <div style="font-size:10px;color:var(--color-text-tertiary);margin-top:6px;">Green = OK · Yellow = slow (>500ms) · Red = error</div>
                </div>

                {{-- Server Stats --}}
                <div class="panel">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:10px;border-bottom:0.5px solid var(--color-border-t);">
                        <span style="font-size:13px;font-weight:500;">Server Stats</span>
                        <button wire:click="refreshStats" class="btn btn-sm">Refresh</button>
                    </div>
                    @if(!empty($serverStats))
                        <div class="server-stats-grid">
                            <div class="sstat">
                                <div class="sstat-label">CPU</div>
                                <div class="sstat-value">{{ $serverStats['cpu_pct'] }}%</div>
                                <div class="progress-mini"><div class="progress-fill {{ $serverStats['cpu_pct'] > 80 ? 'danger' : ($serverStats['cpu_pct'] > 60 ? 'warn' : '') }}" style="width:{{ $serverStats['cpu_pct'] }}%"></div></div>
                            </div>
                            <div class="sstat">
                                <div class="sstat-label">RAM</div>
                                <div class="sstat-value">{{ $serverStats['ram_pct'] }}%</div>
                                <div class="progress-mini"><div class="progress-fill {{ $serverStats['ram_pct'] > 80 ? 'danger' : ($serverStats['ram_pct'] > 60 ? 'warn' : '') }}" style="width:{{ $serverStats['ram_pct'] }}%"></div></div>
                            </div>
                            <div class="sstat">
                                <div class="sstat-label">Disk</div>
                                <div class="sstat-value">{{ $serverStats['disk_pct'] }}%</div>
                                <div class="progress-mini"><div class="progress-fill {{ $serverStats['disk_pct'] > 80 ? 'danger' : '' }}" style="width:{{ $serverStats['disk_pct'] }}%"></div></div>
                            </div>
                            <div class="sstat">
                                <div class="sstat-label">Nginx</div>
                                <div class="sstat-value" style="font-size:13px;color:#3B6D11;">Active</div>
                            </div>
                        </div>
                    @else
                        <div style="font-size:12px;color:var(--color-text-tertiary);text-align:center;padding:16px 0;">
                            Click Refresh to load server stats
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
