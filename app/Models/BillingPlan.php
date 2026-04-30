<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPlan extends Model
{
    protected $fillable = ['name', 'price'];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
