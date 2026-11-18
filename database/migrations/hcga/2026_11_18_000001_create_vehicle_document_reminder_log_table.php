<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleDocumentReminderLogTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('vehicle_document_reminder_log')) {
            Schema::create('vehicle_document_reminder_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('Asset_No', 60)->index();
                $table->string('Doc_Type', 30); // STNK_TAHUNAN | STNK_5_TAHUN | KEUR
                $table->date('Due_Date');
                $table->integer('Threshold_Days'); // 60 | 30 | 14 | 0 | -1 (expired)
                $table->string('Channel', 20)->default('IN_APP'); // IN_APP | EMAIL
                $table->string('Sent_To', 150)->nullable();
                $table->dateTime('Sent_At');

                $table->unique(
                    ['Asset_No', 'Doc_Type', 'Due_Date', 'Threshold_Days', 'Channel'],
                    'uniq_veh_doc_rem'
                );
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_document_reminder_log');
    }
}
