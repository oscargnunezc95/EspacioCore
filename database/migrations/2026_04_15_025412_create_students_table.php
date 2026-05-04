<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
        {
            Schema::create('students', function (Blueprint $table) {
                $table->id();
                // 1. Relación con el Estudio (Dueño de la ficha)
                $table->foreignId('studio_id')->constrained()->cascadeOnDelete();
                
                // 2. Relación con el Usuario Global (El "Match" permanente, opcional al inicio)
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                
                // 3. Datos Personales (Internacional y Flexible)
                $table->string('first_name'); // Único campo de texto obligatorio
                $table->string('last_name')->nullable();
                
                // 4. Contacto (Usaremos el email para el match automático)
                $table->string('email'); 
                $table->string('phone')->nullable();
                
                $table->boolean('is_guest')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};