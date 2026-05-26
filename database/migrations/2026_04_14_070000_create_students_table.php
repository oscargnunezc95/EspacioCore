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
            $table->foreignId('studio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries');

            // Datos Personales
            $table->string('first_name');
            $table->string('last_name')->nullable();

            // Identificacion
            $table->string('national_id')->nullable()->after('user_id');

            // Contacto
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->boolean('is_guest')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Garantizamos que no se repita el mismo alumno en el MISMO estudio
            $table->unique(['studio_id', 'national_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
