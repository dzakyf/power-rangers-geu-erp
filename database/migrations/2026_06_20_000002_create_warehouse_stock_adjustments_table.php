<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseStockAdjustmentsTable extends Migration
{
    public function up()
    {
        Schema::create('warehouse_stock_adjustments', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('Opname_ID');
            $table->unsignedBigInteger('Warehouse_ID');
            $table->enum('Adjustment_Type', ['SURPLUS', 'SHORTAGE']);
            $table->decimal('Qty_Before', 15, 2);
            $table->decimal('Qty_After', 15, 2);
            $table->decimal('Difference', 15, 2);
            $table->text('Reason')->nullable();
            $table->enum('Approval_Status', ['AUTO', 'PENDING', 'APPROVED', 'REJECTED'])->default('AUTO');
            $table->string('Approved_By')->nullable();
            $table->timestamp('Approved_At')->nullable();
            $table->text('Approval_Note')->nullable();
            $table->timestamp('Created_At')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warehouse_stock_adjustments');
    }
}
