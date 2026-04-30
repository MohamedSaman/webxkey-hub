<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalFeature extends Model
{
    protected $fillable = [
        'module_id',
        'feature_text',
        'sort_order',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(ProposalModule::class, 'module_id');
    }
}
