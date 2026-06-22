<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseStockOpnamesTable extends Migration
{
    public function up()
    {
        Schema::create('warehouse_stock_opnames', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Opname_Number');
            $table->unsignedBigInteger('Warehouse_ID');
            $table->date('Opname_Date');
            $table->decimal('System_Qty', 15, 2);
            $table->decimal('Physical_Qty', 15, 2)->default(0);
            $table->decimal('Difference', 15, 2)->default(0);
            $table->enum('Status', ['DRAFT', 'COUNTED', 'ADJUSTED', 'CLOSED', 'CLOSED_PENDING'])->default('DRAFT');
            $table->text('Notes')->nullable();
            $table->string('Counted_By')->nullable();
            $table->string('Approved_By')->nullable();
            $table->timestamp('Created_At')->useCurrent();
            $table->timestamp('Updated_At')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warehouse_stock_opnames');
    }
}
