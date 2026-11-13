<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductTypeToCnsMasterKemasanTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cns_master_kemasan') && !Schema::hasColumn('cns_master_kemasan', 'product_type')) {
            Schema::table('cns_master_kemasan', function (Blueprint $table) {
                $table->string('product_type', 50)->default('Kemasan')->after('Name');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('cns_master_kemasan') && Schema::hasColumn('cns_master_kemasan', 'product_type')) {
            Schema::table('cns_master_kemasan', function (Blueprint $table) {
                $table->dropColumn('product_type');
            });
        }
    }
}
