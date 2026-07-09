<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByToSettlementTable extends Migration
{
    public function up()
    {
        Schema::table('settlement', function (Blueprint $table) {
            $table->string('Created_By', 100)->nullable()->after('Notes');
        });
    }

    public function down()
    {
        Schema::table('settlement', function (Blueprint $table) {
            $table->dropColumn('Created_By');
        });
    }
}
