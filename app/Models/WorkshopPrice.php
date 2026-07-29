<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'class_count',
        'price',
        'validity_months',
        'validity_type',
        'allows_retroactive',
        'introductory_price',
        'is_introductory_active',
    ];

    protected $casts = [
        'validity_months' => 'integer',
        'allows_retroactive' => 'boolean',
        'is_introductory_active' => 'boolean',
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }
}
