<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Refactorización Temporal de Promociones.
     *
     * Elimina la columna `scope` (antes `is_monthly`), un antipatrón redundante,
     * y la reemplaza con reglas temporales explícitas que otorgan autonomía
     * a cada promoción:
     *
     *   - validity_months: ventana de vigencia (0 = sin límite / vitalicio).
     *   - validity_type: 'calendar' (estricto por mes) o 'rolling' (ventana continua).
     *   - allows_retroactive: si permite upgrade retroactivo usando pagos pasados.
     */
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            // 1. Agregar columnas de control temporal
            $table->unsignedSmallInteger('validity_months')
                  ->default(1)
                  ->after('additional_price')
                  ->comment('0 = sin límite / vitalicio');

            $table->string('validity_type', 20)
                  ->default('calendar')
                  ->after('validity_months')
                  ->comment("'calendar' = estricto por mes calendario; 'rolling' = ventana continua por días/meses");

            $table->boolean('allows_retroactive')
                  ->default(true)
                  ->after('validity_type')
                  ->comment('Permite usar clases pagadas en el pasado para completar y activar este combo');

            // 2. Eliminar la columna obsoleta scope (heredera del antipatrón is_monthly)
            $table->dropColumn('scope');
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            // Revertir: restaurar scope y eliminar las nuevas columnas
            $table->string('scope', 20)
                  ->default('workshop')
                  ->after('additional_price')
                  ->comment("'workshop' = evalúa por taller | 'global' = evalúa a nivel estudio");

            $table->dropColumn(['validity_months', 'validity_type', 'allows_retroactive']);
        });
    }
};
