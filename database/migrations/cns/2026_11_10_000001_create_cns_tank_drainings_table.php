<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsTankDrainingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cns_tank_drainings')) {
            return;
        }

        Schema::create('cns_tank_drainings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('draining_code', 100)->unique();
            $table->date('draining_date');
            $table->unsignedBigInteger('tank_id');
            $table->string('tank_name', 100);
            $table->decimal('qty', 15, 2);
            $table->string('target_type', 50); // MOVING, PENJUALAN
            $table->string('moving_target_type', 50)->nullable(); // TANK, EXTERNAL
            $table->unsignedBigInteger('moving_target_tank_id')->nullable();
            $table->string('moving_target_tank_name', 100)->nullable();
            $table->string('moving_target_external', 255)->nullable();
            $table->unsignedInteger('sales_order_id')->nullable();
            $table->string('sales_order_no', 150)->nullable();
            $table->text('notes')->nullable();
            $table->string('created_by', 150)->nullable();
            $table->string('updated_by', 150)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tank_id');
            $table->index('draining_date');
            $table->index('target_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cns_tank_drainings');
    }
}
