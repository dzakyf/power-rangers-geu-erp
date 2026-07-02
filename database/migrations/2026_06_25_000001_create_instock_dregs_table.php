<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstockDregsTable extends Migration
{
    public function up()
    {
        Schema::create('instock_dregs', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->date('Instock_Date');
            $table->decimal('Quantity', 12, 2);
            $table->text('Notes')->nullable();
            $table->timestamp('Created_At')->nullable();
            $table->timestamp('Updated_At')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('instock_dregs');
    }
}
