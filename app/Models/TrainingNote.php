<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingNote extends Model
{
    protected $fillable = [
        'title',
        'training_date',
        'content'
    ];

    // Esto asegura que Laravel trate el campo como una fecha de Carbon
    protected $casts = [
        'training_date' => 'date',
    ];
}