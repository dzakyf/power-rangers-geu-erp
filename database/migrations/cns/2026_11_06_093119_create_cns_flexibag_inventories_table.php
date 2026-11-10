<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsFlexibagInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cns_flexibag_inventories')) {
            Schema::create('cns_flexibag_inventories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->date('transaction_date');
                $table->unsignedBigInteger('vessel_schedule_id')->nullable()->index();
                $table->string('reference_no', 100)->nullable()->index();
                $table->enum('type', ['IN', 'OUT'])->default('OUT')->index();
                $table->decimal('qty_in', 12, 2)->default(0);
                $table->decimal('qty_out', 12, 2)->default(0);
                $table->decimal('current_balance', 12, 2)->default(0);
                $table->string('notes', 255)->nullable();
                $table->string('created_by', 150)->nullable();
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
        Schema::dropIfExists('cns_flexibag_inventories');
    }
}
