<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsPurchaseLogTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('purchase_log')) {
            return; // already provisioned on the live database
        }

        Schema::create('purchase_log', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Location', 20)->nullable();
            $table->string('Task_ID', 30)->nullable();
            $table->string('Category', 20)->nullable();
            $table->string('Sub_Category', 100)->nullable();
            $table->string('Purchase_Type', 100)->nullable();
            $table->string('Transaction', 30)->nullable();
            $table->string('Status', 25)->nullable();
            $table->mediumText('Remark')->nullable();
            $table->timestamp('Date_Trx')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));
            $table->string('User_Trx', 20)->nullable();

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
