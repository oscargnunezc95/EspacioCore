<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToStudio;

class Workshop extends Model
{
    use HasFactory, BelongsToStudio;

    protected $fillable = [
        'studio_id',
        'teacher_id',
        'name',
        'discipline_id',
        'image_path',
        'target_audience',
        'color',
        'start_time',
        'max_students',
        'payment_info',
        'is_single_class',
        'repeat_days',
        'specific_date',
        'use_main_location',
        'address',
        'latitude',
        'longitude',
        'city',
        'region',
        'country',
        'room_location',
    ];

    protected $casts = [
        'is_single_class' => 'boolean',
        'use_main_location' => 'boolean',
        'repeat_days' => 'array', // Convierte el JSON de la BD a un array de PHP y viceversa
        'specific_date' => 'date',
        'start_time' => 'datetime:H:i', // Para que Blade lo formatee fácil si usas Carbon
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }
    
    public function prices()
    {
        return $this->hasMany(WorkshopPrice::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_workshop')
                    ->withTimestamps();
    }

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class);
    }

    // ---> ESTA ES LA RELACIÓN QUE FALTABA <---
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }
    // En App\Models\Workshop.php

    /**
     * Obtiene la imagen del taller. Si no tiene, devuelve el logo del estudio.
     * Si el estudio tampoco tiene, devuelve una imagen por defecto.    
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        if ($this->studio && $this->studio->logo_path) {
            return asset('storage/' . $this->studio->logo_path);
        }

        // Imagen por defecto genérica si nadie subió nada
        return asset('images/default-workshop.jpg'); 
    }
    public function schedules()
{
    return $this->hasMany(WorkshopSchedule::class);
}
}