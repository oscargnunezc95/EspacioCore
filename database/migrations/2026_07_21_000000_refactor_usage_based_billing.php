<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migración maestra: Refactor del motor de monetización.
     *
     * Elimina el sistema antiguo de suscripción fija (PreApproval MP + Split Payments)
     * y lo reemplaza con Facturación Mensual por Uso (Floor-Capped Usage Pricing).
     */
    public function up(): void
    {
        // ─── 1. LIMPIEZA DE COLUMNAS Y FKs EN studios ────────────────────
        Schema::table('studios', function (Blueprint $table) {
            // Eliminar FKs a subscription_plans (nombres por convención de Laravel)
            $table->dropForeign(['subscription_plan_id']);
            $table->dropForeign(['next_plan_id']);

            // Eliminar columnas del sistema de suscripción antiguo
            $table->dropColumn([
                'subscription_plan_id',
                'next_plan_id',
                'mp_preapproval_id',
                'subscription_status',
                'subscription_expires_at',
                'subscription_ends_at',
                'billing_cycles_count',
            ]);
        });

        // ─── 2. AGREGAR COLUMNAS DE CONTROL DE FUNDADORES ───────────────
        Schema::table('studios', function (Blueprint $table) {
            $table->boolean('is_founder')->default(false)
                  ->after('mp_pos_qr_url')
                  ->comment('Indica si el estudio tiene beneficio Founder activo.');

            $table->integer('founder_cycles_remaining')->default(6)
                  ->after('is_founder')
                  ->comment('Meses restantes del beneficio Founder (techo de $15.000 en comisión).');
        });

        // ─── 3. CREAR TABLA studio_invoices ─────────────────────────────
        Schema::create('studio_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')
                  ->constrained('studios')
                  ->cascadeOnDelete();

            $table->string('billing_period', 7)
                  ->comment('Formato YYYY-MM. Ej: 2026-07');

            $table->unsignedBigInteger('gross_sales')->default(0)
                  ->comment('Ventas brutas válidas del mes (en centavos/unidad mínima).');

            $table->unsignedBigInteger('calculated_commission')->default(0)
                  ->comment('Comisión calculada al 5% sobre gross_sales.');

            $table->unsignedBigInteger('minimum_floor')->default(15000)
                  ->comment('Piso mínimo aplicable al estudio para este período.');

            $table->unsignedBigInteger('founder_savings')->default(0)
                  ->comment('Ahorro absorbido por el beneficio Founder (techo de comisión).');

            $table->unsignedBigInteger('total_due')
                  ->comment('Monto final a cobrar al estudio (piso o comisión, con beneficio Founder aplicado).');

            $table->string('status')->default('pending')
                  ->comment('Estado de la factura: pending, paid, past_due');

            $table->date('due_date')
                  ->comment('Fecha límite de pago (día 5 del mes en curso).');

            $table->timestamp('paid_at')->nullable()
                  ->comment('Momento exacto en que se registró el pago.');

            $table->timestamps();

            // Índice único compuesto: un estudio solo puede tener UNA factura por mes
            $table->unique(['studio_id', 'billing_period'], 'uq_studio_invoice_period');
        });

        // ─── 4. ELIMINAR TABLA subscription_plans (ya sin dependencias) ─
        Schema::dropIfExists('subscription_plans');
    }

    /**
     * Rollback: restaura el esquema anterior.
     *
     * NOTA: Los datos de studio_invoices e is_founder/founder_cycles_remaining
     * se pierden irreversiblemente. Las columnas de suscripción se recrean vacías.
     */
    public function down(): void
    {
        // ─── 1. RECREAR subscription_plans ──────────────────────────────
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('price');
            $table->decimal('platform_fee_percent', 5, 2)->default(0);
            $table->integer('capacity_limit')->nullable();
            $table->integer('max_billing_cycles')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('features')->nullable();
            $table->timestamps();
        });

        // ─── 2. RESTAURAR COLUMNAS DE SUSCRIPCIÓN EN studios ────────────
        Schema::table('studios', function (Blueprint $table) {
            // Primero eliminamos las columnas founder
            $table->dropColumn(['is_founder', 'founder_cycles_remaining']);

            // Recreamos columnas antiguas
            $table->foreignId('subscription_plan_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('subscription_plans')
                  ->nullOnDelete();

            $table->integer('billing_cycles_count')->default(0)
                  ->after('subscription_plan_id');

            $table->foreignId('next_plan_id')
                  ->nullable()
                  ->after('billing_cycles_count')
                  ->constrained('subscription_plans')
                  ->nullOnDelete()
                  ->comment('Plan que se activará al finalizar el ciclo.');

            $table->string('mp_preapproval_id')->nullable()
                  ->after('social_link')
                  ->comment('ID de la suscripción en Mercado Pago');

            $table->string('subscription_status')->default('free')
                  ->after('mp_preapproval_id')
                  ->comment('free, pro, elite, past_due');

            $table->dateTime('subscription_expires_at')->nullable()
                  ->after('subscription_status');

            $table->dateTime('subscription_ends_at')->nullable()
                  ->after('subscription_expires_at');
        });

        // ─── 3. ELIMINAR studio_invoices ────────────────────────────────
        Schema::dropIfExists('studio_invoices');
    }
};
