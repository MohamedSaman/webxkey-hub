<div>
    <div class="no-print" style="background:var(--bg-surface);padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
        <a href="{{ route('proposals.show', $proposal) }}" class="btn btn-sm">← Back to Builder</a>
        <div style="display:flex;gap:8px;">
            <button onclick="window.print()" class="btn btn-primary btn-sm">Print / Save as PDF</button>
        </div>
    </div>

    <div class="proposal-doc">
        {{-- Header --}}
        <div class="doc-header">
            <div class="doc-brand">
                <img src="{{ asset('webxkey_logo.png') }}" alt="WebXKey" class="doc-logo">
                <div class="doc-brand-meta">
                    <strong>WEBXKEY PVT LTD</strong><br>
                    273/1 D Warana Road, Central Place,<br>
                    Thihariya<br>
                    +94 755 299 721
                </div>
            </div>
            <div class="doc-meta">
                <div class="doc-meta-row"><span>Date:</span><strong>{{ $proposal->date?->format('d M Y') }}</strong></div>
                <div class="doc-meta-row"><span>Code:</span><strong>{{ $proposal->project->agreement_code }}</strong></div>
            </div>
        </div>

        {{-- To --}}
        <div class="doc-to">
            <div class="doc-label">To:</div>
            <div class="doc-client-name">{{ $proposal->project->client?->name }}</div>
            <div class="doc-client-address">{{ $proposal->project->client?->address }}</div>
        </div>

        {{-- Subject --}}
        <div class="doc-subject">
            <strong>Subject:</strong> {{ $proposal->subject }}
        </div>

        <div class="doc-greeting">Dear Client,</div>

        <div class="doc-paragraph">
            {{ $proposal->intro_text }}
        </div>

        {{-- 1. Scope of Work --}}
        <h2 class="doc-h2">1. Scope of Work</h2>
        @foreach($proposal->modules as $i => $module)
            <div class="doc-module">
                <div class="doc-module-title">{{ $i + 1 }}.{{ $i + 1 }} {{ $module->title }}</div>
                @if($module->description)
                    <div class="doc-module-desc">{{ $module->description }}</div>
                @endif
                <ul class="doc-feature-list">
                    @foreach($module->features as $feature)
                        <li>{{ $feature->feature_text }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        {{-- 2. Terms & Conditions --}}
        <h2 class="doc-h2">2. Terms &amp; Conditions</h2>
        <div class="doc-paragraph">
            <strong>Payment Schedule:</strong>
            <ul class="doc-feature-list">
                <li>{{ $proposal->payment_advance_pct }}% advance upon project commencement.</li>
                <li>{{ $proposal->payment_middle_pct }}% upon completion of system development and review.</li>
                <li>{{ $proposal->payment_final_pct }}% upon final delivery and deployment.</li>
            </ul>
        </div>
        <div class="doc-paragraph">
            <strong>Monthly Support &amp; Maintenance:</strong> An optional monthly support plan is available for <strong>LKR {{ number_format($proposal->monthly_support_fee, 2) }}/month</strong>, covering bug fixes, minor updates, and technical assistance.
        </div>
        @if($proposal->hosting_enabled)
            <div class="doc-paragraph">
                <strong>Hosting &amp; Domain:</strong> Hosting service is included for {{ $proposal->hosting_months }} months at LKR {{ number_format($proposal->hosting_price, 2) }}/month, totaling LKR {{ number_format($proposal->totalHostingCost(), 2) }}.
            </div>
        @endif
        <div class="doc-paragraph">
            <strong>Customisation &amp; Additional Features:</strong> Any additional features or customisations beyond the agreed scope will be billed at LKR {{ number_format($proposal->additional_feature_rate, 2) }} per feature, subject to mutual agreement.
        </div>
        <div class="doc-paragraph">
            <strong>Confidentiality:</strong> All client data, business processes, and project information shared during development will be kept strictly confidential.
        </div>
        <div class="doc-paragraph">
            <strong>Ownership:</strong> Upon final payment, the client will receive full ownership of the developed system, including source code, documentation, and assets.
        </div>
        <div class="doc-paragraph">
            <strong>Change Management:</strong> Major scope changes after project commencement may require timeline and cost re-estimation.
        </div>

        {{-- 3. Acceptance --}}
        <h2 class="doc-h2">3. Acceptance</h2>
        <div class="doc-paragraph">
            By signing this agreement, the client acknowledges acceptance of the scope, terms, and pricing detailed above. WebxKey Pvt Ltd will commence development upon receipt of the advance payment. For any queries, please contact us at admin@webxkey.com or +94 71 234 5678.
        </div>

        {{-- 4. Payment Quotation --}}
        <h2 class="doc-h2">4. Payment Quotation</h2>
        <table class="doc-table">
            <thead>
                <tr>
                    <th style="text-align:left;">Description</th>
                    <th style="text-align:right;">Amount (LKR)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proposal->quotationItems as $qi)
                    <tr>
                        <td>{{ $qi->description }}</td>
                        <td style="text-align:right;">{{ number_format($qi->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td>Total System Development Cost</td>
                        <td style="text-align:right;">{{ number_format($proposal->total_system_cost, 2) }}</td>
                    </tr>
                @endforelse

                @if($proposal->hosting_enabled)
                    <tr>
                        <td>Hosting &amp; Domain ({{ $proposal->hosting_months }} months)</td>
                        <td style="text-align:right;">{{ number_format($proposal->totalHostingCost(), 2) }}</td>
                    </tr>
                @endif

                @if((float) $proposal->discount > 0)
                    <tr>
                        <td>Discount</td>
                        <td style="text-align:right;color:#b91c1c;">− {{ number_format($proposal->discount, 2) }}</td>
                    </tr>
                @endif

                <tr class="doc-table-total">
                    <td><strong>Total Development Cost</strong></td>
                    <td style="text-align:right;"><strong>LKR {{ number_format($proposal->grandTotal(), 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="doc-paragraph" style="margin-top:14px;">
            We kindly request a <strong>{{ $proposal->payment_advance_pct }}% advance payment of LKR {{ number_format($proposal->advanceAmount(), 2) }}</strong> upon agreement to commence development. Once the advance is received, we will begin the planning and development phases of your project.
        </div>

        {{-- Signatures --}}
        <div class="doc-sign-row">
            <div class="doc-sign-col">
                <div class="doc-sign-label">Sincerely,</div>
                <div style="height:48px;"></div>
                <div class="doc-sign-name">Mr. Farhan</div>
                <div class="doc-sign-title">Webxkey (Pvt) Ltd</div>
                <div class="doc-sign-title">Director</div>
            </div>
            <div class="doc-sign-col">
                <div class="doc-sign-label">Client Acceptance</div>
                <div style="height:48px;border-bottom:1px solid #888;margin-bottom:6px;"></div>
                <div class="doc-sign-title">Signature</div>
                <div style="height:18px;border-bottom:1px solid #888;margin:8px 0 6px;"></div>
                <div class="doc-sign-title">Name</div>
                <div style="height:18px;border-bottom:1px solid #888;margin:8px 0 6px;"></div>
                <div class="doc-sign-title">Position</div>
                <div style="height:18px;border-bottom:1px solid #888;margin:8px 0 6px;"></div>
                <div class="doc-sign-title">Date</div>
            </div>
        </div>
    </div>

    <style>
        body { background: #e5e7eb; }
        .proposal-doc {
            background: #fff;
            color: #1f2937;
            max-width: 820px;
            margin: 24px auto;
            padding: 56px 64px;
            border: 1px solid #d1d5db;
            box-shadow: 0 4px 18px rgba(0,0,0,.18);
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 12px;
            line-height: 1.55;
        }
        .doc-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            border-bottom: 2px solid #1f2937; padding-bottom: 16px; margin-bottom: 22px;
        }
        .doc-logo {
            height: 52px; width: auto; display: block; margin-bottom: 8px;
        }
        .doc-brand-meta {
            font-size: 11px; color: #1f2937; line-height: 1.6;
        }
        .doc-meta { text-align: right; font-size: 12px; }
        .doc-meta-row { margin-bottom: 4px; }
        .doc-meta-row span { color: #6b7280; margin-right: 6px; }
        .doc-to { margin-bottom: 18px; }
        .doc-label { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; }
        .doc-client-name { font-size: 14px; font-weight: 700; color: #1f2937; margin-top: 4px; }
        .doc-client-address { font-size: 12px; color: #4b5563; white-space: pre-line; }
        .doc-subject {
            background: #f3f4f6; padding: 10px 14px;
            border-left: 3px solid #1d4ed8;
            margin: 18px 0; font-size: 13px;
        }
        .doc-greeting { margin-bottom: 8px; font-weight: 500; }
        .doc-paragraph { margin-bottom: 12px; text-align: justify; }
        .doc-h2 {
            font-size: 15px; font-weight: 700; color: #1f2937;
            margin: 28px 0 12px; padding-bottom: 6px; border-bottom: 1px solid #d1d5db;
        }
        .doc-module { margin-bottom: 14px; }
        .doc-module-title { font-size: 13px; font-weight: 700; color: #1f2937; margin-bottom: 4px; }
        .doc-module-desc { font-size: 12px; color: #4b5563; margin-bottom: 6px; }
        .doc-feature-list { list-style: disc; padding-left: 22px; margin: 4px 0; }
        .doc-feature-list li { margin-bottom: 3px; font-size: 12px; }
        .doc-table {
            width: 100%; border-collapse: collapse; margin-top: 10px;
            border: 1px solid #d1d5db; font-size: 12px;
        }
        .doc-table th, .doc-table td {
            padding: 8px 12px; border: 1px solid #d1d5db;
        }
        .doc-table thead th {
            background: #1f2937; color: #fff; font-weight: 600;
        }
        .doc-table-total td {
            background: #f3f4f6; font-size: 13px;
        }
        .doc-sign-row {
            display: grid; grid-template-columns: 1fr 1fr; gap: 60px;
            margin-top: 50px;
        }
        .doc-sign-col {}
        .doc-sign-label { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px; }
        .doc-sign-name { font-weight: 700; font-size: 13px; }
        .doc-sign-title { font-size: 11px; color: #4b5563; }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .proposal-doc {
                margin: 0; padding: 24px 28px;
                border: none; box-shadow: none; max-width: none;
            }
        }
    </style>
</div>
