<div>
    <div class="no-print" style="background:var(--bg-surface);padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm">← Back to Editor</a>
        <button onclick="window.print()" class="btn btn-primary btn-sm">Print / Save as PDF</button>
    </div>

    <div class="invoice-doc">
        {{-- Header --}}
        <div class="inv-head">
            <div class="inv-brand">
                <div class="inv-brand-mark">WebXKey</div>
                <div class="inv-brand-sub">PVT LTD</div>
                <div class="inv-brand-meta">
                    No 24/A, Kohombiliyawa,<br>
                    Kalagedihena, Nittambuwa<br>
                    Sri Lanka<br>
                    +94 71 234 5678
                </div>
            </div>
            <div class="inv-title">
                <div class="inv-title-text">INVOICE</div>
                <div class="inv-number">#{{ $invoice->invoice_number }}</div>
            </div>
        </div>

        <div class="inv-meta-row">
            <div class="inv-bill-to">
                <div class="inv-label">Bill To:</div>
                <div class="inv-client">{{ $invoice->project->client?->name }}</div>
                <div class="inv-client-addr">{{ $invoice->project->client?->address }}</div>
            </div>
            <div class="inv-meta">
                <div><span>Date:</span><strong>{{ $invoice->date?->format('M d, Y') }}</strong></div>
                <div><span>Payment Terms:</span><strong>{{ $invoice->project->agreement_code }}</strong></div>
                @if($invoice->due_date)
                    <div><span>Due Date:</span><strong>{{ $invoice->due_date->format('M d, Y') }}</strong></div>
                @endif
                <div class="inv-balance">
                    <span>Balance Due:</span>
                    <strong>LKR {{ number_format($invoice->balance_due, 2) }}</strong>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <table class="inv-items">
            <thead>
                <tr>
                    <th style="text-align:left;">Item</th>
                    <th style="text-align:center;width:80px;">Quantity</th>
                    <th style="text-align:right;width:120px;">Rate</th>
                    <th style="text-align:right;width:140px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td style="text-align:center;">{{ $item->quantity }}</td>
                        <td style="text-align:right;">{{ number_format($item->rate, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;color:#6b7280;">No items.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- Summary --}}
        <div class="inv-summary">
            <div class="inv-summary-row"><span>Subtotal</span><strong>LKR {{ number_format($invoice->subtotal, 2) }}</strong></div>
            @if((float) $invoice->discount > 0)
                <div class="inv-summary-row"><span>Discount</span><strong>− LKR {{ number_format($invoice->discount, 2) }}</strong></div>
            @endif
            <div class="inv-summary-row inv-summary-total"><span>Total</span><strong>LKR {{ number_format($invoice->total, 2) }}</strong></div>
            <div class="inv-summary-row"><span>Amount Paid</span><strong>LKR {{ number_format($invoice->amount_paid, 2) }}</strong></div>
            <div class="inv-summary-row inv-summary-balance"><span>Balance Due</span><strong>LKR {{ number_format($invoice->balance_due, 2) }}</strong></div>
        </div>

        {{-- Notes & Terms --}}
        @if($invoice->notes)
            <div class="inv-section"><div class="inv-section-title">Notes</div><div>{{ $invoice->notes }}</div></div>
        @endif
        @if($invoice->terms)
            <div class="inv-section"><div class="inv-section-title">Terms</div><div>{{ $invoice->terms }}</div></div>
        @endif

        {{-- Bank --}}
        <div class="inv-bank">
            <div class="inv-section-title">Bank Details</div>
            <div><strong>Bank:</strong> HNB BANK</div>
            <div><strong>Account Name:</strong> WEBXKEY PVT LTD</div>
            <div><strong>Account Number:</strong> 025020447864</div>
            <div><strong>Branch:</strong> Nittambuwa</div>
        </div>
    </div>

    <style>
        body { background: #e5e7eb; }
        .invoice-doc {
            background: #fff; color: #1f2937;
            max-width: 820px; margin: 24px auto;
            padding: 56px 64px;
            border: 1px solid #d1d5db;
            box-shadow: 0 4px 18px rgba(0,0,0,.18);
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 12px; line-height: 1.55;
        }
        .inv-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
        .inv-brand-mark { font-size: 24px; font-weight: 800; color: #1d4ed8; letter-spacing: -0.5px; }
        .inv-brand-sub { font-size: 11px; font-weight: 600; color: #6b7280; letter-spacing: 0.18em; margin-top: -2px; }
        .inv-brand-meta { font-size: 11px; color: #4b5563; margin-top: 8px; }
        .inv-title { text-align: right; }
        .inv-title-text { font-size: 38px; font-weight: 800; color: #1f2937; letter-spacing: 1px; }
        .inv-number { font-size: 14px; color: #6b7280; margin-top: 2px; }

        .inv-meta-row { display: flex; justify-content: space-between; gap: 40px; margin-bottom: 28px; }
        .inv-label { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
        .inv-client { font-size: 14px; font-weight: 700; color: #1f2937; }
        .inv-client-addr { font-size: 12px; color: #4b5563; white-space: pre-line; margin-top: 2px; }
        .inv-meta { text-align: right; min-width: 240px; }
        .inv-meta div { display: flex; justify-content: space-between; font-size: 12px; padding: 4px 0; border-bottom: 1px solid #e5e7eb; }
        .inv-meta div span { color: #6b7280; }
        .inv-balance { background: #1f2937; color: #fff !important; padding: 8px 12px !important; border-radius: 4px; margin-top: 6px; border: none !important; }
        .inv-balance span, .inv-balance strong { color: #fff !important; }
        .inv-balance strong { font-size: 14px; }

        .inv-items { width: 100%; border-collapse: collapse; margin-bottom: 18px; font-size: 12px; }
        .inv-items thead th { background: #1f2937; color: #fff; padding: 10px 12px; font-weight: 600; }
        .inv-items tbody td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; }

        .inv-summary { max-width: 320px; margin-left: auto; }
        .inv-summary-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 12.5px; }
        .inv-summary-row span { color: #6b7280; }
        .inv-summary-total { border-top: 1px solid #1f2937; border-bottom: 1px solid #1f2937; padding: 8px 0; margin-top: 4px; font-size: 14px; }
        .inv-summary-balance { background: #1f2937; color: #fff; padding: 10px 12px; margin-top: 6px; border-radius: 4px; }
        .inv-summary-balance span, .inv-summary-balance strong { color: #fff; }

        .inv-section { margin-top: 24px; font-size: 12px; }
        .inv-section-title { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 600; margin-bottom: 6px; }
        .inv-bank { margin-top: 30px; padding-top: 20px; border-top: 2px solid #1f2937; font-size: 12px; }
        .inv-bank div { padding: 2px 0; }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .invoice-doc { margin: 0; padding: 24px 28px; border: none; box-shadow: none; max-width: none; }
        }
    </style>
</div>
