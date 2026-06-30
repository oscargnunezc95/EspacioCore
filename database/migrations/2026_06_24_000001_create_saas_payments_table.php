<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla independiente para los cobros de suscripción SaaS.
     * NO se mezclan con la tabla `payments` de los alumnos.
     */
    public function up(): void
    {
        Schema::create('saas_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained()->cascadeOnDelete();
            $table->string('mp_payment_id')->nullable()->index()
                  ->comment('ID del pago en Mercado Pago. Indexado para búsquedas de idempotencia.');
            $table->decimal('amount', 10, 2)->default(0)
                  ->comment('Monto cobrado en la moneda configurada por el estudio.');
            $table->string('status')->default('approved')
                  ->comment('Estado del pago: approved, refunded, etc.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saas_payments');
    }
};
