<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // Ej: "Chile"
            $table->string('code', 2);         // Ej: "CL"
            $table->string('tax_id_label');    // Ej: "RUT"
            $table->string('tax_id_regex')->nullable(); // Para validaciones futuras
            $table->string('currency_code', 3); // Ej: "CLP"
            $table->string('currency_symbol', 5)->default('$');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
