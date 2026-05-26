<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Creamos la tabla de países
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // Ej: "Chile"
            $table->string('code', 2);         // Ej: "CL"
            $table->string('tax_id_label');    // Ej: "RUT"
            $table->string('tax_id_regex')->nullable(); // Para validaciones futuras
            $table->string('currency_code', 3); // Ej: "CLP"
            $table->timestamps();
        });

        // 3. Vinculamos los Usuarios al País (Por defecto 1: Chile)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('country_id')->default(1)->constrained('countries');
        });

        // 4. Vinculamos los Estudios al País (Por defecto 1: Chile)
        Schema::table('studios', function (Blueprint $table) {
            $table->foreignId('country_id')->default(1)->constrained('countries');
        });
    }

    public function down(): void
    {
        // El rollback en SQLite para columnas foráneas puede ser complejo, 
        // pero esta es la estructura estándar.
        Schema::table('studios', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });

        Schema::dropIfExists('countries');
    }
};