<?php

namespace App\Livewire\Admin;

use App\Models\Application;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Billing'])]
class BillingManager extends Component
{
    public int $year;
    public array $years = [];
    public ?int $markingAppId = null;
    public $billingPlans;

    public function mount(): void
    {
        $this->year  = now()->year;
        $this->years = [$this->year, $this->year - 1, $this->year - 2];
        $this->billingPlans = \App\Models\BillingPlan::all();
    }

    public function render()
    {
        $applications = Application::orderBy('name')
            ->with(['billingPlan', 'payments' => fn($q) => $q->where('year', $this->year)])
            ->get();

        // Build summary
        $allPayments = Payment::whereIn('application_id', $applications->pluck('id'))
            ->where('year', $this->year)
            ->get();

        $totalDue    = $allPayments->where('status', 'due')
                                   ->where('month', '<=', now()->month)
                                   ->sum('amount');
        $totalPaid   = $allPayments->where('status', 'paid')->sum('amount');
        $overdueCount = $allPayments->where('status', 'due')
                                    ->where('month', '<=', now()->month)
                                    ->count();

        $summary = [
            'total_due'     => $totalDue,
            'total_paid'    => $totalPaid,
            'overdue_count' => $overdueCount,
        ];

        return view('livewire.admin.billing-manager', [
            'applications' => $applications,
            'summary'      => $summary,
            'plans'        => $this->billingPlans,
        ])->title('Billing');
    }

    public function setPlan(int $appId, int $planId): void
    {
        Application::find($appId)->update(['billing_plan_id' => $planId]);
        session()->flash('message', 'Billing plan updated.');
    }

    public function togglePayment(int $appId, int $month): void
    {
        $payment = Payment::where('application_id', $appId)
            ->where('year', $this->year)
            ->where('month', $month)
            ->first();

        if (! $payment) {
            $app = Application::with('billingPlan')->find($appId);
            $amount = $app->billingPlan->price ?? 0;

            // No record — create as paid
            Payment::create([
                'application_id' => $appId,
                'year'           => $this->year,
                'month'          => $month,
                'amount'         => $amount,
                'status'         => 'paid',
                'paid_at'        => now(),
            ]);
            return;
        }

        if ($payment->status === 'due') {
            $payment->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);
        } elseif ($payment->status === 'paid') {
            $payment->update([
                'status'  => 'due',
                'paid_at' => null,
            ]);
        }
        // free status is not toggled via this method
    }

    public function applyAnnualDeal(int $appId): void
    {
        $now = now();
        $app = Application::with('billingPlan')->find($appId);
        $planPrice = $app->billingPlan->price ?? 0;

        for ($month = 1; $month <= 12; $month++) {
            $isFree   = $month >= 11;
            $amount   = $isFree ? 0 : $planPrice;
            $status   = $isFree ? 'free' : 'paid';

            Payment::updateOrCreate(
                [
                    'application_id' => $appId,
                    'year'           => $this->year,
                    'month'          => $month,
                ],
                [
                    'amount'  => $amount,
                    'status'  => $status,
                    'paid_at' => $now,
                ]
            );
        }
    }

    public function ensurePaymentsExist(int $appId): void
    {
        $currentMonth = now()->month;
        $currentYear  = now()->year;

        // Only auto-create for current year up to current month
        if ($this->year !== $currentYear) {
            return;
        }

        $app = Application::with('billingPlan')->find($appId);
        $planPrice = $app->billingPlan->price ?? 0;

        for ($month = 1; $month <= $currentMonth; $month++) {
            Payment::firstOrCreate(
                [
                    'application_id' => $appId,
                    'year'           => $this->year,
                    'month'          => $month,
                ],
                [
                    'amount' => $planPrice,
                    'status' => 'due',
                ]
            );
        }
    }

    private function getMonthStatus(int $appId, int $month, Collection $payments): ?Payment
    {
        return $payments->firstWhere(fn($p) => $p->application_id === $appId && $p->month === $month);
    }
}
