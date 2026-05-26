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
            $table->string('national_id')->nullable(); // RUT del familiar
            $table->foreignId('country_id')->constrained();
            $table->string('relationship')->nullable(); // 'Hijo/a', 'Pareja', etc.
            $table->timestamps();
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
