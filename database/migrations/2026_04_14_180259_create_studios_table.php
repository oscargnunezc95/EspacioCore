<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studios', function (Blueprint $table) {
            $table->id();
            // Asumiendo que un estudio pertenece a un usuario administrador
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); 
            
            // Datos Básicos
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->string('subdomain')->unique();
            
            // Datos Geográficos (Sede Principal)
            $table->string('address')->nullable();
            // 10, 8 y 11, 8 es el estándar exacto para precisión GPS de Google Maps
            $table->decimal('latitude', 10, 8)->nullable(); 
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Datos Desglosados para Filtros Rápidos
            $table->string('city')->nullable()->index(); // Indexado porque será muy usado en búsquedas
            $table->string('region')->nullable();
            $table->string('country')->nullable();

            
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studios');
    }
};