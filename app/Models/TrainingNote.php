<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToStudio;

class TrainingNote extends Model
{
    use HasFactory, BelongsToStudio;

    protected $fillable = [
        'title',
        'content',
        'training_date',
    ];

    // Opcional, pero recomendado: Castear la fecha para poder usar métodos de Carbon
    // directamente en tus vistas de Blade (ej: $note->training_date->format('d/m/Y'))
    protected $casts = [
        'training_date' => 'date',
    ];
}