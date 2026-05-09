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
        'is_monthly',
        'introductory_price',
        'is_introductory_active',
    ];

    protected $casts = [
        'is_monthly' => 'boolean',
        'is_introductory_active' => 'boolean',
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }
}
