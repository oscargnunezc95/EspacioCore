<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Limpieza de la tabla `workshops`.
     *
     * Elimina `allow_retroactive_upgrades` ya que la responsabilidad
     * de la retroactividad ahora es 100% granular del Pack (`workshop_prices`)
     * a través de `allows_retroactive` y `validity_months`.
     */
    public function up(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn('allow_retroactive_upgrades');
        });
    }

    public function down(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            $table->boolean('allow_retroactive_upgrades')
                  ->default(true)
                  ->after('is_single_class');
        });
    }
};
