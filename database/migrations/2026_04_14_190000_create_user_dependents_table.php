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
        Schema::create('user_dependents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // El apoderado
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('national_id'); // RUT del familiar — obligatorio
            $table->foreignId('country_id')->constrained('countries');
            $table->string('relationship')->nullable(); // 'Hijo/a', 'Pareja', etc.
            $table->string('status')->default('active');
            $table->timestamps();

            // Mismo familiar no puede estar duplicado para el mismo usuario y país
            $table->unique(['user_id', 'national_id', 'country_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_dependents');
    }
};
