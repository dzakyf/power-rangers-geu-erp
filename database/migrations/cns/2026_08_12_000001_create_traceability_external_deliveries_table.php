<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraceabilityExternalDeliveriesTable extends Migration
{
    public function up()
    {
        Schema::create('traceability_external_deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('supplier_name', 255);
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->date('delivery_date');
            $table->decimal('total_quantity_kg', 12, 2);
            $table->string('api_reference', 100)->nullable()->unique();
            $table->enum('status', ['PENDING', 'LOCKED', 'AUDITED'])->default('PENDING');
            $table->text('delivery_documentation')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('traceability_external_deliveries');
    }
}
