<div>
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button class="hamburger" onclick="toggleSidebar()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            <div class="page-title">Earnings Overview</div>
        </div>
    </div>

    <div class="content-area">

        {{-- ── Summary Cards ─────────────────────────────────────────── --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-bottom:24px;">

            {{-- Total Project Value --}}
            <div class="stat-card" style="border-left:3px solid var(--accent);">
                <div class="stat-label">Total Project Value</div>
                <div style="font-size:22px;font-weight:700;color:var(--accent);">
                    LKR {{ number_format($this->summary['totalValue'], 0) }}
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">All projects combined</div>
            </div>

            {{-- Total Received --}}
            <div class="stat-card" style="border-left:3px solid var(--green);">
                <div class="stat-label">Total Received</div>
                <div style="font-size:22px;font-weight:700;color:var(--green);">
                    LKR {{ number_format($this->summary['totalPaid'], 0) }}
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Payments collected</div>
            </div>

            {{-- Outstanding Due --}}
            <div class="stat-card" style="border-left:3px solid var(--red);">
                <div class="stat-label">Outstanding Due</div>
                <div style="font-size:22px;font-weight:700;color:var(--red);">
                    LKR {{ number_format($this->summary['totalDue'], 0) }}
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Balance to collect</div>
            </div>

            {{-- Pending / Not Confirmed --}}
            <div class="stat-card" style="border-left:3px solid var(--yellow);">
                <div class="stat-label">Pending (Not Confirmed)</div>
                <div style="font-size:22px;font-weight:700;color:var(--yellow);">
                    LKR {{ number_format($this->summary['pendingValue'], 0) }}
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Draft / sent, not started</div>
            </div>

            {{-- Confirmed Project Value --}}
            <div class="stat-card" style="border-left:3px solid #818cf8;">
                <div class="stat-label">Confirmed Value</div>
                <div style="font-size:22px;font-weight:700;color:#818cf8;">
                    LKR {{ number_format($this->summary['confirmedValue'], 0) }}
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Approved + ongoing + completed</div>
            </div>

            {{-- Actual Net Earning --}}
            <div class="stat-card" style="border-left:3px solid var(--green);background:var(--green-bg);">
                <div class="stat-label" style="color:var(--green);">Actual Net Earning</div>
                <div style="font-size:24px;font-weight:700;color:var(--green);">
                    LKR {{ number_format($this->summary['actualNet'], 0) }}
                </div>
                <div style="font-size:11px;color:var(--green);opacity:.7;margin-top:4px;">Cash in hand</div>
            </div>

        </div>

        {{-- ── Collection Progress Bar ──────────────────────────────────── --}}
        @php
            $totalVal = $this->summary['totalValue'];
            $paidPct  = $totalVal > 0 ? min(100, round($this->summary['totalPaid'] / $totalVal * 100)) : 0;
            $duePct   = $totalVal > 0 ? min(100 - $paidPct, round($this->summary['totalDue'] / $totalVal * 100)) : 0;
        @endphp
        <div class="panel" style="margin-bottom:20px;padding:16px 20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <span style="font-size:12.5px;font-weight:600;color:var(--text-white);">Collection Progress</span>
                <span style="font-size:12px;color:var(--text-muted);">{{ $paidPct }}% collected</span>
            </div>
            <div style="height:10px;background:var(--bg-elevated);border-radius:99px;overflow:hidden;display:flex;">
                <div style="width:{{ $paidPct }}%;background:var(--green);transition:width .4s;"></div>
                <div style="width:{{ $duePct }}%;background:var(--red);opacity:.6;transition:width .4s;"></div>
            </div>
            <div style="display:flex;gap:18px;margin-top:8px;">
                <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--text-muted);">
                    <span style="width:10px;height:10px;border-radius:50%;background:var(--green);display:inline-block;"></span> Collected
                </div>
                <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--text-muted);">
                    <span style="width:10px;height:10px;border-radius:50%;background:var(--red);opacity:.6;display:inline-block;"></span> Due
                </div>
                <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--text-muted);">
                    <span style="width:10px;height:10px;border-radius:50%;background:var(--bg-elevated);border:1px solid var(--border);display:inline-block;"></span> Pending
                </div>
            </div>
        </div>

        {{-- ── Filters ──────────────────────────────────────────────────── --}}
        <div class="panel" style="margin-bottom:14px;padding:12px 16px;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span style="font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Filter:</span>

                <select wire:model.live="filterYear" class="form-input" style="width:110px;">
                    <option value="">All Years</option>
                    @foreach($this->availableYears as $yr)
                        <option value="{{ $yr }}">{{ $yr }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterMonth" class="form-input" style="width:130px;">
                    <option value="">All Months</option>
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>

                <select wire:model.live="filterStatus" class="form-input" style="width:140px;">
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                </select>

                @if($filterMonth || $filterStatus || ($filterYear != now()->year))
                    <button wire:click="$set('filterMonth','');$set('filterYear','{{ now()->year }}');$set('filterStatus','')"
                            class="btn btn-sm" style="font-size:11px;">
                        Reset
                    </button>
                @endif

                <span style="margin-left:auto;font-size:12px;color:var(--text-muted);">
                    {{ $this->projects->count() }} project(s)
                </span>
            </div>
        </div>

        {{-- ── Project List ─────────────────────────────────────────────── --}}
        <div class="panel" style="padding:0;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:var(--bg-elevated);">
                        <th style="text-align:left;padding:10px 14px;font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;">ID / Code</th>
                        <th style="text-align:left;padding:10px 14px;font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Project</th>
                        <th style="text-align:left;padding:10px 14px;font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;">Start Date</th>
                        <th style="text-align:right;padding:10px 14px;font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;">Total (LKR)</th>
                        <th style="text-align:right;padding:10px 14px;font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;">Paid (LKR)</th>
                        <th style="text-align:right;padding:10px 14px;font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;">Due (LKR)</th>
                        <th style="text-align:center;padding:10px 14px;font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Status</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->projects as $p)
                        @php
                            $rowDue = $p->_due;
                        @endphp
                        <tr wire:key="row-{{ $p->id }}"
                            style="border-top:1px solid var(--border);transition:background .12s;"
                            onmouseover="this.style.background='var(--bg-elevated)'"
                            onmouseout="this.style.background=''">

                            {{-- ID / Code --}}
                            <td style="padding:10px 14px;">
                                <div style="font-size:12px;font-weight:700;color:var(--accent);">{{ $p->agreement_code ?: '#'.$p->id }}</div>
                                <div style="font-size:10.5px;color:var(--text-muted);">{{ ucfirst($p->type) }}</div>
                            </td>

                            {{-- Project name + client --}}
                            <td style="padding:10px 14px;">
                                <div style="font-size:13px;font-weight:600;color:var(--text-white);">{{ $p->name }}</div>
                                <div style="font-size:11px;color:var(--text-muted);">{{ $p->client?->name }}</div>
                            </td>

                            {{-- Start date --}}
                            <td style="padding:10px 14px;white-space:nowrap;">
                                <div style="font-size:12px;color:var(--text-primary);">{{ $p->created_at->format('d M Y') }}</div>
                            </td>

                            {{-- Total --}}
                            <td style="padding:10px 14px;text-align:right;white-space:nowrap;">
                                @if($p->_total > 0)
                                    <span style="font-size:13px;font-weight:600;color:var(--text-white);">{{ number_format($p->_total, 0) }}</span>
                                @else
                                    <span style="font-size:12px;color:var(--text-muted);">—</span>
                                @endif
                            </td>

                            {{-- Paid --}}
                            <td style="padding:10px 14px;text-align:right;white-space:nowrap;">
                                @if($p->_paid > 0)
                                    <span style="font-size:13px;font-weight:600;color:var(--green);">{{ number_format($p->_paid, 0) }}</span>
                                @else
                                    <span style="font-size:12px;color:var(--text-muted);">—</span>
                                @endif
                            </td>

                            {{-- Due --}}
                            <td style="padding:10px 14px;text-align:right;white-space:nowrap;">
                                @if($rowDue > 0)
                                    <span style="font-size:13px;font-weight:600;color:var(--red);">{{ number_format($rowDue, 0) }}</span>
                                @else
                                    <span style="font-size:12px;color:var(--green);">Cleared</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td style="padding:10px 14px;text-align:center;">
                                @php
                                    $badgeClass = match($p->status) {
                                        'completed' => 'badge-green',
                                        'ongoing'   => 'badge-blue',
                                        'approved'  => 'badge-blue',
                                        'pending'   => 'badge-yellow',
                                        default     => 'badge-gray',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}" style="font-size:10px;">
                                    {{ strtoupper($p->status) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td style="padding:10px 10px;text-align:center;">
                                @if($p->invoices->isNotEmpty())
                                    <a href="{{ route('invoices.show', $p->invoices->first()) }}"
                                       class="btn btn-sm" style="font-size:10.5px;padding:3px 8px;">Invoice</a>
                                @elseif($p->proposals->isNotEmpty())
                                    <a href="{{ route('proposals.show', $p->proposals->first()) }}"
                                       class="btn btn-sm" style="font-size:10.5px;padding:3px 8px;">Proposal</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding:40px;text-align:center;color:var(--text-muted);font-size:13px;">
                                No projects found for the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- List totals footer --}}
                @if($this->projects->count() > 0)
                    @php
                        $listTotal = $this->projects->sum('_total');
                        $listPaid  = $this->projects->sum('_paid');
                        $listDue   = $this->projects->sum('_due');
                    @endphp
                    <tfoot>
                        <tr style="border-top:2px solid var(--border-strong);background:var(--bg-elevated);">
                            <td colspan="3" style="padding:10px 14px;font-size:12px;font-weight:700;color:var(--text-muted);">
                                TOTAL ({{ $this->projects->count() }} projects)
                            </td>
                            <td style="padding:10px 14px;text-align:right;font-size:13px;font-weight:700;color:var(--text-white);">
                                {{ number_format($listTotal, 0) }}
                            </td>
                            <td style="padding:10px 14px;text-align:right;font-size:13px;font-weight:700;color:var(--green);">
                                {{ number_format($listPaid, 0) }}
                            </td>
                            <td style="padding:10px 14px;text-align:right;font-size:13px;font-weight:700;color:var(--red);">
                                {{ number_format($listDue, 0) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

    </div>
</div>
