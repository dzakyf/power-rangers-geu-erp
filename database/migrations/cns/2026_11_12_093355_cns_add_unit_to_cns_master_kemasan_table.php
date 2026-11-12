<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CnsAddUnitToCnsMasterKemasanTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cns_master_kemasan') && !Schema::hasColumn('cns_master_kemasan', 'Unit')) {
            Schema::table('cns_master_kemasan', function (Blueprint $table) {
                $table->string('Unit')->before('Price');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('cns_master_kemasan') && Schema::hasColumn('cns_master_kemasan', 'Unit')) {
            Schema::table('cns_master_kemasan', function (Blueprint $table) {
                $table->dropColumn('Unit');
            });
        }
    }
}
