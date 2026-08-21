<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_session_student', function (Blueprint $table) {
            $table->unsignedBigInteger('workshop_price_id')->nullable()->after('payment_status');
            
            $table->foreign('workshop_price_id')
                  ->references('id')
                  ->on('workshop_prices')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('class_session_student', function (Blueprint $table) {
            $table->dropForeign(['workshop_price_id']);
            $table->dropColumn('workshop_price_id');
        });
    }
};