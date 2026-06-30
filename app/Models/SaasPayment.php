<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaasPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'studio_id',
        'mp_payment_id',
        'amount',
        'status',
    ];

    /**
     * El estudio al que pertenece este cobro SaaS.
     */
    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }
}
