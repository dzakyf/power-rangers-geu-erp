<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceToCnsMasterKemasanTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cns_master_kemasan') && !Schema::hasColumn('cns_master_kemasan', 'Price')) {
            Schema::table('cns_master_kemasan', function (Blueprint $table) {
                $table->decimal('Price', 15, 2)->nullable()->default(0)->after('Max_Stock');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('cns_master_kemasan') && Schema::hasColumn('cns_master_kemasan', 'Price')) {
            Schema::table('cns_master_kemasan', function (Blueprint $table) {
                $table->dropColumn('Price');
            });
        }
    }
}
