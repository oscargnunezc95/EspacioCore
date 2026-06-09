<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'platform_fee_percent',
        'capacity_limit',
        'max_billing_cycles',
        'is_active',
        'features',
    ];

    public function studios()
    {
        return $this->hasMany(Studio::class);
    }
}