<div>
    <div class="topbar">
        <div class="page-title">Deploy New App</div>
        <a href="{{ route('dashboard') }}" class="btn">← Back</a>
    </div>

    <div class="content-area">
        <div class="wizard-wrap">

            {{-- Step indicators --}}
            <div class="wizard-steps">
                @foreach($stepNames as $i => $name)
                    <div class="wstep {{ $step === $i ? 'active' : ($step > $i ? 'done' : '') }}">
                        <span class="wstep-num">
                            @if($step > $i) ✓ @else {{ $i + 1 }} @endif
                        </span>
                        {{ $name }}
                    </div>
                @endforeach
            </div>

            {{-- Progress bar --}}
            <div class="progress-bar-wrap" style="margin:0">
                <div class="progress-bar" style="width:{{ round(($step / 7) * 100) }}%"></div>
            </div>

            <div class="wizard-body">

                {{-- Error/success alert --}}
                @if($stepFailed)
                    <div style="background:#FCEBEB;border:0.5px solid #A32D2D;border-radius:var(--radius-md);padding:10px 14px;margin-bottom:16px;font-size:12px;color:#A32D2D;">
                        ✗ This step failed. Check the output below and try again.
                    </div>
                @endif
                @if($stepDone && !$stepFailed && $step < 7)
                    <div style="background:#EAF3DE;border:0.5px solid #3B6D11;border-radius:var(--radius-md);padding:10px 14px;margin-bottom:16px;font-size:12px;color:#3B6D11;">
                        ✓ Step complete — click Continue to proceed.
                    </div>
                @endif

                {{-- ── STEP 0: Git Clone ── --}}
                @if($step === 0)
                    <div style="font-size:13px;font-weight:500;margin-bottom:16px">Step 1 — Clone repository</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div class="form-group" style="grid-column:1/-1">
                            <label class="form-label">GitHub Repository URL</label>
                            <input wire:model="gitUrl" class="form-input" type="url" placeholder="https://github.com/webxkey/clinic-system.git">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Folder Name <span style="color:var(--color-text-tertiary);font-weight:400">(becomes /var/www/[name])</span></label>
                            <input wire:model="folderName" class="form-input" type="text" placeholder="clinic-system">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Branch</label>
                            <input wire:model="branch" class="form-input" type="text" placeholder="main">
                        </div>
                    </div>
                    @if(@$errors->any())
                        <div style="color:#A32D2D;font-size:12px;margin-bottom:8px;">{{ $errors->first() }}</div>
                    @endif
                    <button wire:click="runClone" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="runClone">Clone Repository</span>
                        <span wire:loading wire:target="runClone"><span class="spinner"></span> Cloning...</span>
                    </button>
                    @if($terminalOutput)
                        <div class="terminal" style="margin-top:12px;max-height:200px;overflow-y:auto;">{{ $terminalOutput }}</div>
                    @endif
                @endif

                {{-- ── STEP 1: Permissions ── --}}
                @if($step === 1)
                    <div style="font-size:13px;font-weight:500;margin-bottom:8px">Step 2 — Set file permissions</div>
                    <p style="font-size:12px;color:var(--color-text-secondary);margin-bottom:12px">Sets correct ownership and write permissions on storage and bootstrap/cache. This prevents 500 errors.</p>
                    <button wire:click="runPermissions" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="runPermissions">Run Permissions</span>
                        <span wire:loading wire:target="runPermissions"><span class="spinner"></span> Running...</span>
                    </button>
                    @if($terminalOutput)
                        <div class="terminal" style="margin-top:12px;max-height:200px;overflow-y:auto;">{{ $terminalOutput }}</div>
                    @endif
                @endif

                {{-- ── STEP 2: Packages ── --}}
                @if($step === 2)
                    <div style="font-size:13px;font-weight:500;margin-bottom:8px">Step 3 — Install packages</div>
                    <p style="font-size:12px;color:var(--color-text-secondary);margin-bottom:12px">Runs <code>composer update</code>, <code>npm install</code>, and <code>npm run build</code>. This may take a few minutes.</p>
                    <button wire:click="runPackages" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="runPackages">Install Packages</span>
                        <span wire:loading wire:target="runPackages"><span class="spinner"></span> Installing...</span>
                    </button>
                    @if($terminalOutput)
                        <div class="terminal" style="margin-top:12px;max-height:220px;overflow-y:auto;" wire:poll.500ms="$refresh">{{ $terminalOutput }}</div>
                    @endif
                @endif

                {{-- ── STEP 3: Env Setup ── --}}
                @if($step === 3)
                    <div style="font-size:13px;font-weight:500;margin-bottom:16px">Step 4 — Environment configuration</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div class="form-group">
                            <label class="form-label">App Name</label>
                            <input wire:model="appName" class="form-input" type="text" placeholder="Clinic System">
                        </div>
                        <div class="form-group">
                            <label class="form-label">App URL</label>
                            <input wire:model="appUrl" class="form-input" type="url" placeholder="https://clinic.webxkey.store">
                        </div>
                        <div class="form-group">
                            <label class="form-label">DB Database</label>
                            <input wire:model="dbName" class="form-input" type="text" placeholder="clinic_db">
                        </div>
                        <div class="form-group">
                            <label class="form-label">DB Username</label>
                            <input wire:model="dbUser" class="form-input" type="text">
                        </div>
                        <div class="form-group">
                            <label class="form-label">DB Password</label>
                            <input wire:model="dbPassword" class="form-input" type="password" placeholder="leave blank if none">
                        </div>
                        <div class="form-group">
                            <label class="form-label">App Environment</label>
                            <input wire:model="appEnv" class="form-input" type="text">
                        </div>
                    </div>
                    <button wire:click="runEnvSetup" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="runEnvSetup">Save .env & Generate Key</span>
                        <span wire:loading wire:target="runEnvSetup"><span class="spinner"></span> Saving...</span>
                    </button>
                @endif

                {{-- ── STEP 4: Database ── --}}
                @if($step === 4)
                    <div style="font-size:13px;font-weight:500;margin-bottom:12px">Step 5 — Create database</div>
                    <div class="form-group">
                        <label class="form-label">Database name to create</label>
                        <input wire:model="dbName" class="form-input" type="text" placeholder="clinic_db">
                        <div class="form-hint">Will run: <code>CREATE DATABASE IF NOT EXISTS `{{ $dbName }}`;</code></div>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <button wire:click="runDatabaseCreate" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="runDatabaseCreate">Create Database</span>
                            <span wire:loading wire:target="runDatabaseCreate"><span class="spinner"></span> Creating...</span>
                        </button>
                        @if($stepFailed)
                            <button wire:click="skipDatabase" class="btn" style="color:#BA7517;border-color:#BA7517;">
                                Ignore & Continue →
                            </button>
                        @endif
                    </div>
                    @if($terminalOutput)
                        <div class="terminal" style="margin-top:12px;max-height:200px;overflow-y:auto;">{{ $terminalOutput }}</div>
                    @endif
                    @if($stepDone && !$stepFailed)
                        <div class="terminal" style="margin-top:12px;min-height:40px;">
                            <span style="color:#639922">✓ Database '{{ $dbName }}' created successfully</span>
                        </div>
                    @endif
                @endif

                {{-- ── STEP 5: Nginx ── --}}
                @if($step === 5)
                    <div style="font-size:13px;font-weight:500;margin-bottom:12px">Step 6 — Nginx configuration</div>
                    <div class="form-group">
                        <label class="form-label">Domain name</label>
                        <div style="display:flex;gap:8px;">
                            <input wire:model.live="domainName" class="form-input" type="text" placeholder="clinic.webxkey.store">
                            <button wire:click="previewNginx" class="btn" style="flex-shrink:0">Preview</button>
                        </div>
                        <div class="form-hint">Config saved to /etc/nginx/sites-available/{{ $domainName }}</div>
                    </div>
                    @if($nginxPreview)
                        <div class="terminal" style="min-height:160px;font-size:11px;overflow-y:auto;margin-bottom:12px;">{{ $nginxPreview }}</div>
                    @endif
                    <button wire:click="runNginx" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="runNginx">Write Config & Reload Nginx</span>
                        <span wire:loading wire:target="runNginx"><span class="spinner"></span> Configuring...</span>
                    </button>
                @endif

                {{-- ── STEP 6: Migrate ── --}}
                @if($step === 6)
                    <div style="font-size:13px;font-weight:500;margin-bottom:12px">Step 7 — Database migrations</div>
                    <div style="display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
                        <button wire:click="runMigrate" class="btn btn-primary" wire:loading.attr="disabled" @if($migrationDone) disabled @endif>
                            <span wire:loading.remove wire:target="runMigrate">Run Migrations</span>
                            <span wire:loading wire:target="runMigrate"><span class="spinner"></span> Migrating...</span>
                        </button>
                        <button wire:click="runMigrateFresh" class="btn" wire:loading.attr="disabled" style="color:#BA7517;border-color:#BA7517;">
                            <span wire:loading.remove wire:target="runMigrateFresh">Fresh (drop &amp; re-run)</span>
                            <span wire:loading wire:target="runMigrateFresh"><span class="spinner"></span> Running...</span>
                        </button>
                        <button wire:click="runSeeders" class="btn" wire:loading.attr="disabled" @if(!$migrationDone) disabled style="opacity:0.4" @endif>
                            <span wire:loading.remove wire:target="runSeeders">Run Seeders (optional)</span>
                            <span wire:loading wire:target="runSeeders"><span class="spinner"></span> Seeding...</span>
                        </button>
                        <button wire:click="skipMigrate" class="btn" style="color:#BA7517;border-color:#BA7517;">
                            Mark Done & Continue →
                        </button>
                    </div>
                    @if($terminalOutput)
                        <div class="terminal" style="min-height:80px;max-height:220px;overflow-y:auto;" wire:poll.500ms="$refresh">{{ $terminalOutput }}</div>
                    @endif
                    @if($migrationDone && !$stepRunning)
                        <div style="color:#3B6D11;font-size:12px;margin-top:10px;">✓ Migrations complete. You can run seeders or continue.</div>
                    @endif
                @endif

                {{-- ── STEP 7: Go Live ── --}}
                @if($step === 7)
                    @if(!$siteIsLive)
                        <div style="font-size:13px;font-weight:500;margin-bottom:12px">Step 8 — SSL Certificate & Go Live</div>
                        <p style="font-size:12px;color:var(--color-text-secondary);margin-bottom:16px;">
                            Install Let's Encrypt SSL for <strong>{{ $domainName }}</strong>. Make sure DNS is already pointing to 57.159.27.225.
                        </p>
                        <div style="display:flex;gap:10px;">
                            <button wire:click="runSSL" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="runSSL">Install SSL Certificate</span>
                                <span wire:loading wire:target="runSSL"><span class="spinner"></span> Installing SSL...</span>
                            </button>
                            <button wire:click="skipSSLGoLive" class="btn">Skip SSL (HTTP only)</button>
                        </div>
                        @if($terminalOutput)
                            <div class="terminal" style="margin-top:12px;max-height:200px;overflow-y:auto;" wire:poll.500ms="$refresh">{{ $terminalOutput }}</div>
                        @endif
                    @else
                        {{-- Success screen --}}
                        <div style="text-align:center;padding:20px 0;">
                            <div style="font-size:40px;margin-bottom:12px;">✓</div>
                            <div style="font-size:18px;font-weight:500;color:#3B6D11;margin-bottom:8px;">Site is Live!</div>
                            <div style="font-size:13px;color:var(--color-text-secondary);margin-bottom:20px;">
                                {{ $domainName }} has been deployed and is now active.
                            </div>
                            <div style="display:flex;gap:10px;justify-content:center;margin-bottom:20px;">
                                <a href="https://{{ $domainName }}" target="_blank" class="btn btn-primary">Open Site ↗</a>
                                @if($applicationId)
                                    <a href="{{ route('applications.show', $applicationId) }}" class="btn">View Details</a>
                                @endif
                                <button wire:click="resetWizard" class="btn">Deploy Another</button>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;max-width:400px;margin:0 auto;text-align:left;background:var(--color-bg-secondary);border-radius:var(--radius-md);padding:14px;font-size:11px;">
                                <div><span style="color:var(--color-text-tertiary)">Domain: </span>{{ $domainName }}</div>
                                <div><span style="color:var(--color-text-tertiary)">Folder: </span>/var/www/{{ $folderName }}</div>
                                <div><span style="color:var(--color-text-tertiary)">Database: </span>{{ $dbName }}</div>
                                <div><span style="color:var(--color-text-tertiary)">Status: </span><span style="color:#3B6D11">Live</span></div>
                            </div>
                        </div>
                    @endif
                @endif

            </div>{{-- /wizard-body --}}

            {{-- Footer navigation --}}
            @if(!$siteIsLive)
            <div class="wizard-footer">
                <button class="btn" wire:click="prevStep" @if($step === 0) disabled style="opacity:0.4" @endif>← Back</button>
                <span style="font-size:11px;color:var(--color-text-tertiary)">Step {{ $step + 1 }} of 8</span>
                @if($step < 7)
                    <button class="btn btn-primary" wire:click="nextStep" @if(!$stepDone) disabled style="opacity:0.4" @endif>
                        Continue →
                    </button>
                @endif
            </div>
            @endif

        </div>
    </div>
</div>
