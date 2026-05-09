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
        Schema::table('teachers', function (Blueprint $table) {
            $table->renameColumn('name', 'first_name');
            $table->string('last_name')->nullable()->after('name'); // Se pondrá después de first_name
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('last_name');
            $table->renameColumn('first_name', 'name');
        });
    }
};
