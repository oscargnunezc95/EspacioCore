<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabla de Áreas Generales (ej: Circo, Baile)
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 2. Tabla de Disciplinas Específicas (ej: Telas pertenece a Circo)
        Schema::create('disciplines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            
            // Evitamos que exista dos veces "Telas" dentro de "Circo"
            $table->unique(['area_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('disciplines');
        Schema::dropIfExists('areas');
    }
};