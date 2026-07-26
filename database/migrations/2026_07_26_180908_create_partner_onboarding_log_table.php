<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerOnboardingLogTable extends Migration
{
    public function up()
    {
        Schema::create('partner_onboarding_log', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('Opportunity_Id');
            $table->string('Stage', 50);
            $table->string('Stage_Label', 100);
            $table->string('Status', 20)->default('in_progress');
            $table->timestamp('Completed_At')->nullable();
            $table->bigInteger('Completed_By')->nullable();
            $table->text('Notes')->nullable();
            $table->timestamp('Created_At')->nullable();
            $table->timestamp('Updated_At')->nullable();

            $table->foreign('Opportunity_Id')
                  ->references('id')
                  ->on('opportunity_partner')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner_onboarding_log');
    }
}
