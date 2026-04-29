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
                <div class="panel" style="margin-bottom:12px;">
                    <div class="panel-title">Action Output</div>
                    <div class="terminal" style="min-height:160px;max-height:300px;overflow-y:auto;">
                        @if($actionOutput)
                            {{ $actionOutput }}
                        @else
                            <span style="color:var(--text-muted);">Click any action above to see live output...</span>
                        @endif
                    </div>
                </div>

                {{-- Laravel Log Viewer --}}
                <div class="panel">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid var(--border);">
                        <span style="font-size:13px;font-weight:600;color:var(--text-white);">Laravel Log</span>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <select wire:model.live="logLines" style="background:var(--bg-input);border:1px solid var(--border-strong);color:var(--text-secondary);border-radius:var(--radius-sm);padding:4px 8px;font-size:11.5px;cursor:pointer;">
                                <option value="50">Last 50 lines</option>
                                <option value="100">Last 100 lines</option>
                                <option value="200">Last 200 lines</option>
                                <option value="500">Last 500 lines</option>
                            </select>
                            <button wire:click="loadLog" class="btn btn-sm" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="loadLog">⟳ Load Log</span>
                                <span wire:loading wire:target="loadLog"><span class="spinner"></span></span>
                            </button>
                            @if($logLoaded)
                                <button wire:click="clearLog" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Clear laravel.log for {{ $application->name }}?')">
                                    Clear
                                </button>
                            @endif
                        </div>
                    </div>

                    @if($logLoaded)
                        @php
                            // Colorize log lines
                            $lines = explode("\n", $logOutput);
                        @endphp
                        <div class="terminal" style="min-height:200px;max-height:480px;overflow-y:auto;font-size:11.5px;line-height:1.7;" id="log-output">
                            @foreach($lines as $line)
                                @if(str_contains($line, '.ERROR') || str_contains($line, 'ERROR:'))
                                    <div style="color:#f87171;">{{ $line }}</div>
                                @elseif(str_contains($line, '.WARNING') || str_contains($line, 'WARNING:'))
                                    <div style="color:#fbbf24;">{{ $line }}</div>
                                @elseif(str_contains($line, '.INFO') || str_contains($line, 'INFO:'))
                                    <div style="color:#60a5fa;">{{ $line }}</div>
                                @elseif(str_contains($line, 'Stack trace') || str_contains($line, '#'))
                                    <div style="color:#6b7280;">{{ $line }}</div>
                                @else
                                    <div>{{ $line }}</div>
                                @endif
                            @endforeach
                        </div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:8px;">
                            /var/www/{{ $application->folder_path }}/storage/logs/laravel.log
                        </div>
                    @else
                        <div style="text-align:center;padding:32px;color:var(--text-muted);font-size:12.5px;">
                            Click <strong style="color:var(--text-secondary)">Load Log</strong> to view the Laravel error log
                        </div>
                    @endif
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
                    <div class="info-row">
                        <span class="info-key">Billing</span>
                        @if($currentMonthPayment && $currentMonthPayment->status === 'paid')
                            <span style="display:inline-flex;align-items:center;gap:6px;">
                                <span class="badge badge-green" style="font-size:11px;">Paid ✓</span>
                                <span style="font-size:11px;color:var(--text-muted);">{{ $currentMonthPayment->paid_at?->format('d M Y') }}</span>
                            </span>
                        @elseif($currentMonthPayment && $currentMonthPayment->status === 'free')
                            <span class="badge" style="background:var(--blue-bg);color:var(--blue);border:1px solid rgba(96,165,250,0.2);font-size:11px;">FREE</span>
                        @elseif($currentMonthPayment && $currentMonthPayment->status === 'due')
                            <span style="display:inline-flex;align-items:center;gap:6px;">
                                <span class="badge badge-red" style="font-size:11px;">Due</span>
                                <span style="font-size:11px;color:var(--text-muted);">2,000 LKR</span>
                            </span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </div>
                    <div class="info-row">
                        <span class="info-key">APP_DEBUG</span>
                        <button wire:click="toggleDebug" style="display:flex;align-items:center;gap:6px;background:none;border:none;padding:0;cursor:pointer;">
                            <span style="display:inline-flex;align-items:center;width:36px;height:20px;border-radius:10px;padding:2px;transition:background .2s;background:{{ $debugMode ? '#A32D2D' : '#ccc' }};">
                                <span style="display:block;width:16px;height:16px;border-radius:50%;background:#fff;transition:transform .2s;transform:translateX({{ $debugMode ? '16px' : '0' }});"></span>
                            </span>
                            <span style="font-size:12px;font-weight:500;color:{{ $debugMode ? '#A32D2D' : 'var(--color-text-tertiary)' }};">
                                {{ $debugMode ? 'true' : 'false' }}
                            </span>
                        </button>
                    </div>
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
