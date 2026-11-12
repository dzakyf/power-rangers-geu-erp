<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsUpcomingInboundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cns_upcoming_inbounds')) {
            Schema::create('cns_upcoming_inbounds', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('inbound_no', 100)->unique();
                $table->string('supplier_name', 255);
                $table->string('origin', 255)->nullable();
                $table->dateTime('eta_date');
                $table->string('driver_name', 150)->nullable();
                $table->string('vehicle_plate', 50)->nullable();
                $table->decimal('estimated_qty', 12, 2)->default(0);
                $table->string('target_tank', 100)->nullable();
                $table->string('product_type', 100)->default('UCO');
                $table->enum('status', ['SCHEDULED', 'IN_TRANSIT', 'ARRIVED', 'COMPLETED', 'CANCELLED'])->default('SCHEDULED')->index();
                $table->text('notes')->nullable();
                $table->string('api_source', 100)->nullable();
                $table->json('api_payload')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cns_upcoming_inbounds');
    }
}
