<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            // currency_code ya existe en la migración original (2026_04_14_000000)
            // Agregamos solo currency_symbol
            if (!Schema::hasColumn('countries', 'currency_symbol')) {
                $table->string('currency_symbol', 5)->default('$')->after('currency_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('currency_symbol');
        });
    }
};
