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
}