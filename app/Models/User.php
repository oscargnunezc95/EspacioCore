<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB; // <-- CRÍTICO: Agregamos esto para el DB::raw

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Services\DocumentService;
use App\Models\Country;

#[Fillable(['name', 'email', 'password', 'google_id','national_id','country_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail 
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function studios()
    {
        return $this->hasMany(Studio::class);
    }

    // Relaciones para saber en qué estudios es Profesor y en cuáles es Alumna
    public function teacherProfiles()
    {
        return $this->hasMany(Teacher::class);
    }

    public function studentProfiles()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * ATRIBUTO MÁGICO: Calcula la cantidad de clases futuras impagas.
     * Reemplaza al antiguo getUnpaidClassesCount().
     * En Blade se llama sin paréntesis: auth()->user()->pending_reservations_count
     */
    public function getPendingReservationsCountAttribute(): int
    {
        // 1. Buscamos todas sus fichas de alumna
        $studentIds = \App\Models\Student::withoutGlobalScopes()
            ->where('user_id', $this->id)
            ->pluck('id')
            ->toArray();

        if (empty($studentIds)) {
            return 0;
        }

        // 2. MAGIA DE OPTIMIZACIÓN BLINDADA: Cuenta directa O(1)
        return \App\Models\ClassSession::withoutGlobalScopes()
            ->whereHas('students', function ($query) use ($studentIds) {
                $query->withoutGlobalScopes()
                      ->whereIn('students.id', $studentIds)
                      // 👇 El mismo blindaje explícito que usamos en el CartController 👇
                      ->where('class_session_student.payment_status', 'pending'); 
            })
            ->where('date', '>=', now()->toDateString())
            ->count();
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // LÓGICA DE VINCULACIÓN INVERSA: Cuando el Usuario Global cambia sus datos
    protected static function booted()
    {
        static::saved(function ($user) {
            if ($user->isDirty('national_id') && !empty($user->national_id)) {
                
                // 1. Buscamos y vinculamos Staff
                $teachersUpdated = Teacher::where('national_id', $user->national_id)
                       ->where('country_id', $user->country_id)
                       ->whereNull('user_id') // Solo actualizamos los que estaban huérfanos
                       ->update(['user_id' => $user->id]);

                // 2. Buscamos y vinculamos Fichas de Alumna
                $studentsUpdated = Student::where('national_id', $user->national_id)
                       ->where('country_id', $user->country_id)
                       ->whereNull('user_id')
                       ->update(['user_id' => $user->id]);
                       
                // 3. Si hubo magia, enviamos un aviso unificado
                if ($studentsUpdated > 0 || $teachersUpdated > 0) {
                    // (Opcional) Puedes crear una notificación genérica para este hito
                    // $user->notify(new \App\Notifications\AccountMergedNotification());
                }
            }
        });
    }

    public function getFormattedNationalIdAttribute()
    {
        // Ahora lee dinámicamente el código real del país (ej: 'CL', 'AR', 'OT')
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
    public function dependents()
    {
        return $this->hasMany(UserDependent::class);
    }
}