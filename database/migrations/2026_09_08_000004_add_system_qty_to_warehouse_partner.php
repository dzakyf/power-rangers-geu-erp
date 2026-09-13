<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSystemQtyToWarehousePartner extends Migration
{
    public function up()
    {
        Schema::table('warehouse_partner', function (Blueprint $table) {
            $table->decimal('System_Qty', 15, 2)->nullable()->after('capacity');
            $table->timestamp('System_Qty_Updated_At')->nullable()->after('System_Qty');
        });
    }

    public function down()
    {
        Schema::table('warehouse_partner', function (Blueprint $table) {
            $table->dropColumn(['System_Qty', 'System_Qty_Updated_At']);
        });
    }
}
