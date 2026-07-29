<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones agregando la portada y redes sociales.
     */
    public function up(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            // Foto de portada/banner apaisado (16:9)
            $table->string('cover_path')->nullable()->after('icon_path');

            // Enlaces directos a las redes sociales del estudio
            $table->string('instagram_url')->nullable()->after('description');
            $table->string('tiktok_url')->nullable()->after('instagram_url');
            $table->string('youtube_url')->nullable()->after('tiktok_url');
        });
    }

    /**
     * Reversa la migración eliminando las columnas agregadas (Rollback limpio).
     */
    public function down(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->dropColumn([
                'cover_path',
                'instagram_url',
                'tiktok_url',
                'youtube_url',
            ]);
        });
    }
};