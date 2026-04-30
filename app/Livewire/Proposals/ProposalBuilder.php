<?php

namespace App\Livewire\Proposals;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Proposal;
use App\Models\ProposalFeature;
use App\Models\ProposalModule;
use App\Models\ProposalQuotationItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Proposal Builder')]
class ProposalBuilder extends Component
{
    public Proposal $proposal;
    public int $step = 1;
    public bool $editMode = false;

    // Step 1 fields (project / client info & subject)
    public string $date = '';
    public string $subject = '';
    public string $agreementCode = '';
    public string $intro_text = '';

    // Step 3 fields (hosting & pricing)
    public bool $hosting_enabled = false;
    public float $hosting_price = 400;
    public int $hosting_months = 12;
    public int $payment_advance_pct = 30;
    public int $payment_middle_pct = 50;
    public int $payment_final_pct = 20;
    public float $monthly_support_fee = 2000;
    public float $additional_feature_rate = 4000;

    // Step 4 fields
    public float $total_system_cost = 0;
    public float $discount = 0;

    // Step 5 fields
    public string $statusValue = 'draft';
    public string $notes = '';

    public function mount(Proposal $proposal): void
    {
        $this->proposal = $proposal->load(['project.client', 'modules.features', 'quotationItems']);
        $this->date           = $proposal->date?->toDateString() ?? now()->toDateString();
        $this->subject        = $proposal->subject ?? '';
        $this->agreementCode  = $proposal->project->agreement_code ?? '';
        $this->intro_text     = $proposal->intro_text ?? '';

        $this->hosting_enabled         = (bool) $proposal->hosting_enabled;
        $this->hosting_price           = (float) $proposal->hosting_price;
        $this->hosting_months          = (int) $proposal->hosting_months;
        $this->payment_advance_pct     = (int) $proposal->payment_advance_pct;
        $this->payment_middle_pct      = (int) $proposal->payment_middle_pct;
        $this->payment_final_pct       = (int) $proposal->payment_final_pct;
        $this->monthly_support_fee     = (float) $proposal->monthly_support_fee;
        $this->additional_feature_rate = (float) $proposal->additional_feature_rate;

        $this->total_system_cost = (float) $proposal->total_system_cost;
        $this->discount          = (float) $proposal->discount;

        $this->statusValue = $proposal->status;
        $this->notes       = $proposal->notes ?? '';

        // Start in edit mode only for draft/sent proposals
        $this->editMode = in_array($proposal->status, ['draft', 'sent'], true);
    }

    public function render()
    {
        return view('livewire.proposals.proposal-builder');
    }

    public function setStep(int $n): void
    {
        if ($n >= 1 && $n <= 5) {
            $this->step = $n;
        }
    }

    public function next(): void
    {
        $this->saveCurrentStep();
        if ($this->step < 5) {
            $this->step++;
        }
    }

    public function back(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function saveCurrentStep(): void
    {
        if ($this->step === 1) {
            $this->proposal->update([
                'date'       => $this->date,
                'subject'    => $this->subject,
                'intro_text' => $this->intro_text,
            ]);
            if ($this->agreementCode && $this->agreementCode !== $this->proposal->project->agreement_code) {
                $this->proposal->project->update(['agreement_code' => $this->agreementCode]);
            }
        } elseif ($this->step === 3) {
            $this->proposal->update([
                'hosting_enabled'         => $this->hosting_enabled,
                'hosting_price'           => $this->hosting_price,
                'hosting_months'          => $this->hosting_months,
                'payment_advance_pct'     => $this->payment_advance_pct,
                'payment_middle_pct'      => $this->payment_middle_pct,
                'payment_final_pct'       => $this->payment_final_pct,
                'monthly_support_fee'     => $this->monthly_support_fee,
                'additional_feature_rate' => $this->additional_feature_rate,
            ]);
        } elseif ($this->step === 4) {
            $this->proposal->update([
                'total_system_cost' => $this->total_system_cost,
                'discount'          => $this->discount,
            ]);
        } elseif ($this->step === 5) {
            $this->proposal->update([
                'notes'  => $this->notes,
            ]);
        }
        $this->proposal->refresh();
    }

    // ── Step 2: Modules ──────────────────────────────────────────────────
    public function addModule(): void
    {
        ProposalModule::create([
            'proposal_id' => $this->proposal->id,
            'title'       => 'New Module',
            'description' => '',
            'sort_order'  => $this->proposal->modules()->count(),
        ]);
        $this->proposal->load('modules.features');
    }

    public function updateModule(int $moduleId, string $field, string $value): void
    {
        if (in_array($field, ['title', 'description'], true)) {
            ProposalModule::where('id', $moduleId)
                ->where('proposal_id', $this->proposal->id)
                ->update([$field => $value]);
        }
        $this->proposal->load('modules.features');
    }

    public function deleteModule(int $moduleId): void
    {
        ProposalModule::where('id', $moduleId)
            ->where('proposal_id', $this->proposal->id)
            ->delete();
        $this->proposal->load('modules.features');
    }

    public function addFeature(int $moduleId): void
    {
        $module = ProposalModule::where('id', $moduleId)
            ->where('proposal_id', $this->proposal->id)
            ->firstOrFail();

        ProposalFeature::create([
            'module_id'    => $module->id,
            'feature_text' => 'New feature',
            'sort_order'   => $module->features()->count(),
        ]);
        $this->proposal->load('modules.features');
    }

    public function updateFeature(int $featureId, string $value): void
    {
        ProposalFeature::where('id', $featureId)->update(['feature_text' => $value]);
        $this->proposal->load('modules.features');
    }

    public function deleteFeature(int $featureId): void
    {
        ProposalFeature::where('id', $featureId)->delete();
        $this->proposal->load('modules.features');
    }

    public function loadSystemTemplate(): void
    {
        $this->proposal->modules()->each(fn ($m) => $m->delete());
        $this->proposal->update(['template_type' => 'system']);

        $modules = [
            [
                'title'       => 'Stock Management System',
                'description' => 'A comprehensive system to track, manage, and control inventory operations efficiently.',
                'features'    => [
                    'Product Management (Add, update, view, and delete products)',
                    'Category & Supplier Management',
                    'Stock In/Out Tracking',
                    'Real-Time Stock Level Monitoring',
                    'Batch & Expiry Date Management (if required)',
                    'Low Stock Alerts & Notifications',
                    'Import/Export Product Data (CSV/Excel)',
                    'Generate Stock Reports',
                ],
            ],
            [
                'title'       => 'Billing & Sales Module',
                'description' => 'A streamlined billing system for fast and accurate sales operations.',
                'features'    => [
                    'Quick & Custom Billing Interface',
                    'Customer Management (Add, update, view customers)',
                    'Discounts, Offers & Tax Calculation',
                    'Multiple Payment Methods (Cash, Card, Bank Transfer, etc.)',
                    'Customer Due Management',
                    'RC Management',
                    'Print/Receipts & Invoices (Custom Branding)',
                    'Daily, Weekly & Monthly Sales Reports',
                ],
            ],
            [
                'title'       => 'Reports Module',
                'description' => 'A centralized module that generates comprehensive analytical and operational reports.',
                'features'    => [
                    'Real-time Data Analytics Dashboard',
                    'Stock, Billing, and Expense Summary Reports',
                    'Sales vs Expense Comparison Reports',
                    'Profit/Loss Reports',
                    'Exportable Reports (PDF/Excel)',
                    'User-based Access for Report Viewing',
                ],
            ],
        ];

        $this->seedModules($modules);
    }

    public function loadWebsiteTemplate(): void
    {
        $this->proposal->modules()->each(fn ($m) => $m->delete());
        $this->proposal->update(['template_type' => 'website']);

        $modules = [
            [
                'title'       => 'Website Design & Development',
                'description' => 'Modern, responsive website built to your brand specifications.',
                'features'    => [
                    'Responsive Design',
                    'Homepage & About Page',
                    'Services/Products Page',
                    'Contact Form',
                    'SEO Optimization',
                    'Mobile-Friendly Layout',
                ],
            ],
            [
                'title'       => 'Content Management System',
                'description' => 'Easily manage all website content from one admin panel.',
                'features'    => [
                    'Admin Dashboard',
                    'Page Content Editor',
                    'Media Upload & Management',
                    'Blog/News Section (optional)',
                ],
            ],
            [
                'title'       => 'Hosting & Deployment',
                'description' => 'Reliable hosting and deployment with full optimization.',
                'features'    => [
                    'Domain Setup',
                    'SSL Certificate',
                    'Website Hosting',
                    'Performance Optimization',
                ],
            ],
        ];

        $this->seedModules($modules);
    }

    private function seedModules(array $modules): void
    {
        foreach ($modules as $i => $m) {
            $module = ProposalModule::create([
                'proposal_id' => $this->proposal->id,
                'title'       => $m['title'],
                'description' => $m['description'],
                'sort_order'  => $i,
            ]);
            foreach ($m['features'] as $j => $featureText) {
                ProposalFeature::create([
                    'module_id'    => $module->id,
                    'feature_text' => $featureText,
                    'sort_order'   => $j,
                ]);
            }
        }
        $this->proposal->load('modules.features');
    }

    // ── Step 4: Quotation items ──────────────────────────────────────────
    public function addQuotationItem(): void
    {
        ProposalQuotationItem::create([
            'proposal_id' => $this->proposal->id,
            'description' => 'New Item',
            'amount'      => 0,
            'sort_order'  => $this->proposal->quotationItems()->count(),
        ]);
        $this->proposal->load('quotationItems');
    }

    public function updateQuotationItem(int $itemId, string $field, $value): void
    {
        if (in_array($field, ['description', 'amount'], true)) {
            ProposalQuotationItem::where('id', $itemId)
                ->where('proposal_id', $this->proposal->id)
                ->update([$field => $value]);
        }
        $this->proposal->load('quotationItems');
    }

    public function deleteQuotationItem(int $itemId): void
    {
        ProposalQuotationItem::where('id', $itemId)
            ->where('proposal_id', $this->proposal->id)
            ->delete();
        $this->proposal->load('quotationItems');
    }

    public function toggleEditMode(): void
    {
        $this->editMode = ! $this->editMode;
    }

    // ── Step 5: Status / approve ─────────────────────────────────────────
    public function changeStatus(string $status): void
    {
        if (!in_array($status, ['draft', 'sent', 'approved', 'cancelled'], true)) {
            return;
        }
        $this->proposal->update(['status' => $status]);
        $this->statusValue = $status;

        if ($status === 'approved') {
            $this->proposal->project->update(['status' => 'approved']);
        } elseif ($status === 'sent') {
            if ($this->proposal->project->status === 'draft') {
                $this->proposal->project->update(['status' => 'pending']);
            }
        } elseif ($status === 'cancelled') {
            $this->proposal->project->update(['status' => 'cancelled']);
        }

        // After approving/cancelling, switch to detail view
        if (in_array($status, ['approved', 'cancelled'], true)) {
            $this->editMode = false;
        }

        $this->proposal->refresh();
        session()->flash('message', 'Proposal status updated to ' . ucfirst($status) . '.');
    }

    public function createInvoice()
    {
        $this->saveCurrentStep();

        $invoice = Invoice::create([
            'project_id'  => $this->proposal->project_id,
            'proposal_id' => $this->proposal->id,
            'date'        => now()->toDateString(),
            'due_date'    => now()->addDays(14)->toDateString(),
            'subtotal'    => 0,
            'discount'    => $this->proposal->discount,
            'total'       => 0,
            'amount_paid' => 0,
            'balance_due' => 0,
            'notes'       => 'An optional monthly support and maintenance plan is available for LKR ' . number_format($this->proposal->monthly_support_fee, 0) . '/month.',
            'terms'       => 'Please settle ' . $this->proposal->payment_advance_pct . '% of the project amount as an advance. Once the payment is received, we will begin quality development to support your digital growth.',
            'status'      => 'draft',
        ]);

        // Create invoice items from quotation items
        foreach ($this->proposal->quotationItems as $qi) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => $qi->description,
                'quantity'    => 1,
                'rate'        => $qi->amount,
                'amount'      => $qi->amount,
            ]);
        }

        // If hosting enabled and no quotation rows yet, add baseline rows
        if ($this->proposal->quotationItems->isEmpty()) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => 'Total System Development Cost',
                'quantity'    => 1,
                'rate'        => $this->proposal->total_system_cost,
                'amount'      => $this->proposal->total_system_cost,
            ]);
            if ($this->proposal->hosting_enabled) {
                $hosting = $this->proposal->totalHostingCost();
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => 'Hosting & Domain (' . $this->proposal->hosting_months . ' months)',
                    'quantity'    => 1,
                    'rate'        => $hosting,
                    'amount'      => $hosting,
                ]);
            }
        }

        $invoice->recalculate();

        return redirect()->route('invoices.show', $invoice);
    }
}
