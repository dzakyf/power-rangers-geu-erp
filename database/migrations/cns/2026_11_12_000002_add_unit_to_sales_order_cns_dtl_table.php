<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitToSalesOrderCnsDtlTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sales_order_cns_dtl') && !Schema::hasColumn('sales_order_cns_dtl', 'unit')) {
            Schema::table('sales_order_cns_dtl', function (Blueprint $table) {
                $table->string('unit', 50)->nullable()->after('qty');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('sales_order_cns_dtl') && Schema::hasColumn('sales_order_cns_dtl', 'unit')) {
            Schema::table('sales_order_cns_dtl', function (Blueprint $table) {
                $table->dropColumn('unit');
            });
        }
    }
}
