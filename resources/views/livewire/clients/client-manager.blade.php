<div>
    {{-- Topbar --}}
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button class="hamburger" onclick="toggleSidebar()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            <div class="page-title">Clients</div>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search clients..."
                   style="background:var(--bg-input);border:1px solid var(--border-strong);color:var(--text-primary);border-radius:var(--radius-sm);padding:6px 10px;font-size:12.5px;width:240px;">
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <button class="btn btn-primary btn-sm" wire:click="openCreate">
                + New Client
            </button>
        </div>
    </div>

    <div class="content-area">
        @if (session()->has('message'))
            <div class="panel" style="padding:10px 14px;margin-bottom:14px;background:var(--green-bg);border:1px solid var(--green-border);color:var(--green);font-size:12.5px;">
                {{ session('message') }}
            </div>
        @endif

        <div class="panel" style="overflow-x:auto;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid var(--border);">
                <span style="font-size:13px;font-weight:600;color:var(--text-white);">All Clients ({{ $clients->count() }})</span>
            </div>

            <table style="width:100%;border-collapse:collapse;min-width:800px;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:8px 12px;font-size:11.5px;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border);">Name</th>
                        <th style="text-align:left;padding:8px 12px;font-size:11.5px;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border);">Contact</th>
                        <th style="text-align:left;padding:8px 12px;font-size:11.5px;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border);">Phone</th>
                        <th style="text-align:left;padding:8px 12px;font-size:11.5px;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border);">Email</th>
                        <th style="text-align:center;padding:8px 12px;font-size:11.5px;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border);">Projects</th>
                        <th style="text-align:right;padding:8px 12px;font-size:11.5px;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:10px 12px;">
                                <div style="font-size:13px;font-weight:500;color:var(--text-white);">{{ $client->name }}</div>
                                @if($client->address)
                                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ Str::limit($client->address, 40) }}</div>
                                @endif
                            </td>
                            <td style="padding:10px 12px;font-size:12px;color:var(--text-secondary);">{{ $client->contact_person ?: '—' }}</td>
                            <td style="padding:10px 12px;font-size:12px;color:var(--text-secondary);">{{ $client->phone ?: '—' }}</td>
                            <td style="padding:10px 12px;font-size:12px;color:var(--text-secondary);">{{ $client->email ?: '—' }}</td>
                            <td style="padding:10px 12px;text-align:center;">
                                <span class="badge" style="background:var(--accent-subtle);color:var(--accent);border:1px solid var(--accent-subtle);font-size:11px;">
                                    {{ $client->projects_count }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;text-align:right;white-space:nowrap;">
                                <button class="btn btn-sm btn-primary" wire:click="openNewProject({{ $client->id }})" style="font-size:11px;">+ New Project</button>
                                <button class="btn btn-sm" wire:click="openEdit({{ $client->id }})" style="font-size:11px;">Edit</button>
                                <button class="btn btn-sm" wire:click="delete({{ $client->id }})"
                                        onclick="return confirm('Delete this client and all their projects?')"
                                        style="font-size:11px;color:var(--red);">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted);font-size:13px;">
                                No clients yet. <button wire:click="openCreate" style="background:none;border:none;color:var(--accent);cursor:pointer;font-size:13px;">Create your first client →</button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Client modal --}}
    @if($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-panel">
                <div class="modal-header">
                    <h3>{{ $editingId ? 'Edit Client' : 'New Client' }}</h3>
                    <button wire:click="$set('showModal', false)" class="modal-close">×</button>
                </div>
                <form wire:submit.prevent="save" class="modal-body">
                    <label class="form-label">Company / Client Name *</label>
                    <input type="text" wire:model="name" class="form-input" required>
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror

                    <label class="form-label">Address</label>
                    <textarea wire:model="address" rows="2" class="form-input"></textarea>

                    <label class="form-label">Contact Person</label>
                    <input type="text" wire:model="contact_person" class="form-input">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="form-label">Phone</label>
                            <input type="text" wire:model="phone" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Email</label>
                            <input type="email" wire:model="email" class="form-input">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">{{ $editingId ? 'Update' : 'Create' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- New Project modal --}}
    @if($showProjectModal)
        <div class="modal-overlay" wire:click.self="$set('showProjectModal', false)">
            <div class="modal-panel">
                <div class="modal-header">
                    <h3>New Project</h3>
                    <button wire:click="$set('showProjectModal', false)" class="modal-close">×</button>
                </div>
                <form wire:submit.prevent="createProject" class="modal-body">
                    <label class="form-label">Project Name *</label>
                    <input type="text" wire:model="projectName" class="form-input" placeholder="e.g. Stock Management & Billing System" required>
                    @error('projectName') <div class="form-error">{{ $message }}</div> @enderror

                    <label class="form-label">Project Type</label>
                    <select wire:model="projectType" class="form-input">
                        <option value="system">System / Software</option>
                        <option value="website">Website</option>
                    </select>

                    <label class="form-label">Description (optional)</label>
                    <textarea wire:model="projectDescription" rows="3" class="form-input"></textarea>

                    <div style="font-size:11.5px;color:var(--text-muted);margin-top:8px;">
                        An agreement code will be auto-generated and a draft proposal will be created.
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn" wire:click="$set('showProjectModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create &amp; Open Proposal →</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @include('livewire.partials.proposal-styles')
</div>
