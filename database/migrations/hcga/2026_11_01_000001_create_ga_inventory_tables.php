<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaInventoryTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ga_stock_ledger')) {
            Schema::create('ga_stock_ledger', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->date('Movement_Date')->index();
                $table->string('Movement_Type', 30);
                $table->string('Product_Code', 60);
                $table->string('Location', 50)->default('');
                $table->string('WH_ID', 50)->default('');
                $table->string('Bin', 50)->nullable();
                $table->decimal('Qty_In', 18, 4)->default(0);
                $table->decimal('Qty_Out', 18, 4)->default(0);
                $table->decimal('Unit_Cost', 18, 4)->default(0);
                $table->decimal('Running_Qty', 18, 4)->default(0);
                $table->decimal('Running_Value', 18, 4)->default(0);
                $table->decimal('Avg_Cost', 18, 4)->default(0);
                $table->string('Source_Type', 30)->nullable();
                $table->string('Source_ID', 100)->nullable();
                $table->string('Source_Line_ID', 100)->nullable();
                $table->string('Posting_Key', 191)->unique();
                $table->string('Department', 100)->nullable();
                $table->string('Cost_Center', 50)->nullable();
                $table->string('Reason', 500)->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->index(['Product_Code', 'Location', 'WH_ID', 'Movement_Date'], 'ga_stock_ledger_balance_idx');
            });
        }

        if (!Schema::hasTable('ga_item_minimum_stock')) {
            Schema::create('ga_item_minimum_stock', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Product_Code', 60);
                $table->string('Location', 50)->default('');
                $table->string('WH_ID', 50)->default('');
                $table->decimal('Min_Qty', 18, 4)->default(0);
                $table->decimal('Target_Qty', 18, 4)->default(0);
                $table->tinyInteger('Is_Active')->default(1);
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();
                $table->unique(['Product_Code', 'Location', 'WH_ID'], 'ga_item_minimum_stock_unique');
                $table->index('Product_Code');
            });
        }

        if (!Schema::hasTable('ga_stock_period')) {
            Schema::create('ga_stock_period', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Period', 7)->unique();
                $table->string('Status', 20)->default('OPEN')->index();
                $table->timestamp('Locked_At')->nullable();
                $table->string('Locked_By', 100)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ga_stock_ledger');
        Schema::dropIfExists('ga_item_minimum_stock');
        Schema::dropIfExists('ga_stock_period');
    }
}