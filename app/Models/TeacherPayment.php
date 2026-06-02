<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToStudio;

class TeacherPayment extends Model
{
    use HasFactory, BelongsToStudio;

    protected $fillable = [
        'studio_id',
        'teacher_id',
        'month_year',
        'amount',
        'payment_method',
        'receipt_path',
        'status',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
