<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->string('mp_store_id')->nullable()->after('mp_user_id')
                ->comment('MercadoPago Store ID for static QR payments');
            $table->string('mp_external_pos_id')->nullable()->after('mp_store_id')
                ->comment('MercadoPago POS external_id for static QR');
            $table->string('mp_pos_qr_url')->nullable()->after('mp_external_pos_id')
                ->comment('Static QR code image URL from MercadoPago POS');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->dropColumn(['mp_store_id', 'mp_external_pos_id', 'mp_pos_qr_url']);
        });
    }
};
