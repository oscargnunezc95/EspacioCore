<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToStudio;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Services\DocumentService;
use App\Models\Country;

class Student extends Model
{
    use HasFactory, SoftDeletes, BelongsToStudio;

    protected $fillable = [
        'user_id',          // Llave foránea del Usuario Global
        'first_name',       // Obligatorio
        'last_name',        // Opcional
        'email',            
        'national_id', 
        'phone',
        'is_guest',
        'country_id'
    ];

    // MAGIA 1: Mantener compatibilidad con el frontend ($student->name)
    public function getNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // --- RELACIONES ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function workshops()
    {
        return $this->belongsToMany(Workshop::class, 'student_workshop')
                    ->withTimestamps();
    }
    
    // AQUÍ ESTÁ LA OPTIMIZACIÓN DEL CARRITO
    public function classSessions()
    {
        return $this->belongsToMany(ClassSession::class, 'class_session_student')
                    ->withPivot('payment_status') // <-- Vínculo mágico para saber si pagó
                    ->withTimestamps();
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // MAGIA 2: Sincronización Automática (Lado del Estudio)
    // Registro temporal para transportar el escenario de saving → saved
    // sin contaminar $attributes (que se escribirían como columnas SQL).
    protected static array $scenarioRegistry = [];

    protected static function booted()
    {
        static::saving(function ($student) {
            // SOLO actuamos si NO trae user_id explícito y viene con RUT válido
            if (empty($student->user_id) && $student->isDirty('national_id') && !empty($student->national_id)) {
                
                // ESCENARIO A: ¿Existe un Usuario Titular Global con este RUT?
                $user = User::where('national_id', $student->national_id)
                            ->where('country_id', $student->country_id)
                            ->first();
                
                if ($user) {
                    $student->user_id = $user->id;
                    self::$scenarioRegistry[spl_object_id($student)] = 'A';
                    return;
                }

                // ESCENARIO C: ¿Existe como Familiar/Dependiente de alguien?
                // PRINCIPIO: "Separar la Identidad de la Tutoría"
                // El user_id en students SIEMPRE es la persona que ASISTE a la clase.
                // El apoderado administra vía user_dependents, NO vía user_id.
                // Si el dependiente tiene su propia cuenta, Escenario A ya lo habría vinculado.
                // Si no, user_id queda null — el apoderado lo gestiona por el puente user_dependents.
                $dependent = UserDependent::where('national_id', $student->national_id)
                                          ->where('country_id', $student->country_id)
                                          ->first();
                
                if ($dependent) {
                    // NO asignamos el user_id del apoderado. La ficha pertenece al dependiente.
                    self::$scenarioRegistry[spl_object_id($student)] = 'C';
                    return;
                }

                // ESCENARIO B: No existe. Es un alumno local.
                self::$scenarioRegistry[spl_object_id($student)] = 'B';
            }
        });

        static::saved(function ($student) {
            $key = spl_object_id($student);
            $scenario = self::$scenarioRegistry[$key] ?? null;

            // Despachamos correos en segundo plano SOLO si acaba de ocurrir la magia
            if ($scenario) {
                
                if ($scenario === 'A') {
                    $user = User::find($student->user_id);
                    if ($user) {
                        $user->notify(new \App\Notifications\LocalProfileLinkedNotification($student));
                        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\LocalProfileLinkedMail($student));
                    }
                } 
                elseif ($scenario === 'C') {
                    // Buscar al apoderado vía UserDependent (ya no está en user_id del student)
                    $dependentLink = UserDependent::where('national_id', $student->national_id)
                        ->where('country_id', $student->country_id)
                        ->first();
                    if ($dependentLink) {
                        $parentUser = User::find($dependentLink->user_id);
                        if ($parentUser) {
                            $parentUser->notify(new \App\Notifications\DependentProfileLinkedNotification($student));
                            \Illuminate\Support\Facades\Mail::to($parentUser->email)->send(new \App\Mail\DependentProfileLinkedMail($student));
                        }
                    }
                }
                
                // Limpiamos el registro para no acumular basura
                unset(self::$scenarioRegistry[$key]);
            }
        });
    }

    public function getFormattedNationalIdAttribute()
    {
        // 100% Dinámico
        $countryCode = $this->country ? $this->country->code : 'CL'; 

        return DocumentService::format($this->national_id, $countryCode);
    }

    protected function nationalId(): Attribute
    {
        return Attribute::make(
            set: function ($value, $attributes) {
                if (empty($value)) return null;

                $countryCode = null;
                if (!empty($attributes['country_id'])) {
                    $countryCode = Country::find($attributes['country_id'])?->code;
                }

                return DocumentService::standardize($value, $countryCode);
            }
        );
    }
}