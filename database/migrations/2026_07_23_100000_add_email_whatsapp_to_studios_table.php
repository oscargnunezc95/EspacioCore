<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega los campos de contacto: correo electrónico y WhatsApp.
     */
    public function up(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->string('email')->nullable()->after('description');
            $table->string('whatsapp')->nullable()->after('email');
        });
    }

    /**
     * Reversa la migración eliminando las columnas agregadas.
     */
    public function down(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->dropColumn(['email', 'whatsapp']);
        });
    }
};
