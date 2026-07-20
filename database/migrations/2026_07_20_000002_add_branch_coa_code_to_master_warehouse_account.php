<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBranchCoaCodeToMasterWarehouseAccount extends Migration
{
    public function up()
    {
        Schema::table('master_warehouse_account', function (Blueprint $table) {
            $table->string('branch_coa_code', 25)->nullable()->after('account_no_coa');
            $table->index('branch_coa_code');
        });
    }

    public function down()
    {
        Schema::table('master_warehouse_account', function (Blueprint $table) {
            $table->dropColumn('branch_coa_code');
        });
    }
}
