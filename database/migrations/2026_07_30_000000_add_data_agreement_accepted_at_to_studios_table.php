<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna de aceptación del acuerdo de tratamiento de datos.
     * Escudo legal B2B: registra el momento exacto en que el estudio
     * acepta su responsabilidad sobre los datos de terceros.
     */
    public function up(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->timestamp('data_agreement_accepted_at')->nullable()->after('founder_cycles_remaining');
        });
    }

    /**
     * Reversa la migración eliminando la columna agregada.
     */
    public function down(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->dropColumn('data_agreement_accepted_at');
        });
    }
};
