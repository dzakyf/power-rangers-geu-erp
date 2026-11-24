<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSerialNumberToCnsGoodsReceiptDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cns_goods_receipt_details') && !Schema::hasColumn('cns_goods_receipt_details', 'serial_number')) {
            Schema::table('cns_goods_receipt_details', function (Blueprint $table) {
                $table->text('serial_number')->nullable()->after('notes');
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
        if (Schema::hasTable('cns_goods_receipt_details') && Schema::hasColumn('cns_goods_receipt_details', 'serial_number')) {
            Schema::table('cns_goods_receipt_details', function (Blueprint $table) {
                $table->dropColumn('serial_number');
            });
        }
    }
}
