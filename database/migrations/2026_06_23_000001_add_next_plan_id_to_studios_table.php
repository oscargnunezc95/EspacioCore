<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->foreignId('next_plan_id')
                  ->nullable()
                  ->after('subscription_plan_id')
                  ->constrained('subscription_plans')
                  ->nullOnDelete()
                  ->comment('Plan que se activará al finalizar el ciclo de facturación actual. Null si no hay cambio pendiente.');
        });
    }

    public function down(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->dropForeign(['next_plan_id']);
            $table->dropColumn('next_plan_id');
        });
    }
};
