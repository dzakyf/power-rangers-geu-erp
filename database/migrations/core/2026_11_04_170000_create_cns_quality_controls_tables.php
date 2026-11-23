<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsQualityControlsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cns_quality_controls')) {
            Schema::create('cns_quality_controls', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('Kode_QC', 100)->nullable()->unique();
                $table->date('Inspection_Date')->nullable();
                $table->string('QC_Type', 20)->default('INBOUND');
                $table->string('QC_Category', 20)->default('EXTERNAL');
                $table->unsignedBigInteger('Contract_ID')->nullable();
                $table->unsignedBigInteger('Booking_ID')->nullable();
                $table->string('Attachment_Path', 255)->nullable();
                $table->text('Notes')->nullable();
                $table->tinyInteger('Is_Deleted')->default(0);
                $table->string('Created_By', 100)->nullable();
                $table->timestamps();

                $table->index('Kode_QC');
                $table->index('Inspection_Date');
                $table->index('QC_Type');
                $table->index('QC_Category');
            });
        }

        if (!Schema::hasTable('cns_quality_control_details')) {
            Schema::create('cns_quality_control_details', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('Quality_Control_ID');
                $table->string('Sample_Level', 20); // TOP, MIDDLE, BOTTOM, AVERAGE
                $table->decimal('FFA_As_Palmitic', 10, 2)->nullable();
                $table->decimal('FFA_As_Oleic', 10, 2)->nullable();
                $table->decimal('MIU', 10, 2)->nullable();
                $table->decimal('Sulphur', 10, 2)->nullable();
                $table->decimal('IV', 10, 2)->nullable();
                $table->decimal('Total_Chlorides', 10, 2)->nullable();
                $table->decimal('Phosphorus', 10, 2)->nullable();
                $table->decimal('Total_Metals', 10, 2)->nullable();
                $table->timestamps();

                $table->index('Quality_Control_ID');
                $table->index('Sample_Level');
                $table->foreign('Quality_Control_ID')
                    ->references('id')
                    ->on('cns_quality_controls')
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
        Schema::dropIfExists('cns_quality_control_details');
        Schema::dropIfExists('cns_quality_controls');
    }
}

