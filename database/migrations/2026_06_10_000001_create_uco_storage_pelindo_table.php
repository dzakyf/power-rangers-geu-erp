<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUcoStoragePelindoTable extends Migration
{
    public function up()
    {
        Schema::create('uco_storage_pelindo', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->enum('Type', ['inbound', 'outbound']);
            $table->decimal('Quantity', 12, 2);
            $table->decimal('Quantity_PTU', 12, 2);
            $table->decimal('Quantity_Sounding', 12, 2)->nullable();
            $table->string('Created_By');
            $table->date('Transaction_Date');
            $table->string('Reference_Number')->nullable();
            $table->text('Notes')->nullable();
            $table->timestamp('Created_At')->nullable();
            $table->timestamp('Updated_At')->nullable();

            $table->index('Transaction_Date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('uco_storage_pelindo');
    }
}
