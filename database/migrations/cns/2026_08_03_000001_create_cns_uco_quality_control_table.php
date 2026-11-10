<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsUcoQualityControlTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('uco_quality_control')) {
            return; // already provisioned on the live database
        }

        Schema::create('uco_quality_control', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->dateTime('Created_At')->nullable()->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));
            $table->decimal('FFA_As_Palmitic', 5, 2)->nullable();
            $table->decimal('FFA_As_Oleic', 5, 2)->nullable();
            $table->decimal('MC', 5, 2)->nullable();
            $table->decimal('IMP', 5, 2)->nullable();
            $table->enum('Type', ['TOP', 'MIDDLE', 'BOTTOM'])->nullable();
            $table->tinyInteger('Is_Deleted')->nullable()->default(0);

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
