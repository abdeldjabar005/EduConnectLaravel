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
        Schema::table('join_requests', function (Blueprint $table) {
            $table->dropForeign(['student_id']); // Drop foreign key constraint
            $table->dropColumn('student_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('join_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->after('id');
            $table->foreign('student_id')->references('id')->on('students');

        });
    }
};
