<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained('studios');
            $table->foreignId('teacher_id')->constrained('teachers');
            $table->string('month_year'); // YYYY-MM
            $table->integer('amount');
            $table->enum('payment_method', ['manual', 'mercadopago'])->default('manual');
            $table->string('receipt_path')->nullable();
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_payments');
    }
};
