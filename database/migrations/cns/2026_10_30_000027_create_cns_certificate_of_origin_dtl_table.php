<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsCertificateOfOriginDtlTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('certificate_of_origin_dtl')) {
            return; // already provisioned on the live database
        }

        Schema::create('certificate_of_origin_dtl', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('COO_No', 100);
            $table->string('Container_No', 100);
            $table->string('Seal_No', 100)->nullable();
            $table->index(['COO_No'], 'idx_coo_no');
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
