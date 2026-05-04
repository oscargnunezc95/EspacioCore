<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToStudio;

class Teacher extends Model
{
    use HasFactory, SoftDeletes, BelongsToStudio;

    protected $fillable = [
        'user_id', 
        'name', 
        'email', 
        'phone',
        'is_active'
    ];

    // LÓGICA DE VINCULACIÓN: Cuando el Admin crea/edita al profe
    protected static function booted()
    {
        static::saving(function ($teacher) {
            // Si el correo fue modificado y no está vacío
            if ($teacher->isDirty('email') && !empty($teacher->email)) {
                
                // Buscamos si el profe ya tiene una cuenta global en EspacioCore
                $user = User::where('email', $teacher->email)->first();
                
                // Lo vinculamos si existe. Si no, queda en null esperando a que se registre.
                $teacher->user_id = $user ? $user->id : null;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workshops()
    {
        return $this->hasMany(Workshop::class);
    }
}