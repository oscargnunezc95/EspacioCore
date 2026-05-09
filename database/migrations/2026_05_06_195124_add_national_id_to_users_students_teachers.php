<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla Users: Agregamos national_id (Debe ser único a nivel global)
        Schema::table('users', function (Blueprint $table) {
            $table->string('national_id')->nullable()->unique()->after('email');
        });

        // 2. Tabla Students: Agregamos national_id y quitamos la obligación del email
        Schema::table('students', function (Blueprint $table) {
            $table->string('national_id')->nullable()->after('user_id');
            $table->string('email')->nullable()->change(); // El email ya no es obligatorio
            
            // Garantizamos que no se repita el mismo alumno en el MISMO estudio
            $table->unique(['studio_id', 'national_id']);
        });

        // 3. Tabla Teachers: Igual que Students
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('national_id')->nullable()->after('user_id');
            $table->string('email')->nullable()->change();
            
            $table->unique(['studio_id', 'national_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('national_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['studio_id', 'national_id']);
            $table->dropColumn('national_id');
            // Nota: SQLite a veces tiene problemas revirtiendo ->change(), 
            // pero como es entorno local de desarrollo, el up() es lo vital.
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique(['studio_id', 'national_id']);
            $table->dropColumn('national_id');
        });
    }
};