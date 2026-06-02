<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la FK workshop_schedule_id a class_sessions para
     * establecer la jerarquía Workshop -> WorkshopSchedule -> ClassSession.
     * Es nullable + nullOnDelete para preservar sesiones históricas.
     */
    public function up(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->foreignId('workshop_schedule_id')
                  ->nullable()
                  ->after('workshop_id')
                  ->constrained('workshop_schedules')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workshop_schedule_id');
        });
    }
};
