<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('class_session_student', function (Blueprint $table) {
            // Por defecto, toda pre-inscripción nace como 'pending' (carrito)
            $table->string('payment_status', 20)->default('pending')->after('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_session_student', function (Blueprint $table) {
            //
        });
    }
};
