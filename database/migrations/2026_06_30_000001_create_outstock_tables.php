<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutstockTables extends Migration
{
    public function up()
    {
        Schema::create('outstock', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Code');
            $table->date('Date');
            $table->unsignedBigInteger('Warehouse_Origin');
            $table->unsignedBigInteger('Warehouse_Destination')->nullable();
            $table->unsignedBigInteger('Fleet_ID')->nullable();
            $table->string('Vehicle_Category')->nullable();
            $table->string('Status')->default('DRAFT');
            $table->text('Notes')->nullable();
            $table->timestamp('Created_At')->nullable();
            $table->timestamp('Updated_At')->nullable();
        });

        Schema::create('outstock_loading', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('Outstock_ID');
            $table->unsignedBigInteger('Container_ID');
            $table->date('Date');
            $table->decimal('System_Qty', 12, 2)->default(0);
            $table->decimal('Actual_Qty', 12, 2)->default(0);
            $table->string('Photo_Path')->nullable();
            $table->text('Notes')->nullable();
            $table->timestamp('Created_At')->nullable();
        });

        Schema::create('outstock_handover', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('Outstock_ID');
            $table->date('Date');
            $table->decimal('Actual_Qty', 12, 2)->default(0);
            $table->string('Destination')->nullable();
            $table->string('Photo_Path')->nullable();
            $table->text('Notes')->nullable();
            $table->timestamp('Created_At')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('outstock_handover');
        Schema::dropIfExists('outstock_loading');
        Schema::dropIfExists('outstock');
    }
}
