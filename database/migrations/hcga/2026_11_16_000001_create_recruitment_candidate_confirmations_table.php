<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecruitmentCandidateConfirmationsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('recruitment_candidate_confirmations')) {
            Schema::create('recruitment_candidate_confirmations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('task_id', 64)->index();
                $table->string('stage_type', 50)->index();
                $table->string('confirmation_status', 40)->default('UPDATED');
                $table->dateTime('preferred_date')->nullable();
                $table->string('notice_period', 100)->nullable();
                $table->text('candidate_note')->nullable();
                $table->string('signed_offering_file', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('recruitment_candidate_confirmations');
    }
}
