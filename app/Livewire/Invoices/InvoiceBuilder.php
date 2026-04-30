<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProjectPayment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Invoice Builder')]
class InvoiceBuilder extends Component
{
    public Invoice $invoice;

    public string $date = '';
    public string $due_date = '';
    public float $discount = 0;
    public string $notes = '';
    public string $terms = '';

    // Inline payment form
    public bool $showPaymentForm = false;
    public float $paymentAmount = 0;
    public string $paymentType = 'advance';
    public string $paymentDate = '';
    public string $paymentMethod = 'Bank Transfer';
    public string $paymentReference = '';

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->load(['items', 'payments', 'project.client', 'proposal']);
        $this->date     = $invoice->date?->toDateString() ?? now()->toDateString();
        $this->due_date = $invoice->due_date?->toDateString() ?? '';
        $this->discount = (float) $invoice->discount;
        $this->notes    = $invoice->notes ?? '';
        $this->terms    = $invoice->terms ?? '';

        $this->paymentDate = now()->toDateString();
    }

    public function render()
    {
        return view('livewire.invoices.invoice-builder');
    }

    public function saveHeader(): void
    {
        $this->invoice->update([
            'date'     => $this->date ?: null,
            'due_date' => $this->due_date ?: null,
            'discount' => $this->discount,
            'notes'    => $this->notes,
            'terms'    => $this->terms,
        ]);
        $this->invoice->recalculate();
        $this->invoice->refresh();
        session()->flash('message', 'Invoice details saved.');
    }

    // Items CRUD
    public function addItem(): void
    {
        InvoiceItem::create([
            'invoice_id'  => $this->invoice->id,
            'description' => 'New Item',
            'quantity'    => 1,
            'rate'        => 0,
            'amount'      => 0,
        ]);
        $this->invoice->load('items');
        $this->invoice->recalculate();
    }

    public function updateItem(int $itemId, string $field, $value): void
    {
        $item = InvoiceItem::where('id', $itemId)
            ->where('invoice_id', $this->invoice->id)
            ->firstOrFail();

        if ($field === 'description') {
            $item->description = (string) $value;
        } elseif ($field === 'quantity') {
            $item->quantity = (int) $value;
            $item->amount = $item->quantity * (float) $item->rate;
        } elseif ($field === 'rate') {
            $item->rate = (float) $value;
            $item->amount = (int) $item->quantity * $item->rate;
        }
        $item->save();
        $this->invoice->load('items');
        $this->invoice->recalculate();
        $this->invoice->refresh();
    }

    public function deleteItem(int $itemId): void
    {
        InvoiceItem::where('id', $itemId)
            ->where('invoice_id', $this->invoice->id)
            ->delete();
        $this->invoice->load('items');
        $this->invoice->recalculate();
        $this->invoice->refresh();
    }

    // Status
    public function setStatus(string $status): void
    {
        if (!in_array($status, ['draft', 'sent', 'partial', 'paid'], true)) {
            return;
        }
        $this->invoice->update(['status' => $status]);
        $this->invoice->refresh();
        session()->flash('message', 'Invoice status set to ' . ucfirst($status));
    }

    // Payments
    public function openPayment(): void
    {
        $this->paymentAmount = (float) $this->invoice->balance_due;
        $this->paymentType   = $this->invoice->payments()->count() === 0 ? 'advance' : 'middle';
        $this->paymentDate   = now()->toDateString();
        $this->paymentMethod = 'Bank Transfer';
        $this->paymentReference = '';
        $this->showPaymentForm = true;
    }

    public function recordPayment(): void
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentType'   => 'required|in:advance,middle,final,other',
            'paymentDate'   => 'required|date',
        ]);

        ProjectPayment::create([
            'project_id'   => $this->invoice->project_id,
            'invoice_id'   => $this->invoice->id,
            'amount'       => $this->paymentAmount,
            'payment_type' => $this->paymentType,
            'payment_date' => $this->paymentDate,
            'method'       => $this->paymentMethod ?: null,
            'reference'    => $this->paymentReference ?: null,
        ]);

        $this->invoice->load('payments');
        $this->invoice->recalculate();
        $this->invoice->refresh();

        // Project status transitions
        $project = $this->invoice->project;
        if ($this->paymentType === 'advance' && in_array($project->status, ['approved', 'pending', 'draft'], true)) {
            $project->update(['status' => 'ongoing']);
            session()->flash('message', 'Advance recorded. Project is now Ongoing.');
        } elseif ((float) $this->invoice->balance_due <= 0) {
            $project->update(['status' => 'completed']);
            session()->flash('message', 'Final payment recorded. Project is now Completed.');
        } else {
            session()->flash('message', 'Payment recorded.');
        }

        $this->showPaymentForm = false;
        $this->reset(['paymentAmount', 'paymentType', 'paymentReference']);
    }

    public function deletePayment(int $paymentId): void
    {
        ProjectPayment::where('id', $paymentId)
            ->where('invoice_id', $this->invoice->id)
            ->delete();
        $this->invoice->load('payments');
        $this->invoice->recalculate();
        $this->invoice->refresh();
    }
}
