<div>
    {{-- Topbar --}}
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button class="hamburger" onclick="toggleSidebar()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            <div class="page-title">Billing</div>
            <select wire:model.live="year"
                    style="background:var(--bg-input);border:1px solid var(--border-strong);color:var(--text-primary);border-radius:var(--radius-sm);padding:5px 10px;font-size:12.5px;cursor:pointer;">
                @foreach($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end">
            <span class="badge" style="background:var(--red-bg);color:var(--red);border:1px solid var(--red-border);font-size:11.5px;">
                Due: LKR {{ number_format($summary['total_due']) }}
            </span>
            <span class="badge" style="background:var(--green-bg);color:var(--green);border:1px solid var(--green-border);font-size:11.5px;">
                Paid: LKR {{ number_format($summary['total_paid']) }}
            </span>
            @if($summary['overdue_count'] > 0)
                <span class="badge badge-red" style="font-size:11.5px;">{{ $summary['overdue_count'] }} overdue</span>
            @endif
        </div>
    </div>

    <div class="content-area">

        {{-- Summary Cards --}}
        <div class="stats-row">
            <div class="panel" style="padding:16px 20px;">
                <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Total Due</div>
                <div style="font-size:22px;font-weight:600;color:var(--red);">LKR {{ number_format($summary['total_due']) }}</div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Outstanding this year</div>
            </div>
            <div class="panel" style="padding:16px 20px;">
                <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Total Collected</div>
                <div style="font-size:22px;font-weight:600;color:var(--green);">LKR {{ number_format($summary['total_paid']) }}</div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Paid in {{ $year }}</div>
            </div>
            <div class="panel" style="padding:16px 20px;">
                <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Overdue Months</div>
                <div style="font-size:22px;font-weight:600;color:{{ $summary['overdue_count'] > 0 ? 'var(--red)' : 'var(--green)' }};">
                    {{ $summary['overdue_count'] }}
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Unpaid past months</div>
            </div>
            <div class="panel" style="padding:16px 20px;">
                <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Apps Managed</div>
                <div style="font-size:22px;font-weight:600;color:var(--accent);">{{ $applications->count() }}</div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">@ LKR 2,000/mo each</div>
            </div>
        </div>

        {{-- Per-App Payment Table --}}
        <div class="panel" style="overflow-x:auto;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid var(--border);">
                <span style="font-size:13px;font-weight:600;color:var(--text-white);">Monthly Payment Tracker — {{ $year }}</span>
                <span style="font-size:11.5px;color:var(--text-muted);">Click a cell to toggle paid/due status</span>
            </div>

            <table style="width:100%;border-collapse:collapse;min-width:900px;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:8px 12px;font-size:11.5px;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border);white-space:nowrap;">Application</th>
                        @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $idx => $mon)
                            <th style="text-align:center;padding:8px 6px;font-size:10.5px;font-weight:600;color:{{ (now()->year === $year && ($idx+1) === now()->month) ? 'var(--accent)' : 'var(--text-muted)' }};border-bottom:1px solid var(--border);min-width:52px;">
                                {{ $mon }}
                            </th>
                        @endforeach
                        <th style="text-align:center;padding:8px 12px;font-size:11.5px;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border);white-space:nowrap;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                        @php
                            $appPayments = $app->payments; // already eager-loaded for this year
                            $currentMonth = now()->month;
                            $currentYear  = now()->year;
                        @endphp
                        <tr style="border-bottom:1px solid var(--border);" class="billing-row">
                            <td style="padding:10px 12px;white-space:nowrap;">
                                <div style="font-size:13px;font-weight:500;color:var(--text-white);">{{ $app->name }}</div>
                                <div style="font-size:11px;color:var(--text-muted);">{{ $app->domain }}</div>
                            </td>

                            @for($m = 1; $m <= 12; $m++)
                                @php
                                    $payment = $appPayments->firstWhere('month', $m);
                                    $isFuture = ($year === $currentYear) && ($m > $currentMonth);
                                    $isPast   = ($year < $currentYear);

                                    if ($payment) {
                                        $cellStatus = $payment->status;
                                    } elseif ($isFuture) {
                                        $cellStatus = 'future';
                                    } else {
                                        $cellStatus = 'unrecorded_due'; // past or current, no record
                                    }
                                @endphp

                                <td style="padding:5px 4px;text-align:center;">
                                    @if($cellStatus === 'paid')
                                        <button wire:click="togglePayment({{ $app->id }}, {{ $m }})"
                                                wire:loading.attr="disabled"
                                                wire:target="togglePayment({{ $app->id }}, {{ $m }})"
                                                title="Paid — click to mark due"
                                                style="display:inline-flex;align-items:center;justify-content:center;width:44px;height:26px;border-radius:5px;border:1px solid var(--green-border);background:var(--green-bg);color:var(--green);font-size:11px;font-weight:600;cursor:pointer;transition:opacity .15s;">
                                            <span wire:loading.remove wire:target="togglePayment({{ $app->id }}, {{ $m }})">✓</span>
                                            <span wire:loading wire:target="togglePayment({{ $app->id }}, {{ $m }})"><span class="spinner" style="width:10px;height:10px;"></span></span>
                                        </button>

                                    @elseif($cellStatus === 'free')
                                        <span style="display:inline-flex;align-items:center;justify-content:center;width:44px;height:26px;border-radius:5px;border:1px solid rgba(96,165,250,0.2);background:var(--blue-bg);color:var(--blue);font-size:9.5px;font-weight:700;letter-spacing:.04em;">
                                            FREE
                                        </span>

                                    @elseif($cellStatus === 'due')
                                        <button wire:click="togglePayment({{ $app->id }}, {{ $m }})"
                                                wire:loading.attr="disabled"
                                                wire:target="togglePayment({{ $app->id }}, {{ $m }})"
                                                title="Overdue — click to mark paid"
                                                style="display:inline-flex;align-items:center;justify-content:center;width:44px;height:26px;border-radius:5px;border:1px solid var(--red-border);background:var(--red-bg);color:var(--red);font-size:9.5px;font-weight:700;letter-spacing:.04em;cursor:pointer;transition:opacity .15s;">
                                            <span wire:loading.remove wire:target="togglePayment({{ $app->id }}, {{ $m }})">DUE</span>
                                            <span wire:loading wire:target="togglePayment({{ $app->id }}, {{ $m }})"><span class="spinner" style="width:10px;height:10px;"></span></span>
                                        </button>

                                    @elseif($cellStatus === 'unrecorded_due')
                                        {{-- Past/current month, no payment record yet — clickable to mark paid --}}
                                        <button wire:click="togglePayment({{ $app->id }}, {{ $m }})"
                                                wire:loading.attr="disabled"
                                                wire:target="togglePayment({{ $app->id }}, {{ $m }})"
                                                title="No record — click to mark paid"
                                                style="display:inline-flex;align-items:center;justify-content:center;width:44px;height:26px;border-radius:5px;border:1px solid var(--red-border);background:var(--red-bg);color:var(--red);font-size:9.5px;font-weight:700;letter-spacing:.04em;cursor:pointer;transition:opacity .15s;">
                                            <span wire:loading.remove wire:target="togglePayment({{ $app->id }}, {{ $m }})">DUE</span>
                                            <span wire:loading wire:target="togglePayment({{ $app->id }}, {{ $m }})"><span class="spinner" style="width:10px;height:10px;"></span></span>
                                        </button>

                                    @else
                                        {{-- Future month — not interactive --}}
                                        <span style="display:inline-flex;align-items:center;justify-content:center;width:44px;height:26px;border-radius:5px;background:rgba(255,255,255,0.03);color:var(--text-muted);font-size:14px;">
                                            —
                                        </span>
                                    @endif
                                </td>
                            @endfor

                            <td style="padding:8px 12px;text-align:center;">
                                <button wire:click="applyAnnualDeal({{ $app->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="applyAnnualDeal({{ $app->id }})"
                                        onclick="return confirm('Apply annual deal for {{ addslashes($app->name) }}?\n\nMonths 1–10 → Paid (LKR 2,000 each)\nMonths 11–12 → FREE\n\nThis will overwrite existing records.')"
                                        class="btn btn-sm btn-primary"
                                        style="white-space:nowrap;font-size:11px;">
                                    <span wire:loading.remove wire:target="applyAnnualDeal({{ $app->id }})">Annual Deal</span>
                                    <span wire:loading wire:target="applyAnnualDeal({{ $app->id }})"><span class="spinner"></span></span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" style="text-align:center;padding:40px;color:var(--text-muted);font-size:13px;">
                                No applications found. <a href="{{ route('deploy') }}" style="color:var(--accent);">Deploy one →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Legend --}}
        <div style="display:flex;align-items:center;gap:16px;margin-top:12px;padding:10px 16px;background:var(--bg-surface);border-radius:var(--radius-md);border:1px solid var(--border);flex-wrap:wrap;">
            <span style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Legend:</span>
            <span style="display:inline-flex;align-items:center;gap:6px;font-size:11.5px;color:var(--green);">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:20px;border-radius:4px;border:1px solid var(--green-border);background:var(--green-bg);font-size:11px;font-weight:600;">✓</span>
                Paid
            </span>
            <span style="display:inline-flex;align-items:center;gap:6px;font-size:11.5px;color:var(--red);">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:20px;border-radius:4px;border:1px solid var(--red-border);background:var(--red-bg);font-size:9px;font-weight:700;">DUE</span>
                Overdue
            </span>
            <span style="display:inline-flex;align-items:center;gap:6px;font-size:11.5px;color:var(--blue);">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:20px;border-radius:4px;border:1px solid rgba(96,165,250,0.2);background:var(--blue-bg);font-size:9px;font-weight:700;">FREE</span>
                Free (annual deal)
            </span>
            <span style="display:inline-flex;align-items:center;gap:6px;font-size:11.5px;color:var(--text-muted);">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:20px;border-radius:4px;background:rgba(255,255,255,0.03);font-size:14px;">—</span>
                Future month
            </span>
        </div>

    </div>

    <style>
        .billing-row:hover td { background: rgba(255,255,255,0.015); }
        .billing-row button:hover { opacity: 0.75; }
    </style>
</div>
