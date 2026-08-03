<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContainerIdToWarehousePartnerSuppliesTable extends Migration
{
    public function up()
    {
        Schema::table('warehouse_partner_supplies', function (Blueprint $table) {
            $table->unsignedBigInteger('Container_ID')->nullable()->after('warehouse_task_id');
            $table->index('Container_ID');
        });
    }

    public function down()
    {
        Schema::table('warehouse_partner_supplies', function (Blueprint $table) {
            $table->dropIndex(['Container_ID']);
            $table->dropColumn('Container_ID');
        });
    }
}
