<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // En SQLite, al agregar una llave foránea a una tabla que ya tiene datos, 
        // es obligatorio ponerle nullable() o un valor por defecto.
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('studio_id')->nullable()->constrained()->onDelete('cascade');
        });

        Schema::table('training_notes', function (Blueprint $table) {
            $table->foreignId('studio_id')->nullable()->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['studio_id']);
            $table->dropColumn('studio_id');
        });

        Schema::table('training_notes', function (Blueprint $table) {
            $table->dropForeign(['studio_id']);
            $table->dropColumn('studio_id');
        });
    }
};