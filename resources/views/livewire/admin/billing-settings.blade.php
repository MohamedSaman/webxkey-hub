<div>
    <div class="topbar">
        <div style="display:flex;align-items:center">
            <button class="hamburger" onclick="toggleSidebar()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            <div class="page-title">Billing Plans & Settings</div>
        </div>
        <a href="{{ route('billing') }}" class="btn">← Back to Billing</a>
    </div>

    <div class="content-area">
        @if (session()->has('message'))
            <div class="alert-success" style="margin-bottom: 20px;">
                {{ session('message') }}
            </div>
        @endif

        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 24px;">
            {{-- Plans List --}}
            <div class="panel">
                <div class="panel-title">Active Hosting Plans</div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Plan Name</th>
                                <th>Monthly Price (LKR)</th>
                                <th>Managed Apps</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plans as $plan)
                                <tr>
                                    <td style="font-weight: 500; color: var(--text-white);">{{ $plan->name }}</td>
                                    <td>
                                        @if($plan->price == 0)
                                            <span class="badge badge-blue">FREE</span>
                                        @else
                                            LKR {{ number_format($plan->price) }}
                                        @endif
                                    </td>
                                    <td style="color: var(--text-muted);">{{ $plan->applications_count ?? $plan->applications()->count() }}</td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <button wire:click="edit({{ $plan->id }})" class="act-btn" style="padding: 4px 10px;">Edit</button>
                                            <button wire:click="delete({{ $plan->id }})" class="act-btn" style="padding: 4px 10px; color: var(--red);" onclick="confirm('Are you sure? This will set managed apps to No Plan.') || event.stopImmediatePropagation()">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Form --}}
            <div class="panel">
                <div class="panel-title">{{ $editingId ? 'Edit' : 'Create New' }} Plan</div>
                <form wire:submit.prevent="save">
                    <div class="form-group">
                        <label class="form-label">Plan Name</label>
                        <input type="text" wire:model="name" class="form-input" placeholder="e.g. Enterprise Hosting">
                        @error('name') <span style="color: var(--red); font-size: 11px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Monthly Rate (LKR)</label>
                        <input type="number" wire:model="price" class="form-input" placeholder="0 for Free">
                        @error('price') <span style="color: var(--red); font-size: 11px;">{{ $message }}</span> @enderror
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            {{ $editingId ? 'Update Plan' : 'Add Hosting Plan' }}
                        </button>
                        @if($editingId)
                            <button type="button" wire:click="resetInput" class="btn" style="flex: 1;">Cancel</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
