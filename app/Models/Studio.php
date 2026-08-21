<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 🚀 1. Importación obligatoria
use App\Models\Promotion;

class Studio extends Model
{
    // 🚀 2. Inyección del trait
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'country_id',
        'subdomain',
        'logo_path',
        'icon_path',
        'cover_path',
        'address',
        'latitude',
        'longitude',
        'city',
        'region',
        'country',
        'description',
        'email',
        'whatsapp',
        'instagram_url',
        'tiktok_url',
        'youtube_url',
        'mp_access_token',
        'mp_refresh_token',
        'mp_user_id',
        'mp_store_id',
        'mp_external_pos_id',
        'mp_pos_qr_url',
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

    public function invoices()
    {
        return $this->hasMany(StudioInvoice::class);
    }

    public function getCurrencyCodeAttribute(): string
    {
        return $this->user?->country?->currency_code ?? 'CLP';
    }

    public function getCurrencySymbolAttribute(): string
    {
        return $this->user?->country?->currency_symbol ?? '$';
    }

    public function isFounderActive(): bool
    {
        return $this->is_founder && $this->founder_cycles_remaining > 0;
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_path ?? $this->logo_path;
    }

    public function getAvatarImageUrlAttribute(): ?string
    {
        return $this->icon_path ?? $this->logo_path;
    }

    // 🚀 3. Métodos financieros requeridos por el evento deleting
    public function hasUnpaidPlatformInvoices(): bool
    {
        // Verifica si existe alguna factura emitida por la plataforma en estado pendiente
        return $this->invoices()->where('status', 'unpaid')->exists();
    }

    public function currentMonthPendingDebt(): float
    {
        // 1. Resolvemos tu servicio de facturación a través del contenedor de Laravel
        $billingService = app(\App\Services\BillingService::class);
        
        // 2. Obtenemos la proyección del mes en curso usando tu lógica Floor-Capped
        $projection = $billingService->getCurrentMonthProjection($this);
        
        // 3. Retornamos el total proyectado que el estudio le debe a la plataforma
        return (float) $projection['projected_total'];
    }

    protected static function booted()
    {
        static::deleting(function ($studio) {
            if ($studio->hasUnpaidPlatformInvoices() || $studio->currentMonthPendingDebt() > 0) {
                throw new \Exception('Operación denegada: El estudio tiene facturas o deudas pendientes con la plataforma. Debes saldarlas antes de cerrar la cuenta.');
            }
        });
    }
}