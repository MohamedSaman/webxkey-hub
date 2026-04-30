<div>
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button class="hamburger" onclick="toggleSidebar()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            <div class="page-title">Projects</div>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search projects..."
                   style="background:var(--bg-input);border:1px solid var(--border-strong);color:var(--text-primary);border-radius:var(--radius-sm);padding:6px 10px;font-size:12.5px;width:240px;">
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <button class="btn btn-primary btn-sm" wire:click="openNewProject">+ New Project</button>
        </div>
    </div>

    <div class="content-area">
        {{-- Pipeline columns --}}
        <div class="pipeline-grid">
            @php
                $columnsMeta = [
                    'pending'   => ['label' => 'Pending',   'color' => 'var(--yellow)'],
                    'approved'  => ['label' => 'Approved',  'color' => 'var(--blue)'],
                    'ongoing'   => ['label' => 'Ongoing',   'color' => 'var(--accent)'],
                    'completed' => ['label' => 'Completed', 'color' => 'var(--green)'],
                    'cancelled' => ['label' => 'Cancelled', 'color' => 'var(--red)'],
                ];
            @endphp
            @foreach($columns as $key => $items)
                <div class="pipeline-col">
                    <div class="pipeline-col-head" style="color:{{ $columnsMeta[$key]['color'] }};">
                        <span>{{ $columnsMeta[$key]['label'] }}</span>
                        <span style="color:var(--text-muted);">{{ $items->count() }}</span>
                    </div>
                    @forelse($items as $project)
                        <div class="pipeline-card">
                            <div style="font-size:12.5px;font-weight:600;color:var(--text-white);margin-bottom:4px;">
                                {{ $project->name }}
                            </div>
                            <div style="font-size:11px;color:var(--text-secondary);margin-bottom:6px;">
                                {{ $project->client?->name }}
                            </div>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px;">
                                <span class="badge badge-gray" style="font-size:10px;">{{ strtoupper($project->type) }}</span>
                                @if($project->agreement_code)
                                    <span class="badge badge-blue" style="font-size:10px;">{{ $project->agreement_code }}</span>
                                @endif
                            </div>
                            @php
                                $proposal = $project->latestProposal;
                                $invoice  = $project->invoices->first();
                                $total    = $proposal ? $proposal->grandTotal() : 0;
                            @endphp
                            @if($total > 0)
                                <div style="font-size:11px;color:var(--text-muted);margin-bottom:8px;">
                                    Total: <span style="color:var(--text-primary);font-weight:500;">LKR {{ number_format($total, 2) }}</span>
                                </div>
                            @endif
                            <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                @if($proposal)
                                    <a href="{{ route('proposals.show', $proposal) }}" class="btn btn-sm" style="font-size:10.5px;padding:3px 8px;">Proposal</a>
                                @endif
                                @if($invoice)
                                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm" style="font-size:10.5px;padding:3px 8px;">Invoice</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="font-size:11px;color:var(--text-muted);text-align:center;padding:20px 0;">
                            Empty
                        </div>
                    @endforelse
                </div>
            @endforeach
        </div>
    </div>

    @if($showNewProject)
        <div class="modal-overlay" wire:click.self="$set('showNewProject', false)">
            <div class="modal-panel">
                <div class="modal-header">
                    <h3>New Project — Step {{ $step }} of 2</h3>
                    <button wire:click="$set('showNewProject', false)" class="modal-close">×</button>
                </div>

                <div class="wizard-steps">
                    <div class="wizard-step {{ $step >= 1 ? 'active' : '' }} {{ $step > 1 ? 'completed' : '' }}">1. Client</div>
                    <div class="wizard-step {{ $step >= 2 ? 'active' : '' }}">2. Project Details</div>
                </div>

                <div class="modal-body">
                    @if($step === 1)
                        <div style="display:flex;gap:8px;">
                            <button type="button"
                                    wire:click="$set('clientMode', 'existing')"
                                    class="btn {{ $clientMode === 'existing' ? 'btn-primary' : '' }}"
                                    style="flex:1;">Existing Client</button>
                            <button type="button"
                                    wire:click="$set('clientMode', 'new')"
                                    class="btn {{ $clientMode === 'new' ? 'btn-primary' : '' }}"
                                    style="flex:1;">New Client</button>
                        </div>

                        @if($clientMode === 'existing')
                            <label class="form-label">Select Client *</label>
                            <select wire:model="clientId" class="form-input">
                                <option value="">— Select —</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('clientId') <div class="form-error">{{ $message }}</div> @enderror
                        @else
                            <label class="form-label">Client Name *</label>
                            <input type="text" wire:model="newClientName" class="form-input">
                            @error('newClientName') <div class="form-error">{{ $message }}</div> @enderror

                            <label class="form-label">Address</label>
                            <textarea wire:model="newClientAddress" rows="2" class="form-input"></textarea>

                            <div class="field-row">
                                <div>
                                    <label class="form-label">Phone</label>
                                    <input type="text" wire:model="newClientPhone" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Email</label>
                                    <input type="email" wire:model="newClientEmail" class="form-input">
                                </div>
                            </div>

                            <label class="form-label">Contact Person</label>
                            <input type="text" wire:model="newClientContact" class="form-input">
                        @endif
                    @endif

                    @if($step === 2)
                        <label class="form-label">Project Name *</label>
                        <input type="text" wire:model="projectName" class="form-input"
                               placeholder="e.g. Stock Management & Billing System">
                        @error('projectName') <div class="form-error">{{ $message }}</div> @enderror

                        <div class="field-row">
                            <div>
                                <label class="form-label">Type</label>
                                <select wire:model="projectType" class="form-input">
                                    <option value="system">System / Software</option>
                                    <option value="website">Website</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Agreement Code</label>
                                <input type="text" wire:model="agreementCode" class="form-input">
                            </div>
                        </div>

                        <label class="form-label">Description (optional)</label>
                        <textarea wire:model="projectDescription" rows="3" class="form-input"></textarea>

                        <div style="font-size:11.5px;color:var(--text-muted);margin-top:4px;">
                            A draft proposal will be created automatically — you'll be redirected to fill it in.
                        </div>
                    @endif

                    <div class="modal-footer">
                        @if($step > 1)
                            <button type="button" class="btn" wire:click="prevStep">← Back</button>
                        @else
                            <button type="button" class="btn" wire:click="$set('showNewProject', false)">Cancel</button>
                        @endif

                        @if($step === 1)
                            <button type="button" class="btn btn-primary" wire:click="nextStep">Next →</button>
                        @else
                            <button type="button" class="btn btn-primary" wire:click="createProject">Create &amp; Open Proposal</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.partials.proposal-styles')
</div>
