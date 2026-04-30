<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Invoice Preview')]
class InvoicePreview extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->load(['items', 'project.client', 'payments']);
    }

    public function render()
    {
        return view('livewire.invoices.invoice-preview');
    }
}
