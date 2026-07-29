<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migración de "Bolsas de Tiempo" (Time-Bound Packs).
     *
     * Traslada la lógica de vigencia y retroactividad desde la tabla `workshops`
     * hacia cada Pack de Precio (`workshop_prices`), permitiendo configuraciones
     * granulares como:
     *   - Clase Suelta: 1 mes de vigencia, sin retroactividad.
     *   - Pack 4 Clases: 1 mes de vigencia, con retroactividad de 1 mes.
     *   - Bolsa Trimestral (12): 3 meses de vigencia, retroactividad de 3 meses.
     *   - Bolsa Anual / Vitalicia: vigencia 0 (sin límite), retroactividad infinita.
     */
    public function up(): void
    {
        Schema::table('workshop_prices', function (Blueprint $table) {
            // 1. Agregar nuevas columnas de Time-Bound Packs
            $table->unsignedSmallInteger('validity_months')
                  ->default(1)
                  ->after('price')
                  ->comment('0 = sin límite / vitalicio. Ventana deslizante de vigencia del pack.');

            $table->boolean('allows_retroactive')
                  ->default(true)
                  ->after('validity_months')
                  ->comment('Si el pack permite upgrade retroactivo (delta pricing) dentro de su ventana.');

            // 2. Eliminar la columna obsoleta is_monthly
            //    (su lógica es reemplazada por validity_months + allows_retroactive)
            $table->dropColumn('is_monthly');
        });
    }

    public function down(): void
    {
        Schema::table('workshop_prices', function (Blueprint $table) {
            // Revertir: restaurar is_monthly y eliminar las nuevas columnas
            $table->boolean('is_monthly')
                  ->default(false)
                  ->after('price')
                  ->comment('Aplica la regla del primer mes?');

            $table->dropColumn(['validity_months', 'allows_retroactive']);
        });
    }
};
