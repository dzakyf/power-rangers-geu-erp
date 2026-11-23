<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterWarehouseAccountTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('master_warehouse_account')) {
            return;
        }

        Schema::create('master_warehouse_account', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_no');
            $table->string('account_name', 150);
            $table->integer('group_account');
            $table->integer('account_no_coa')->nullable();
            $table->string('branch_coa_code', 25)->nullable()->index();
            $table->string('type', 50);
            $table->string('created_by', 150);
            $table->dateTime('created_date')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_warehouse_account');
    }
}
