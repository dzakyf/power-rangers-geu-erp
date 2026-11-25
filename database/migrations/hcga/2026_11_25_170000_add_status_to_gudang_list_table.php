<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToGudangListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('gudang_list') && !Schema::hasColumn('gudang_list', 'Status')) {
            Schema::table('gudang_list', function (Blueprint $table) {
                $table->string('Status', 20)->default('ACTIVE')->after('City');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('gudang_list') && Schema::hasColumn('gudang_list', 'Status')) {
            Schema::table('gudang_list', function (Blueprint $table) {
                $table->dropColumn('Status');
            });
        }
    }
}
