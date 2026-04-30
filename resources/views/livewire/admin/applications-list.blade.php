<div>
    <div class="topbar">
        <div style="display:flex;align-items:center">
            <button class="hamburger" onclick="toggleSidebar()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            <div class="page-title">Client Systems</div>
        </div>
        <div class="topbar-actions" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:flex-end">
            <input wire:model.live="search" type="text" placeholder="Search..." class="form-input" style="width:140px;padding:6px 10px;font-size:12px;">
            <button wire:click="scanAndSyncAll" class="btn" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="scanAndSyncAll">⟳ Scan</span>
                <span wire:loading wire:target="scanAndSyncAll">...</span>
            </button>
            <button wire:click="openImport" class="btn">+ Register</button>
            <a href="{{ route('deploy') }}" class="btn btn-primary">+ Deploy</a>
        </div>
    </div>

    <div class="content-area">

        {{-- Import Existing App Panel --}}
        @if($showImport)
        <div style="background:var(--color-bg-primary);border:0.5px solid var(--color-border-t);border-radius:var(--radius-lg);padding:20px;margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <div style="font-size:13px;font-weight:500;">Register Existing App</div>
                <button wire:click="closeImport" style="background:none;border:none;font-size:18px;cursor:pointer;color:var(--color-text-tertiary);">×</button>
            </div>

            @if(!$importFolder)
                {{-- Step 1: pick folder --}}
                <div style="font-size:12px;color:var(--color-text-secondary);margin-bottom:10px;">Select a folder from <code>/var/www</code> to register:</div>
                @if(empty($unregistered))
                    <div style="font-size:12px;color:var(--color-text-tertiary);">All folders are already registered.</div>
                @else
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        @foreach($unregistered as $f)
                            <button wire:click="selectImportFolder('{{ $f }}')" class="btn" style="font-family:var(--font-mono);font-size:12px;">
                                /{{ $f }}
                            </button>
                        @endforeach
                    </div>
                @endif
            @else
                {{-- Step 2: confirm / edit details --}}
                <div style="font-size:12px;color:var(--color-text-tertiary);margin-bottom:12px;">
                    Reading from <code>/var/www/{{ $importFolder }}/.env</code> — edit any field then click Register.
                </div>
                <div class="import-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:10px;margin-bottom:12px;">
                    <div class="form-group">
                        <label class="form-label">Folder</label>
                        <input class="form-input" type="text" value="{{ $importFolder }}" disabled style="opacity:0.6;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">App Name</label>
                        <input wire:model="importName" class="form-input" type="text">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Domain</label>
                        <input wire:model="importDomain" class="form-input" type="text">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Database</label>
                        <input wire:model="importDbName" class="form-input" type="text">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Git Repo</label>
                        <input wire:model="importGitRepo" class="form-input" type="text" placeholder="https://github.com/...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Branch</label>
                        <input wire:model="importBranch" class="form-input" type="text">
                    </div>
                </div>
                <div style="display:flex;gap:8px;">
                    <button wire:click="importApp" class="btn btn-primary">Register App</button>
                    <button wire:click="$set('importFolder', '')" class="btn">← Back</button>
                </div>
            @endif
        </div>
        @endif

        @if($confirmDeleteId)
            <div style="background:#FCEBEB;border:0.5px solid #A32D2D;border-radius:var(--radius-md);padding:14px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:13px;color:#A32D2D;">Delete this application? The folder <strong>/var/www/{{ $deleteFolder }}</strong> will be permanently removed from the server.</span>
                <div style="display:flex;gap:8px;">
                    <button wire:click="deleteApp" class="btn btn-danger btn-sm">Yes, Remove</button>
                    <button wire:click="cancelDelete" class="btn btn-sm">Cancel</button>
                </div>
            </div>
        @endif

        <div class="table-responsive" style="background:var(--color-bg-primary);border:0.5px solid var(--color-border-t);border-radius:var(--radius-lg);overflow:hidden;">
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
