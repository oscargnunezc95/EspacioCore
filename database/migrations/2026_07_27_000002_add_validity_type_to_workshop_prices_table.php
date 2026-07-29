<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega `validity_type` a `workshop_prices` para controlar el modo de
     * cálculo de la ventana de vigencia de cada Pack.
     *
     * - 'calendar': Estricto por mes calendario (ej. del 1 al 31 del mes).
     *               Ideal para cuotas escolares o mensualidades fijas.
     * - 'rolling':  Ventana flotante continua desde el día de anclaje
     *               (ej. 30 o 90 días exactos). Ideal para bolsas de fitness.
     */
    public function up(): void
    {
        Schema::table('workshop_prices', function (Blueprint $table) {
            $table->string('validity_type', 20)
                  ->default('calendar')
                  ->after('validity_months')
                  ->comment("'calendar' = estricto por mes calendario | 'rolling' = ventana continua flotante");
        });
    }

    public function down(): void
    {
        Schema::table('workshop_prices', function (Blueprint $table) {
            $table->dropColumn('validity_type');
        });
    }
};
