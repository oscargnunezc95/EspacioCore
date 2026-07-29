<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Almacena el valor base del piso mínimo mensual (minimum_floor)
 * que se aplica a todas las facturas de estudios.
 *
 * Solo existe UNA fila en esta tabla. Se accede con:
 *   StandarMinimumFloor::current()  → int
 *   StandarMinimumFloor::updateValue(int $value)  → void
 */
class StandarMinimumFloor extends Model
{
    protected $table = 'standar_minimum_floor';

    protected $fillable = ['value'];

    protected $casts = [
        'value' => 'integer',
    ];

    /**
     * Devuelve el valor actual del piso mínimo estándar.
     * Si la tabla está vacía (improbable), retorna 15000 como fallback seguro.
     */
    public static function current(): int
    {
        return (int) (static::first()?->value ?? 15000);
    }

    /**
     * Actualiza el valor del piso mínimo estándar.
     * Crea el registro si no existe (idempotente).
     */
    public static function updateValue(int $value): void
    {
        $record = static::first();

        if ($record) {
            $record->update(['value' => $value]);
        } else {
            static::create(['value' => $value]);
        }
    }
}
