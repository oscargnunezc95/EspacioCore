<?php

// app/Models/Payment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    // Permite guardar estos datos masivamente
    protected $fillable = [
        'student_id', 
        'workshop_id', 
        'payment_type', 
        'amount', 
        'receipt_path' // Opcional, por si sube la foto del comprobante
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }
    // Agrega esto dentro de la clase Payment
    public function classSessions()
    {
        return $this->belongsToMany(ClassSession::class, 'class_session_payment')
                    ->withPivot('student_id')
                    ->withTimestamps();
    }
}