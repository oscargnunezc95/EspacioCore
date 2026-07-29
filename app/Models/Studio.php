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
        'cover_path',      // <-- NUEVO: Foto horizontal de portada/card
        'address',
        'latitude',
        'longitude',
        'city',
        'region',
        'country',
        'description',
        'email',           // <-- NUEVO: Correo de contacto del estudio
        'whatsapp',        // <-- NUEVO: WhatsApp de contacto del estudio
        'instagram_url',   // <-- NUEVO: Enlace específico de Instagram
        'tiktok_url',      // <-- NUEVO: Enlace específico de TikTok
        'youtube_url',     // <-- NUEVO: Enlace específico de YouTube


        // --- CREDENCIALES DE MERCADO PAGO CONNECT ---
        'mp_access_token',
        'mp_refresh_token',
        'mp_user_id',
        'mp_store_id',
        'mp_external_pos_id',
        'mp_pos_qr_url',
        // --- CONTROL DE BENEFICIO FOUNDER ---
        'is_founder',
        'founder_cycles_remaining',
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

    /**
     * Facturas mensuales del estudio (sistema Floor-Capped Usage Pricing).
     */
    public function invoices()
    {
        return $this->hasMany(StudioInvoice::class);
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

    /**
     * Determina si el beneficio Founder está activo en este momento.
     */
    public function isFounderActive(): bool
    {
        return $this->is_founder && $this->founder_cycles_remaining > 0;
    }

    // ─── ACCESSORS DE IMÁGENES ───────────────────────────────────────
    // Jerarquía de fallback para portada y avatar, evitando lógica
    // repetitiva en las vistas.

    /**
     * Imagen de portada con fallback al logo.
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_path ?? $this->logo_path;
    }

    /**
     * Avatar / ícono con fallback al logo.
     */
    public function getAvatarImageUrlAttribute(): ?string
    {
        return $this->icon_path ?? $this->logo_path;
    }
}
