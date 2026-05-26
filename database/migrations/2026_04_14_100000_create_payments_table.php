<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->string('payment_type');
            $table->string('payment_method')->default('transferencia')->after('payment_type');
            $table->integer('amount');
            $table->string('receipt_path')->nullable();
            $table->string('mp_payment_id')->nullable()->after('receipt_path');
            $table->string('status')->default('approved')->after('mp_payment_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
