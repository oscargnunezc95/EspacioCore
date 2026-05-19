<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->string('mp_preapproval_id')->nullable()->comment('ID de la suscripción en Mercado Pago');
            $table->string('subscription_status')->default('free')->comment('free, pro, elite, past_due');
            $table->timestamp('subscription_ends_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->dropColumn(['mp_preapproval_id', 'subscription_status', 'subscription_ends_at']);
        });
    }
};