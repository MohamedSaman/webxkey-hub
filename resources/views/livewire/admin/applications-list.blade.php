<div>
    <div class="topbar">
        <div class="page-title">Client Systems</div>
        <div style="display:flex;gap:8px;align-items:center">
            <input wire:model.live="search" type="text" placeholder="Search sites..." class="form-input" style="width:200px;padding:6px 10px;font-size:12px;">
            <a href="{{ route('deploy') }}" class="btn btn-primary">+ Deploy New App</a>
        </div>
    </div>

    <div class="content-area">

        @if($confirmDelete)
            <div style="background:#FCEBEB;border:0.5px solid #A32D2D;border-radius:var(--radius-md);padding:14px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:13px;color:#A32D2D;">Delete this application? The folder <strong>/var/www/{{ $deleteFolder }}</strong> will be permanently removed from the server.</span>
                <div style="display:flex;gap:8px;">
                    <button wire:click="deleteApp" class="btn btn-danger btn-sm">Yes, Remove</button>
                    <button wire:click="cancelDelete" class="btn btn-sm">Cancel</button>
                </div>
            </div>
        @endif

        <div style="background:var(--color-bg-primary);border:0.5px solid var(--color-border-t);border-radius:var(--radius-lg);overflow:hidden;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>Domain</th>
                        <th>Status</th>
                        <th>HTTP</th>
                        <th>SSL</th>
                        <th>Last Pull</th>
                        <th>Branch</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                        @php
                            $health = $app->healthChecks->first();
                            $sslDays = $health?->ssl_days_remaining;
                        @endphp
                        <tr>
                            <td style="font-weight:500;">{{ $app->name }}</td>
                            <td class="mono" style="font-size:11px;color:var(--color-text-secondary);">
                                <a href="https://{{ $app->domain }}" target="_blank" style="color:var(--color-text-secondary);text-decoration:none;">{{ $app->domain }} ↗</a>
                            </td>
                            <td>
                                @switch($app->status)
                                    @case('live')     <span class="badge badge-green">Live</span> @break
                                    @case('stopped')  <span class="badge badge-gray">Stopped</span> @break
                                    @case('deploying')<span class="badge badge-blue">Deploying</span> @break
                                    @case('error')    <span class="badge badge-red">Error</span> @break
                                @endswitch
                            </td>
                            <td style="font-size:11px;">
                                @if($health)
                                    <span style="color:{{ $health->is_up ? '#3B6D11' : '#A32D2D' }}">{{ $health->http_status }}</span>
                                    <span style="color:var(--color-text-tertiary)">· {{ $health->response_ms }}ms</span>
                                @else
                                    <span style="color:var(--color-text-tertiary)">—</span>
                                @endif
                            </td>
                            <td style="font-size:11px;{{ $sslDays && $sslDays < 30 ? 'color:#BA7517;font-weight:500;' : '' }}">
                                {{ $sslDays ? $sslDays.'d' : '—' }}
                                @if($sslDays && $sslDays < 30) ⚠ @endif
                            </td>
                            <td style="font-size:11px;color:var(--color-text-tertiary);">{{ $app->lastPullHuman() }}</td>
                            <td style="font-size:11px;font-family:var(--font-mono);">{{ $app->branch }}</td>
                            <td>
                                <div style="display:flex;gap:6px;">
                                    <button wire:click="quickPull({{ $app->id }})" class="act-btn" style="padding:4px 10px;" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="quickPull({{ $app->id }})">Pull</span>
                                        <span wire:loading wire:target="quickPull({{ $app->id }})">...</span>
                                    </button>
                                    <a href="{{ route('applications.show', $app) }}" class="act-btn" style="padding:4px 10px;text-decoration:none;text-align:center;">Details</a>
                                    <button wire:click="confirmDelete({{ $app->id }})" class="act-btn" style="padding:4px 10px;color:#A32D2D;">Remove</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:40px;color:var(--color-text-tertiary);">
                                No applications found. <a href="{{ route('deploy') }}" style="color:#185FA5;">Deploy your first app →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
