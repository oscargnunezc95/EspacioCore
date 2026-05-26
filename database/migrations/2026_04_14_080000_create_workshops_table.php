<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('studio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('discipline_id')->nullable()->constrained('disciplines')->nullOnDelete();

            // Datos Basicos
            $table->string('name');
            $table->string('target_audience')->default('adults');
            $table->string('color')->default('blue');
            $table->time('start_time')->nullable();
            $table->integer('max_students')->nullable();
            $table->text('payment_info')->nullable();

            // Contenido y media
            $table->text('description')->nullable();
            $table->string('promo_video_url')->nullable();
            $table->string('image_path')->nullable()->after('name');

            // Logica de Calendario
            $table->boolean('is_single_class')->default(false);
            $table->json('repeat_days')->nullable();
            $table->date('specific_date')->nullable();

            // Logica de Ubicacion (Sede principal o personalizada)
            $table->boolean('use_main_location')->default(true);
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('city')->nullable()->index();
            $table->string('region')->nullable();
            $table->string('country')->nullable();
            $table->string('room_location')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};
