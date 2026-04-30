<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'type',
        'agreement_code',
        'description',
        'status',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function latestProposal()
    {
        return $this->hasOne(Proposal::class)->latestOfMany();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ProjectPayment::class);
    }

    /**
     * Generate a sequential agreement code: WXK + YY + NN (e.g. WXK2601)
     */
    public static function generateAgreementCode(): string
    {
        $year = substr((string) now()->year, -2);
        $prefix = 'WXK' . $year;
        $count = static::where('agreement_code', 'like', $prefix . '%')->count() + 1;

        return $prefix . str_pad((string) $count, 2, '0', STR_PAD_LEFT);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }
}
