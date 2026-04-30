<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalQuotationItem extends Model
{
    protected $fillable = [
        'proposal_id',
        'description',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
