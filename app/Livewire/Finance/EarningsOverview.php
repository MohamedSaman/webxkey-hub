<?php

namespace App\Livewire\Finance;

use App\Models\Project;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Earnings Overview')]
class EarningsOverview extends Component
{
    public string $filterMonth = '';
    public string $filterYear  = '';
    public string $filterStatus = '';

    public function mount(): void
    {
        $this->filterYear  = (string) now()->year;
        $this->filterMonth = '';
    }

    // ── All-time summary (cards) ──────────────────────────────────────────

    #[Computed]
    public function summary(): array
    {
        $projects = Project::with(['invoices', 'payments'])->get();

        $totalValue     = 0;
        $totalPaid      = 0;
        $totalDue       = 0;
        $pendingValue   = 0; // draft / pending / sent (not yet confirmed)
        $confirmedValue = 0; // approved / ongoing / completed

        foreach ($projects as $p) {
            $projectTotal = $p->invoices->sum('total');
            $projectPaid  = $p->payments->sum('amount');
            $projectDue   = $p->invoices->sum('balance_due');

            $totalValue += $projectTotal;
            $totalPaid  += $projectPaid;
            $totalDue   += $projectDue;

            if (in_array($p->status, ['draft', 'pending'], true)) {
                $pendingValue += $projectTotal;
            } else {
                $confirmedValue += $projectTotal;
            }
        }

        // Net value = confirmed projects total (excluding still-pending)
        $netConfirmed = $confirmedValue;
        // Actual net = only what has been received
        $actualNet = $totalPaid;

        return compact(
            'totalValue',
            'totalPaid',
            'totalDue',
            'pendingValue',
            'confirmedValue',
            'netConfirmed',
            'actualNet',
        );
    }

    // ── Filtered project list ─────────────────────────────────────────────

    #[Computed]
    public function projects(): Collection
    {
        $query = Project::with(['client', 'invoices', 'payments', 'proposals'])
            ->orderByDesc('created_at');

        if ($this->filterYear) {
            $query->whereYear('created_at', $this->filterYear);
        }

        if ($this->filterMonth) {
            $query->whereMonth('created_at', $this->filterMonth);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return $query->get()->map(function (Project $p) {
            $p->_total = (float) $p->invoices->sum('total');
            $p->_paid  = (float) $p->payments->sum('amount');
            $p->_due   = max(0, $p->_total - $p->_paid);
            return $p;
        });
    }

    #[Computed]
    public function availableYears(): array
    {
        return Project::selectRaw('YEAR(created_at) as yr')
            ->distinct()
            ->orderByDesc('yr')
            ->pluck('yr')
            ->filter()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.finance.earnings-overview');
    }
}
