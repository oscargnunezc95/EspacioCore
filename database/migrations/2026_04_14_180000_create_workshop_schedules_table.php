<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Nueva tabla para horarios dinámicos
        Schema::create('workshop_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            
            // 0: Domingo, 1: Lunes... 6: Sábado
            $table->unsignedTinyInteger('day_of_week'); 
            $table->time('start_time');
            $table->integer('max_students')->nullable();
            $table->timestamps();
        });

        // 2. FK workshop_schedule_id en class_sessions (jerarquía Workshop -> Schedule -> Session)
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->foreignId('workshop_schedule_id')
                  ->nullable()
                  ->after('workshop_id')
                  ->constrained('workshop_schedules')
                  ->nullOnDelete();
        });

        // 3. Limpieza de la tabla workshops (Quitamos lo que ya no sirve)
        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn('repeat_days');
        });
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workshop_schedule_id');
        });

        Schema::dropIfExists('workshop_schedules');
        Schema::table('workshops', function (Blueprint $table) {
            $table->time('start_time')->nullable();
            $table->json('repeat_days')->nullable();
            $table->date('specific_date')->nullable();
            $table->boolean('is_single_class')->default(false);
        });
    }
};