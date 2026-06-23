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
        // Tabla de reglas
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained()->cascadeOnDelete();
            
            $table->string('name'); // Ej: "Pack 2 Talleres" o "Acro + Flex"
            $table->enum('type', ['specific_combo', 'additional_discount']); 
            
            // Si es combo específico, guardamos el precio total del pack
            $table->integer('total_price')->nullable(); 
            $table->integer('class_count')->nullable();
            
            // Si es descuento por taller adicional, guardamos el costo de los extras
            $table->integer('additional_price')->nullable();
            $table->boolean('is_monthly')->default(false);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabla pivote para los combos específicos
        Schema::create('promotion_workshop_price', function (Blueprint $table) {
        $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
        // IMPORTANTE: Ahora apuntamos al ID del precio/pack
        $table->foreignId('workshop_price_id')->constrained()->cascadeOnDelete(); 
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
