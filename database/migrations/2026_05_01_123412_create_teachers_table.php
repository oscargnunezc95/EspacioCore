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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            // Relación con el estudio (Multi-tenant)
            $table->foreignId('studio_id')->constrained()->cascadeOnDelete();
            
            // Relación con el usuario global (Nullable porque puede no haberse registrado aún)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Datos del profesor
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes(); // Requerido porque usamos SoftDeletes en el modelo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
