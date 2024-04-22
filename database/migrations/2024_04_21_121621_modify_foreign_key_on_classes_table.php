<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyForeignKeyOnClassesTable extends Migration
{
    public function up()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['school_id']);

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->foreign('school_id')->references('id')->on('schools');
        });
    }
}
