<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToStudio;

class Payment extends Model
{
    use HasFactory, BelongsToStudio;

    protected $fillable = [
        'student_id',
        'workshop_id',
        'payment_type',
        'amount',
        'receipt_path',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function classSessions()
    {
        return $this->belongsToMany(ClassSession::class, 'class_session_payment')
                    ->withPivot('student_id')
                    ->withTimestamps();
    }
}