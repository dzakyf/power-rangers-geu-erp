<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsMasterProductCnsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('master_product_cns')) {
            return; // already provisioned on the live database
        }

        Schema::create('master_product_cns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description', 155);

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
