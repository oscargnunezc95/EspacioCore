<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Renombra `is_monthly` a `scope` en la tabla `promotions`.
     *
     * Razón: `is_monthly` era un antipatrón de nombres engañosos. No tiene nada
     * que ver con meses; en realidad controla si la promoción aplica por Taller
     * ('workshop') o a nivel de Estudio Global ('global').
     *
     * Mapeo de datos existentes:
     *   is_monthly = 1 (true)  → scope = 'workshop'
     *   is_monthly = 0 (false) → scope = 'global'
     */
    public function up(): void
    {
        // 1. Agregar la nueva columna
        Schema::table('promotions', function (Blueprint $table) {
            $table->string('scope', 20)
                  ->default('workshop')
                  ->after('additional_price')
                  ->comment("'workshop' = evalúa por taller | 'global' = evalúa a nivel estudio");
        });

        // 2. Migrar datos existentes
        DB::statement("UPDATE promotions SET scope = CASE WHEN is_monthly = 1 THEN 'workshop' ELSE 'global' END");

        // 3. Eliminar la columna obsoleta
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('is_monthly');
        });
    }

    public function down(): void
    {
        // Revertir: restaurar is_monthly (boolean) y eliminar scope
        Schema::table('promotions', function (Blueprint $table) {
            $table->boolean('is_monthly')
                  ->default(false)
                  ->after('additional_price');
        });

        // Migrar datos de vuelta
        DB::statement("UPDATE promotions SET is_monthly = CASE WHEN scope = 'workshop' THEN 1 ELSE 0 END");

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
    }
};
