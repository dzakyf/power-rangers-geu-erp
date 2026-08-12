<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraceabilityExternalPooFragmentsTable extends Migration
{
    public function up()
    {
        Schema::create('traceability_external_poo_fragments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('traceability_external_delivery_id');
            $table->integer('fragment_sequence');
            $table->string('poo_name', 255);
            $table->decimal('quantity_kg', 12, 2);
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->text('full_address')->nullable();
            $table->string('lat', 50)->nullable();
            $table->string('lng', 50)->nullable();
            $table->text('oil_documentation')->nullable();
            $table->string('wo_code', 50)->nullable();
            $table->date('wo_date')->nullable();
            $table->string('wo_status', 50)->nullable();
            $table->string('po_code', 50)->nullable();
            $table->date('po_date')->nullable();
            $table->decimal('po_qty', 12, 2)->nullable();
            $table->string('po_status', 50)->nullable();
            $table->string('do_code', 50)->nullable();
            $table->date('do_date')->nullable();
            $table->string('do_status', 50)->nullable();
            $table->timestamps();

            $table->index('traceability_external_delivery_id', 'tep_fk_delivery_idx');
            $table->foreign('traceability_external_delivery_id', 'tep_fk_delivery_foreign')
                ->references('id')
                ->on('traceability_external_deliveries')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('traceability_external_poo_fragments');
    }
}
