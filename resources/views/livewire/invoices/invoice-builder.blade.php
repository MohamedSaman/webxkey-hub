<div>
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button class="hamburger" onclick="toggleSidebar()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            <a href="{{ route('projects') }}" class="btn btn-sm">← Projects</a>
            <div class="page-title">{{ $invoice->invoice_number }}</div>
            <span class="badge {{ $invoice->status === 'paid' ? 'badge-green' : ($invoice->status === 'partial' ? 'badge-yellow' : ($invoice->status === 'sent' ? 'badge-blue' : 'badge-gray')) }}">
                {{ strtoupper($invoice->status) }}
            </span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <a href="{{ route('invoices.preview', $invoice) }}" target="_blank" class="btn btn-sm">Preview / Print</a>
        </div>
    </div>

    <div class="content-area">
        @if (session()->has('message'))
            <div class="panel" style="padding:10px 14px;margin-bottom:14px;background:var(--green-bg);border:1px solid var(--green-border);color:var(--green);font-size:12.5px;">
                {{ session('message') }}
            </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 320px;gap:14px;">
            {{-- Main panel --}}
            <div>
                {{-- Header --}}
                <div class="panel" style="margin-bottom:14px;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px;">
                        <div>
                            <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Bill To</div>
                            <div style="font-size:15px;font-weight:700;color:var(--text-white);margin-top:4px;">{{ $invoice->project->client?->name }}</div>
                            <div style="font-size:12px;color:var(--text-secondary);white-space:pre-line;">{{ $invoice->project->client?->address }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:24px;font-weight:700;color:var(--accent);">{{ $invoice->invoice_number }}</div>
                            <div style="margin-top:8px;font-size:11px;color:var(--text-muted);">Project: {{ $invoice->project->name }}</div>
                            <div style="font-size:11px;color:var(--text-muted);">Code: {{ $invoice->project->agreement_code }}</div>
                        </div>
                    </div>

                    <div class="field-row-3" style="margin-top:14px;">
                        <div>
                            <label class="form-label">Date</label>
                            <input type="date" wire:model="date" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Due Date</label>
                            <input type="date" wire:model="due_date" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Payment Terms (Code)</label>
                            <input type="text" value="{{ $invoice->project->agreement_code }}" disabled class="form-input">
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-primary" wire:click="saveHeader" style="margin-top:10px;">Save Details</button>
                </div>

                {{-- Items --}}
                <div class="panel">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                        <span style="font-size:13px;font-weight:600;color:var(--text-white);">Line Items</span>
                        <button type="button" class="btn btn-sm btn-primary" wire:click="addItem">+ Add Item</button>
                    </div>

                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:var(--bg-elevated);">
                                <th style="text-align:left;padding:8px 10px;font-size:11px;color:var(--text-muted);">Description</th>
                                <th style="text-align:center;padding:8px 10px;font-size:11px;color:var(--text-muted);width:80px;">Qty</th>
                                <th style="text-align:right;padding:8px 10px;font-size:11px;color:var(--text-muted);width:130px;">Rate</th>
                                <th style="text-align:right;padding:8px 10px;font-size:11px;color:var(--text-muted);width:140px;">Amount</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->items as $item)
                                <tr wire:key="item-{{ $item->id }}" style="border-top:1px solid var(--border);">
                                    <td style="padding:6px 10px;">
                                        <input type="text" value="{{ $item->description }}"
                                               wire:change="updateItem({{ $item->id }}, 'description', $event.target.value)"
                                               class="form-input">
                                    </td>
                                    <td style="padding:6px 10px;">
                                        <input type="number" value="{{ $item->quantity }}"
                                               wire:change="updateItem({{ $item->id }}, 'quantity', $event.target.value)"
                                               class="form-input" style="text-align:center;">
                                    </td>
                                    <td style="padding:6px 10px;">
                                        <input type="number" step="0.01" value="{{ $item->rate }}"
                                               wire:change="updateItem({{ $item->id }}, 'rate', $event.target.value)"
                                               class="form-input" style="text-align:right;">
                                    </td>
                                    <td style="padding:6px 10px;text-align:right;font-weight:500;color:var(--text-primary);">
                                        {{ number_format($item->amount, 2) }}
                                    </td>
                                    <td>
                                        <button type="button" class="icon-btn" wire:click="deleteItem({{ $item->id }})">×</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" style="padding:20px;text-align:center;color:var(--text-muted);">No items yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Totals --}}
                    <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);max-width:300px;margin-left:auto;">
                        <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:4px;">
                            <span style="color:var(--text-muted);">Subtotal</span>
                            <strong style="color:var(--text-primary);">LKR {{ number_format($invoice->subtotal, 2) }}</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:4px;align-items:center;">
                            <span style="color:var(--text-muted);">Discount</span>
                            <input type="number" step="0.01" wire:model.lazy="discount" wire:change="saveHeader" class="form-input" style="width:120px;text-align:right;">
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:14px;padding-top:6px;border-top:1px solid var(--border);">
                            <strong style="color:var(--text-white);">Total</strong>
                            <strong style="color:var(--accent);">LKR {{ number_format($invoice->total, 2) }}</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-top:6px;">
                            <span style="color:var(--green);">Amount Paid</span>
                            <strong style="color:var(--green);">LKR {{ number_format($invoice->amount_paid, 2) }}</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:13px;margin-top:6px;padding:8px 10px;background:var(--red-bg);border:1px solid var(--red-border);border-radius:var(--radius-sm);">
                            <strong style="color:var(--red);">Balance Due</strong>
                            <strong style="color:var(--red);">LKR {{ number_format($invoice->balance_due, 2) }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Notes / Terms --}}
                <div class="panel" style="margin-top:14px;">
                    <h5 style="font-size:13px;font-weight:600;color:var(--text-white);margin-bottom:10px;">Notes &amp; Terms</h5>
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" wire:blur="saveHeader" rows="2" class="form-input"></textarea>

                    <label class="form-label" style="margin-top:10px;">Terms</label>
                    <textarea wire:model="terms" wire:blur="saveHeader" rows="3" class="form-input"></textarea>
                </div>
            </div>

            {{-- Right column --}}
            <div>
                {{-- Balance Due card --}}
                <div class="panel" style="margin-bottom:14px;text-align:center;background:var(--red-bg);border-color:var(--red-border);">
                    <div style="font-size:11px;color:var(--red);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Balance Due</div>
                    <div style="font-size:26px;font-weight:700;color:var(--red);">LKR {{ number_format($invoice->balance_due, 2) }}</div>
                </div>

                {{-- Status --}}
                <div class="panel" style="margin-bottom:14px;">
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Status</div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <button type="button" wire:click="setStatus('draft')"
                                class="btn btn-sm {{ $invoice->status === 'draft' ? 'btn-primary' : '' }}">Draft</button>
                        <button type="button" wire:click="setStatus('sent')"
                                class="btn btn-sm {{ $invoice->status === 'sent' ? 'btn-primary' : '' }}">Sent</button>
                        <button type="button" wire:click="setStatus('partial')"
                                class="btn btn-sm {{ $invoice->status === 'partial' ? 'btn-primary' : '' }}">Partial</button>
                        <button type="button" wire:click="setStatus('paid')"
                                class="btn btn-sm {{ $invoice->status === 'paid' ? 'btn-primary' : '' }}">Paid</button>
                    </div>
                </div>

                {{-- Record payment --}}
                <div class="panel" style="margin-bottom:14px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                        <span style="font-size:13px;font-weight:600;color:var(--text-white);">Payments</span>
                        <button type="button" class="btn btn-sm btn-primary" wire:click="openPayment">+ Record</button>
                    </div>

                    @if($showPaymentForm)
                        <div class="builder-card" style="background:var(--bg-elevated);">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" wire:model="paymentAmount" class="form-input">

                            <label class="form-label" style="margin-top:8px;">Type</label>
                            <select wire:model="paymentType" class="form-input">
                                <option value="advance">Advance</option>
                                <option value="middle">Middle</option>
                                <option value="final">Final</option>
                                <option value="other">Other</option>
                            </select>

                            <label class="form-label" style="margin-top:8px;">Date</label>
                            <input type="date" wire:model="paymentDate" class="form-input">

                            <label class="form-label" style="margin-top:8px;">Method</label>
                            <input type="text" wire:model="paymentMethod" class="form-input">

                            <label class="form-label" style="margin-top:8px;">Reference</label>
                            <input type="text" wire:model="paymentReference" class="form-input">

                            <div style="display:flex;gap:6px;margin-top:10px;">
                                <button type="button" class="btn btn-sm" wire:click="$set('showPaymentForm', false)" style="flex:1;">Cancel</button>
                                <button type="button" class="btn btn-sm btn-primary" wire:click="recordPayment" style="flex:1;">Record</button>
                            </div>
                        </div>
                    @endif

                    <div style="margin-top:10px;">
                        @forelse($invoice->payments as $p)
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);">
                                <div>
                                    <div style="font-size:12px;font-weight:500;color:var(--text-primary);">LKR {{ number_format($p->amount, 2) }}</div>
                                    <div style="font-size:10.5px;color:var(--text-muted);">
                                        {{ ucfirst($p->payment_type) }} · {{ $p->payment_date?->format('M d, Y') }}
                                        @if($p->method) · {{ $p->method }} @endif
                                    </div>
                                </div>
                                <button type="button" class="icon-btn"
                                        wire:click="deletePayment({{ $p->id }})"
                                        onclick="return confirm('Delete this payment?')">×</button>
                            </div>
                        @empty
                            <div style="font-size:11.5px;color:var(--text-muted);text-align:center;padding:10px 0;">No payments yet.</div>
                        @endforelse
                    </div>
                </div>

                {{-- Bank info --}}
                <div class="panel">
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Bank Details</div>
                    <div style="font-size:12px;color:var(--text-primary);line-height:1.7;">
                        <strong>Bank:</strong> HNB Bank<br>
                        <strong>Account:</strong> WEBXKEY PVT LTD<br>
                        <strong>Number:</strong> 025020447864<br>
                        <strong>Branch:</strong> Nittambuwa
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('livewire.partials.proposal-styles')
</div>
