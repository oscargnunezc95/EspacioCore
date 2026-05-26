<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->default(1)->constrained('countries');

            // Datos Basicos
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->string('icon_path')->nullable()->after('logo_path');
            $table->string('subdomain')->unique();

            // Datos Geograficos (Sede Principal)
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Datos Desglosados para Filtros Rapidos
            $table->string('city')->nullable()->index();
            $table->string('region')->nullable();
            $table->string('country')->nullable();

            // Perfil publico
            $table->text('description')->nullable();
            $table->string('social_link')->nullable();

            // Suscripcion SaaS
            $table->string('mp_preapproval_id')->nullable()->comment('ID de la suscripcion en Mercado Pago');
            $table->string('subscription_status')->default('free')->comment('free, pro, elite, past_due');
            $table->timestamp('subscription_ends_at')->nullable();

            // Mercado Pago OAuth
            $table->string('mp_access_token')->nullable();
            $table->string('mp_refresh_token')->nullable();
            $table->string('mp_user_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studios');
    }
};
