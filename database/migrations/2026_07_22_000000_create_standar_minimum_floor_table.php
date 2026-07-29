<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Crea la tabla standar_minimum_floor que almacena el valor base
     * del piso mínimo mensual aplicable a todos los estudios.
     *
     * Este valor reemplaza el hardcode de $15.000 en BillingService
     * y permite al superadmin ajustarlo sin desplegar código.
     */
    public function up(): void
    {
        Schema::create('standar_minimum_floor', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('value')->default(15000)
                  ->comment('Valor base del piso mínimo mensual en la moneda local.');
            $table->timestamps();
        });

        // Insertar el registro por defecto
        DB::table('standar_minimum_floor')->insert([
            'value'      => 15000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('standar_minimum_floor');
    }
};
