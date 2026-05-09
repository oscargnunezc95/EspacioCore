<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workshop_prices', function (Blueprint $table) {
            // Agregamos las nuevas columnas justo después de 'is_monthly' para mantener el orden visual en la BD
            $table->integer('introductory_price')->nullable()->after('is_monthly');
            $table->boolean('is_introductory_active')->default(false)->after('introductory_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshop_prices', function (Blueprint $table) {
            // Si haces rollback, eliminamos solo estas dos columnas
            $table->dropColumn(['introductory_price', 'is_introductory_active']);
        });
    }
};