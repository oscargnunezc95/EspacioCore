<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('dependent_decision_pending')->default(false)->after('country_id');
            $table->unsignedBigInteger('dependent_decision_owner_id')->nullable()->after('dependent_decision_pending');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dependent_decision_pending', 'dependent_decision_owner_id']);
        });
    }
};
