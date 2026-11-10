<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsGoodsReceiptsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cns_goods_receipts')) {
            Schema::create('cns_goods_receipts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('gr_no', 50)->unique();
                $table->string('po_no', 50)->index();
                $table->date('receipt_date');
                $table->string('delivery_note_no', 100)->nullable();
                $table->string('driver_name', 100)->nullable();
                $table->string('vehicle_no', 30)->nullable();
                $table->string('received_by', 100)->nullable();
                $table->enum('status', ['DRAFT', 'APPROVED', 'VOID'])->default('DRAFT')->index();
                $table->string('approved_by', 100)->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->string('attachment_path', 255)->nullable();
                $table->text('notes')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cns_goods_receipt_details')) {
            Schema::create('cns_goods_receipt_details', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('goods_receipt_id')->index();
                $table->unsignedInteger('po_dtl_id')->nullable()->index();
                $table->string('item_code', 50)->nullable()->index();
                $table->string('item_name', 255);
                $table->string('category', 100)->nullable();
                $table->string('unit', 50)->default('PCS');
                $table->decimal('qty_ordered', 12, 2)->default(0);
                $table->decimal('qty_received', 12, 2)->default(0);
                $table->string('notes', 255)->nullable();
                $table->timestamps();

                $table->foreign('goods_receipt_id')
                    ->references('id')
                    ->on('cns_goods_receipts')
                    ->onDelete('cascade');
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
        Schema::dropIfExists('cns_goods_receipt_details');
        Schema::dropIfExists('cns_goods_receipts');
    }
}
