<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear tabla de Planes
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: "Founder Elite"
            $table->string('slug')->unique(); // Ej: "founder-elite"
            $table->integer('price'); 
            $table->decimal('platform_fee_percent', 5, 2)->default(0); 
            $table->integer('capacity_limit')->nullable(); // null = infinito
            $table->integer('max_billing_cycles')->nullable(); // null = infinito
            $table->boolean('is_active')->default(true);
            $table->text('features')->nullable();
            $table->timestamps();
        });

        // 2. Actualizar tabla Studios
        Schema::table('studios', function (Blueprint $table) {
            $table->foreignId('subscription_plan_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('subscription_plans')
                  ->nullOnDelete();
                  
            $table->integer('billing_cycles_count')->default(0)->after('subscription_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->dropForeign(['subscription_plan_id']);
            $table->dropColumn(['subscription_plan_id', 'billing_cycles_count']);
        });
        
        Schema::dropIfExists('subscription_plans');
    }
};