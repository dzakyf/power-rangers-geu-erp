<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsKemasanSerialNumbersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cns_kemasan_serial_numbers')) {
            Schema::create('cns_kemasan_serial_numbers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('kemasan_id')->index();
                $table->string('serial_number', 100)->unique();
                $table->string('status', 20)->default('AVAILABLE')->index();
                $table->unsignedBigInteger('goods_receipt_id')->nullable()->index();
                $table->unsignedBigInteger('vessel_schedule_id')->nullable()->index();
                $table->unsignedBigInteger('vessel_schedule_dtl_id')->nullable()->index();
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('vessel_schedule_dtls') && !Schema::hasColumn('vessel_schedule_dtls', 'serial_number')) {
            Schema::table('vessel_schedule_dtls', function (Blueprint $table) {
                $table->string('serial_number', 100)->nullable()->after('Seal');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('vessel_schedule_dtls') && Schema::hasColumn('vessel_schedule_dtls', 'serial_number')) {
            Schema::table('vessel_schedule_dtls', function (Blueprint $table) {
                $table->dropColumn('serial_number');
            });
        }

        Schema::dropIfExists('cns_kemasan_serial_numbers');
    }
}
