<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proposal extends Model
{
    protected $fillable = [
        'project_id',
        'date',
        'subject',
        'intro_text',
        'template_type',
        'hosting_enabled',
        'hosting_price',
        'hosting_months',
        'payment_advance_pct',
        'payment_middle_pct',
        'payment_final_pct',
        'monthly_support_fee',
        'additional_feature_rate',
        'discount',
        'total_system_cost',
        'status',
        'notes',
    ];

    protected $casts = [
        'date'                    => 'date',
        'hosting_enabled'         => 'boolean',
        'hosting_price'           => 'decimal:2',
        'hosting_months'          => 'integer',
        'payment_advance_pct'     => 'integer',
        'payment_middle_pct'      => 'integer',
        'payment_final_pct'       => 'integer',
        'monthly_support_fee'     => 'decimal:2',
        'additional_feature_rate' => 'decimal:2',
        'discount'                => 'decimal:2',
        'total_system_cost'       => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(ProposalModule::class)->orderBy('sort_order');
    }

    public function quotationItems(): HasMany
    {
        return $this->hasMany(ProposalQuotationItem::class)->orderBy('sort_order');
    }

    public function totalHostingCost(): float
    {
        if (!$this->hosting_enabled) {
            return 0;
        }
        return (float) $this->hosting_price * (int) $this->hosting_months;
    }

    public function grandTotal(): float
    {
        return (float) $this->total_system_cost + $this->totalHostingCost() - (float) $this->discount;
    }

    public function advanceAmount(): float
    {
        return $this->grandTotal() * ((int) $this->payment_advance_pct) / 100;
    }

    public function middleAmount(): float
    {
        return $this->grandTotal() * ((int) $this->payment_middle_pct) / 100;
    }

    public function finalAmount(): float
    {
        return $this->grandTotal() * ((int) $this->payment_final_pct) / 100;
    }
}
