<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProposalModule extends Model
{
    protected $fillable = [
        'proposal_id',
        'title',
        'description',
        'sort_order',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(ProposalFeature::class, 'module_id')->orderBy('sort_order');
    }
}
