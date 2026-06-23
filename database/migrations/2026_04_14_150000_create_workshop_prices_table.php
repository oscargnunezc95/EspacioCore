<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshop_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();

            $table->integer('class_count'); // Ej: 4 (clases)
            $table->integer('price'); // Ej: 25000 ($)
            $table->boolean('is_monthly')->default(false); // Aplica la regla del primer mes?
            $table->integer('introductory_price')->nullable();
            $table->boolean('is_introductory_active')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_prices');
    }
};
