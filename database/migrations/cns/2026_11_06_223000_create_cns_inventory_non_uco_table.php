<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCnsInventoryNonUcoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cns_inventory_non_uco')) {
            Schema::create('cns_inventory_non_uco', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->date('transaction_date');
                $table->string('category', 100)->nullable()->index();
                $table->string('item_code', 50)->nullable()->index();
                $table->string('item_name', 255)->index();
                $table->string('unit', 50)->default('PCS');
                $table->unsignedBigInteger('vessel_schedule_id')->nullable()->index();
                $table->unsignedBigInteger('goods_receipt_id')->nullable()->index();
                $table->string('reference_no', 100)->nullable()->index();
                $table->enum('type', ['IN', 'OUT'])->default('IN')->index();
                $table->decimal('qty_in', 12, 2)->default(0);
                $table->decimal('qty_out', 12, 2)->default(0);
                $table->decimal('current_balance', 12, 2)->default(0);
                $table->string('notes', 255)->nullable();
                $table->string('created_by', 150)->nullable();
                $table->timestamps();
            });
        }

        // Migrate historical flexibag records if exists
        if (Schema::hasTable('cns_flexibag_inventories')) {
            $existingCount = DB::table('cns_inventory_non_uco')->count();
            if ($existingCount === 0) {
                $flexibagRows = DB::table('cns_flexibag_inventories')->orderBy('id', 'asc')->get();
                foreach ($flexibagRows as $row) {
                    DB::table('cns_inventory_non_uco')->insert([
                        'transaction_date'   => $row->transaction_date,
                        'category'           => 'Flexibag',
                        'item_code'          => null,
                        'item_name'          => 'Flexibag 24KL',
                        'unit'               => 'PCS',
                        'vessel_schedule_id' => $row->vessel_schedule_id,
                        'goods_receipt_id'   => null,
                        'reference_no'       => $row->reference_no,
                        'type'               => $row->type,
                        'qty_in'             => $row->qty_in,
                        'qty_out'            => $row->qty_out,
                        'current_balance'    => $row->current_balance,
                        'notes'              => $row->notes,
                        'created_by'         => $row->created_by,
                        'created_at'         => $row->created_at ?? now(),
                        'updated_at'         => $row->updated_at ?? now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cns_inventory_non_uco');
    }
}
