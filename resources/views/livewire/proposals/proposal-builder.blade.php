<div>
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button class="hamburger" onclick="toggleSidebar()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            <a href="{{ route('projects') }}" class="btn btn-sm">← Projects</a>
            <div class="page-title">{{ $proposal->subject ?: 'Proposal' }}</div>
            <span class="badge {{ $proposal->status === 'approved' ? 'badge-green' : ($proposal->status === 'sent' ? 'badge-blue' : 'badge-gray') }}">
                {{ strtoupper($proposal->status) }}
            </span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <a href="{{ route('proposals.preview', $proposal) }}" target="_blank" class="btn btn-sm">Preview / Print</a>
        </div>
    </div>

    <div class="content-area">
        @if (session()->has('message'))
            <div class="panel" style="padding:10px 14px;margin-bottom:14px;background:var(--green-bg);border:1px solid var(--green-border);color:var(--green);font-size:12.5px;">
                {{ session('message') }}
            </div>
        @endif

        <div class="builder-grid">
            {{-- Sidebar steps --}}
            <div class="builder-sidebar">
                <button type="button" wire:click="setStep(1)"
                        class="builder-step {{ $step === 1 ? 'active' : '' }} {{ $step > 1 ? 'completed' : '' }}">
                    <span class="step-num">1</span> Client &amp; Subject
                </button>
                <button type="button" wire:click="setStep(2)"
                        class="builder-step {{ $step === 2 ? 'active' : '' }} {{ $step > 2 ? 'completed' : '' }}">
                    <span class="step-num">2</span> Scope of Work
                </button>
                <button type="button" wire:click="setStep(3)"
                        class="builder-step {{ $step === 3 ? 'active' : '' }} {{ $step > 3 ? 'completed' : '' }}">
                    <span class="step-num">3</span> Hosting &amp; Pricing
                </button>
                <button type="button" wire:click="setStep(4)"
                        class="builder-step {{ $step === 4 ? 'active' : '' }} {{ $step > 4 ? 'completed' : '' }}">
                    <span class="step-num">4</span> Payment Quotation
                </button>
                <button type="button" wire:click="setStep(5)"
                        class="builder-step {{ $step === 5 ? 'active' : '' }}">
                    <span class="step-num">5</span> Preview &amp; Status
                </button>

                <div style="margin-top:20px;padding-top:14px;border-top:1px solid var(--border);font-size:11px;color:var(--text-muted);">
                    <div style="margin-bottom:4px;"><strong style="color:var(--text-secondary);">Client:</strong> {{ $proposal->project->client?->name }}</div>
                    <div><strong style="color:var(--text-secondary);">Code:</strong> {{ $proposal->project->agreement_code }}</div>
                </div>
            </div>

            {{-- Main step content --}}
            <div class="builder-content">
                {{-- Step 1 --}}
                @if($step === 1)
                    <h4>Step 1 — Client &amp; Subject</h4>

                    <div class="builder-card">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Client</div>
                        <div style="font-size:14px;font-weight:600;color:var(--text-white);">{{ $proposal->project->client?->name }}</div>
                        <div style="font-size:12px;color:var(--text-secondary);margin-top:4px;white-space:pre-wrap;">{{ $proposal->project->client?->address }}</div>
                    </div>

                    <div class="field-row">
                        <div>
                            <label class="form-label">Date *</label>
                            <input type="date" wire:model="date" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Agreement Code</label>
                            <input type="text" wire:model="agreementCode" class="form-input">
                        </div>
                    </div>

                    <label class="form-label" style="margin-top:12px;">Subject *</label>
                    <input type="text" wire:model="subject" class="form-input"
                           placeholder="e.g. Agreement for Stock Management & Billing System">

                    <label class="form-label" style="margin-top:12px;">Intro Paragraph</label>
                    <textarea wire:model="intro_text" rows="4" class="form-input"></textarea>

                {{-- Step 2 --}}
                @elseif($step === 2)
                    <h4>Step 2 — Scope of Work</h4>

                    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
                        <button type="button" class="btn btn-sm" wire:click="loadSystemTemplate"
                                onclick="return confirm('Replace existing modules with the System template?')">
                            Load System Template
                        </button>
                        <button type="button" class="btn btn-sm" wire:click="loadWebsiteTemplate"
                                onclick="return confirm('Replace existing modules with the Website template?')">
                            Load Website Template
                        </button>
                    </div>

                    @forelse($proposal->modules as $idx => $module)
                        <div class="builder-card" wire:key="module-{{ $module->id }}">
                            <div class="builder-card-head">
                                <div style="display:flex;align-items:center;gap:8px;flex:1;">
                                    <span class="badge badge-blue" style="font-size:10px;">Module {{ $idx + 1 }}</span>
                                    <input type="text"
                                           value="{{ $module->title }}"
                                           wire:change="updateModule({{ $module->id }}, 'title', $event.target.value)"
                                           class="form-input" style="font-weight:600;">
                                </div>
                                <button type="button" class="icon-btn"
                                        wire:click="deleteModule({{ $module->id }})"
                                        onclick="return confirm('Delete this module?')">×</button>
                            </div>

                            <label class="form-label">Description</label>
                            <textarea
                                wire:change="updateModule({{ $module->id }}, 'description', $event.target.value)"
                                rows="2" class="form-input">{{ $module->description }}</textarea>

                            <label class="form-label" style="margin-top:10px;">Features</label>
                            @foreach($module->features as $feature)
                                <div class="feature-row" wire:key="feature-{{ $feature->id }}">
                                    <input type="text" value="{{ $feature->feature_text }}"
                                           wire:change="updateFeature({{ $feature->id }}, $event.target.value)"
                                           class="form-input">
                                    <button type="button" class="icon-btn"
                                            wire:click="deleteFeature({{ $feature->id }})">×</button>
                                </div>
                            @endforeach
                            <button type="button" class="btn btn-sm btn-ghost"
                                    wire:click="addFeature({{ $module->id }})"
                                    style="margin-top:6px;">+ Add Feature</button>
                        </div>
                    @empty
                        <div style="text-align:center;padding:30px;color:var(--text-muted);font-size:13px;">
                            No modules yet. Use a template above or add your first module below.
                        </div>
                    @endforelse

                    <button type="button" class="btn btn-primary" wire:click="addModule">+ Add Module</button>

                {{-- Step 3 --}}
                @elseif($step === 3)
                    <h4>Step 3 — Hosting &amp; Pricing</h4>

                    <div class="builder-card">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                            <input type="checkbox" wire:model="hosting_enabled" style="width:16px;height:16px;">
                            <span style="font-size:13px;font-weight:500;color:var(--text-white);">Include Hosting in this Proposal</span>
                        </label>

                        @if($hosting_enabled)
                            <div class="field-row" style="margin-top:14px;">
                                <div>
                                    <label class="form-label">Hosting Price / month (LKR)</label>
                                    <input type="number" step="0.01" wire:model="hosting_price" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Months</label>
                                    <input type="number" wire:model="hosting_months" class="form-input">
                                </div>
                            </div>
                            <div style="margin-top:8px;font-size:11.5px;color:var(--text-muted);">
                                Total Hosting: <strong style="color:var(--text-primary);">LKR {{ number_format($hosting_price * $hosting_months, 2) }}</strong>
                            </div>
                        @endif
                    </div>

                    <h5>Payment Breakdown (%)</h5>
                    <div class="field-row-3">
                        <div>
                            <label class="form-label">Advance %</label>
                            <input type="number" wire:model="payment_advance_pct" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Middle %</label>
                            <input type="number" wire:model="payment_middle_pct" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Final %</label>
                            <input type="number" wire:model="payment_final_pct" class="form-input">
                        </div>
                    </div>
                    @php $sumPct = $payment_advance_pct + $payment_middle_pct + $payment_final_pct; @endphp
                    <div style="margin-top:6px;font-size:11.5px;color:{{ $sumPct === 100 ? 'var(--green)' : 'var(--red)' }};">
                        Sum: {{ $sumPct }}% {{ $sumPct === 100 ? '(OK)' : '(must equal 100%)' }}
                    </div>

                    <h5>Service Rates</h5>
                    <div class="field-row">
                        <div>
                            <label class="form-label">Monthly Support Fee (LKR)</label>
                            <input type="number" step="0.01" wire:model="monthly_support_fee" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Additional Feature Rate (LKR)</label>
                            <input type="number" step="0.01" wire:model="additional_feature_rate" class="form-input">
                        </div>
                    </div>

                {{-- Step 4 --}}
                @elseif($step === 4)
                    <h4>Step 4 — Payment Quotation</h4>

                    <div class="field-row">
                        <div>
                            <label class="form-label">Total System Development Cost (LKR)</label>
                            <input type="number" step="0.01" wire:model.live="total_system_cost" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Discount (LKR)</label>
                            <input type="number" step="0.01" wire:model.live="discount" class="form-input">
                        </div>
                    </div>

                    <h5>Quotation Line Items</h5>
                    <div class="builder-card" style="padding:0;overflow:hidden;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:var(--bg-elevated);">
                                    <th style="text-align:left;padding:8px 12px;font-size:11px;color:var(--text-muted);">Description</th>
                                    <th style="text-align:right;padding:8px 12px;font-size:11px;color:var(--text-muted);width:160px;">Amount (LKR)</th>
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($proposal->quotationItems as $qi)
                                    <tr wire:key="qi-{{ $qi->id }}" style="border-top:1px solid var(--border);">
                                        <td style="padding:6px 12px;">
                                            <input type="text" value="{{ $qi->description }}"
                                                   wire:change="updateQuotationItem({{ $qi->id }}, 'description', $event.target.value)"
                                                   class="form-input">
                                        </td>
                                        <td style="padding:6px 12px;text-align:right;">
                                            <input type="number" step="0.01" value="{{ $qi->amount }}"
                                                   wire:change="updateQuotationItem({{ $qi->id }}, 'amount', $event.target.value)"
                                                   class="form-input" style="text-align:right;">
                                        </td>
                                        <td style="padding:6px 8px;">
                                            <button type="button" class="icon-btn"
                                                    wire:click="deleteQuotationItem({{ $qi->id }})">×</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" style="padding:20px;text-align:center;color:var(--text-muted);font-size:12px;">No line items. Click "Add Item" below.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-ghost" wire:click="addQuotationItem" style="margin-top:6px;">+ Add Item</button>

                    @php
                        $hostingCost = $hosting_enabled ? ($hosting_price * $hosting_months) : 0;
                        $grandTotal  = $total_system_cost + $hostingCost - $discount;
                        $advance     = $grandTotal * $payment_advance_pct / 100;
                    @endphp
                    <div class="builder-card" style="margin-top:14px;background:var(--bg-elevated);">
                        <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:4px;">
                            <span style="color:var(--text-secondary);">System Cost</span>
                            <strong style="color:var(--text-primary);">LKR {{ number_format($total_system_cost, 2) }}</strong>
                        </div>
                        @if($hosting_enabled)
                            <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:4px;">
                                <span style="color:var(--text-secondary);">Hosting ({{ $hosting_months }} months)</span>
                                <strong style="color:var(--text-primary);">LKR {{ number_format($hostingCost, 2) }}</strong>
                            </div>
                        @endif
                        @if($discount > 0)
                            <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:4px;color:var(--red);">
                                <span>Discount</span>
                                <strong>− LKR {{ number_format($discount, 2) }}</strong>
                            </div>
                        @endif
                        <div style="display:flex;justify-content:space-between;font-size:14px;padding-top:8px;border-top:1px solid var(--border);">
                            <strong style="color:var(--text-white);">Grand Total</strong>
                            <strong style="color:var(--accent);">LKR {{ number_format($grandTotal, 2) }}</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:12px;margin-top:6px;color:var(--text-muted);">
                            <span>Advance ({{ $payment_advance_pct }}%)</span>
                            <strong>LKR {{ number_format($advance, 2) }}</strong>
                        </div>
                    </div>

                {{-- Step 5 --}}
                @elseif($step === 5)
                    <h4>Step 5 — Preview &amp; Status</h4>

                    <div class="builder-card">
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:6px;">CURRENT STATUS</div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <button type="button" wire:click="changeStatus('draft')"
                                    class="btn btn-sm {{ $proposal->status === 'draft' ? 'btn-primary' : '' }}">Draft</button>
                            <button type="button" wire:click="changeStatus('sent')"
                                    class="btn btn-sm {{ $proposal->status === 'sent' ? 'btn-primary' : '' }}">Sent</button>
                            <button type="button" wire:click="changeStatus('approved')"
                                    class="btn btn-sm {{ $proposal->status === 'approved' ? 'btn-primary' : '' }}"
                                    style="{{ $proposal->status === 'approved' ? 'background:var(--green);border-color:var(--green);' : '' }}">
                                Approved
                            </button>
                        </div>
                    </div>

                    <label class="form-label">Internal Notes</label>
                    <textarea wire:model="notes" rows="3" class="form-input"
                              placeholder="Internal notes (not shown to client)"></textarea>

                    <div class="builder-card" style="margin-top:14px;">
                        <h5 style="margin-top:0;">Quick Preview</h5>
                        <div style="font-size:13px;font-weight:600;color:var(--text-white);">{{ $proposal->subject }}</div>
                        <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px;">
                            Date: {{ $proposal->date?->format('M d, Y') }} · {{ $proposal->project->agreement_code }}
                        </div>
                        <div style="font-size:12px;color:var(--text-secondary);margin-top:8px;">{{ \Illuminate\Support\Str::limit($proposal->intro_text, 220) }}</div>

                        <div style="margin-top:12px;font-size:11.5px;color:var(--text-muted);">
                            Modules: <strong style="color:var(--text-primary);">{{ $proposal->modules->count() }}</strong> ·
                            Quotation Items: <strong style="color:var(--text-primary);">{{ $proposal->quotationItems->count() }}</strong> ·
                            Grand Total: <strong style="color:var(--accent);">LKR {{ number_format($proposal->grandTotal(), 2) }}</strong>
                        </div>

                        <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap;">
                            <a href="{{ route('proposals.preview', $proposal) }}" target="_blank" class="btn btn-sm">Open Print Preview →</a>

                            @if($proposal->status === 'approved')
                                <button type="button" wire:click="createInvoice" class="btn btn-sm btn-primary">
                                    Create Invoice from Proposal →
                                </button>
                            @else
                                <span style="font-size:11px;color:var(--text-muted);align-self:center;">
                                    Set status to Approved to create an invoice.
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Footer nav --}}
                <div style="display:flex;justify-content:space-between;margin-top:24px;padding-top:14px;border-top:1px solid var(--border);">
                    <button type="button" class="btn" wire:click="back" @if($step === 1) disabled @endif>← Back</button>
                    @if($step < 5)
                        <button type="button" class="btn btn-primary" wire:click="next">Save &amp; Next →</button>
                    @else
                        <button type="button" class="btn btn-primary" wire:click="saveCurrentStep">Save</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('livewire.partials.proposal-styles')
</div>
