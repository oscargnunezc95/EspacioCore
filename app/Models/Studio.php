<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Promotion;

class Studio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'country_id',
        'subdomain',
        'logo_path',
        'icon_path',
        'address',
        'latitude',
        'longitude',
        'city',
        'region',
        'country',
        'description',
        'social_link',
        // --- CREDENCIALES DE MERCADO PAGO CONNECT ---
        'mp_access_token',
        'mp_refresh_token',
        'mp_user_id',

        'subscription_status',
        'mp_preapproval_id',
        'subscription_expires_at',
        'subscription_plan_id',
        'next_plan_id',
        'billing_cycles_count'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function workshops()
    {
        return $this->hasMany(Workshop::class);
    }
    
    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }
    
    public function classSessions()
    {
        return $this->hasMany(ClassSession::class);
    }

    // ─── ACCESSORS DE MONEDA ───────────────────────────────────────
    // Navegan a través de $this->user->country para obtener la moneda
    // del país de residencia del dueño del estudio.
    // Incluyen fallback seguro (CLP / $) en caso de relación nula.

    public function getCurrencyCodeAttribute(): string
    {
        return $this->user?->country?->currency_code ?? 'CLP';
    }

    public function getCurrencySymbolAttribute(): string
    {
        return $this->user?->country?->currency_symbol ?? '$';
    }
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function nextPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'next_plan_id');
    }
    /**
     * El método "booted" del modelo.
     * Se ejecuta cuando el modelo ha terminado de cargar.
     */
    protected static function booted()
    {
        // Interceptamos la creación ANTES de que se guarde en la BD
        static::creating(function ($studio) {

            // Si no se le ha asignado un plan explícitamente...
            if (empty($studio->subscription_plan_id)) {

                // Buscamos el ID del plan 'free'
                $freePlan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();

                if ($freePlan) {
                    $studio->subscription_plan_id = $freePlan->id;
                    $studio->subscription_status = 'free'; // Aseguramos el status

                    // Le damos 100 años de duración al plan free para que no expire nunca,
                    // o lo dejamos en null si tu lógica maneja null como "infinito".
                    $studio->subscription_expires_at = now()->addYears(100);
                } else {
                    // Medida de seguridad: Si alguien borra el plan free por error, avisamos.
                    \Illuminate\Support\Facades\Log::warning('Se intentó crear un estudio, pero el Plan Free no existe en la base de datos.');
                }
            }
        });
    }

    /**
     * Motor de Ciclo de Vida y Morosidad.
     *
     * Evalúa y ejecuta transiciones de plan por expiración natural o por
     * superar el límite de 5 días de gracia en morosidad.
     *
     * Diseñado para ser llamado tanto desde el comando Artisan programado
     * como desde cualquier otro punto de la aplicación que necesite
     * forzar una evaluación del ciclo de vida.
     */
    public function checkAndManageLifecycle(): void
    {
        // ─── REGLA 1: TRANSICIÓN DE PLANES VENCIDOS (Suscripción expirada naturalmente) ───
        if (
            $this->subscription_expires_at
            && $this->subscription_expires_at->isPast()
            && !in_array($this->subscription_status, ['past_due', 'free'], true)
        ) {
            if ($this->next_plan_id) {
                $nextPlan = \App\Models\SubscriptionPlan::find($this->next_plan_id);

                if ($nextPlan && $nextPlan->slug === 'free') {
                    // Downgrade diferido a plan gratuito
                    $this->update([
                        'subscription_plan_id'    => $this->next_plan_id,
                        'next_plan_id'            => null,
                        'subscription_status'     => 'free',
                        'subscription_expires_at' => now()->addYears(100),
                        'mp_preapproval_id'       => null,
                    ]);
                } else {
                    // Upgrade diferido a plan de pago
                    $this->update([
                        'subscription_plan_id' => $this->next_plan_id,
                        'next_plan_id'         => null,
                        'subscription_status'  => 'pending_payment',
                    ]);
                }
            } else {
                // Sin next_plan_id: safety net → plan gratuito
                $freePlan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();
                $this->update([
                    'subscription_plan_id'    => $freePlan?->id,
                    'subscription_status'     => 'free',
                    'subscription_expires_at' => now()->addYears(100),
                    'mp_preapproval_id'       => null,
                    'next_plan_id'            => null,
                ]);
            }
        }

        // ─── REGLA 2: LÍMITE DE MOROSIDAD (Los 5 días de gracia) ───
        if ($this->subscription_status === 'past_due') {
            $daysPastDue = now()->diffInDays($this->subscription_expires_at);

            if ($daysPastDue > 5) {
                // 1. Cancelación inversa por API de Mercado Pago
                if ($this->mp_preapproval_id) {
                    try {
                        app(\App\Services\MercadoPagoService::class)->cancelPreapproval($this->mp_preapproval_id);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error(
                            "Error cancelando MP por morosidad: " . $e->getMessage()
                        );
                    }
                }

                // 2. Downgrade forzado a plan gratuito
                $freePlan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();
                $this->update([
                    'subscription_plan_id'    => $freePlan?->id,
                    'subscription_status'     => 'free',
                    'subscription_expires_at' => now()->addYears(100),
                    'mp_preapproval_id'       => null,
                    'next_plan_id'            => null,
                ]);

                // 3. Auditoría
                \Illuminate\Support\Facades\Log::info(
                    "Estudio ID {$this->id} movido a plan gratuito por superar los 5 días de morosidad."
                );
            }
        }
    }
}