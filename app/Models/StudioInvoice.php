<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudioInvoice extends Model
{
    protected $fillable = [
        'studio_id',
        'billing_period',
        'gross_sales',
        'calculated_commission',
        'minimum_floor',
        'founder_savings',
        'total_due',
        'status',
        'due_date',
        'paid_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];

    /**
     * La factura pertenece a un estudio.
     */
    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }

    /**
     * Helpers de estado para legibilidad en vistas y middleware.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    /**
     * Etiqueta humana del estado para la UI.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'paid'     => 'Pagada',
            'pending'  => 'Pendiente',
            'past_due' => 'Vencida',
            default    => 'Desconocido',
        };
    }

    /**
     * Clase CSS de Tailwind para el badge de estado.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'paid'     => 'bg-green-100 text-green-800 border-green-300',
            'pending'  => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'past_due' => 'bg-red-100 text-red-800 border-red-300',
            default    => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }
}
