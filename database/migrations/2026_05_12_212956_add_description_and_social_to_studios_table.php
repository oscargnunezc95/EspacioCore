<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->string('social_link')->nullable(); // Para el link de Instagram/TikTok
        });
    }

    public function down()
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->dropColumn(['description', 'social_link']);
        });
    }

};
