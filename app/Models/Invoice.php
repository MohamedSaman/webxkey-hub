<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'project_id',
        'proposal_id',
        'invoice_number',
        'date',
        'due_date',
        'subtotal',
        'discount',
        'total',
        'amount_paid',
        'balance_due',
        'notes',
        'terms',
        'status',
    ];

    protected $casts = [
        'date'        => 'date',
        'due_date'    => 'date',
        'subtotal'    => 'decimal:2',
        'discount'    => 'decimal:2',
        'total'       => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                // Tentative — replaced after the row is created.
                $invoice->invoice_number = 'INV-PENDING-' . uniqid();
            }
        });

        static::created(function (Invoice $invoice) {
            $padded = str_pad((string) $invoice->id, 3, '0', STR_PAD_LEFT);
            $invoice->invoice_number = 'INV-' . $padded;
            $invoice->saveQuietly();
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ProjectPayment::class);
    }

    public function recalculate(): void
    {
        $subtotal = $this->items()->sum('amount');
        $this->subtotal = $subtotal;
        $this->total = max(0, $subtotal - (float) $this->discount);
        $this->amount_paid = $this->payments()->sum('amount');
        $this->balance_due = max(0, (float) $this->total - (float) $this->amount_paid);

        if ($this->balance_due <= 0 && $this->amount_paid > 0) {
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partial';
        }

        $this->save();
    }
}
