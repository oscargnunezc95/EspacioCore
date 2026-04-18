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
            $table->string('name');
            $table->string('color')->default('blue');
            $table->boolean('is_single_class')->default(false);
            $table->date('specific_date')->nullable();
            $table->string('trainer')->nullable();
            $table->string('trainer_phone')->nullable();
            $table->integer('repeat_day')->nullable();
            $table->time('start_time')->nullable();
            $table->text('payment_info')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};