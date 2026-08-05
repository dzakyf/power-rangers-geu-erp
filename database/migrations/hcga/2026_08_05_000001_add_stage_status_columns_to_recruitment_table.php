<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStageStatusColumnsToRecruitmentTable extends Migration
{
    public function up()
    {
        Schema::table('recruitment', function (Blueprint $table) {
            if (!Schema::hasColumn('recruitment', 'Technical_Test_Status')) {
                $table->string('Technical_Test_Status', 50)->nullable();
            }
            if (!Schema::hasColumn('recruitment', 'MCU_Status')) {
                $table->string('MCU_Status', 50)->nullable();
            }
            if (!Schema::hasColumn('recruitment', 'Offering_Status')) {
                $table->string('Offering_Status', 50)->nullable();
            }
            if (!Schema::hasColumn('recruitment', 'Description')) {
                $table->text('Description')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('recruitment', function (Blueprint $table) {
            $table->dropColumn([
                'Technical_Test_Status',
                'MCU_Status',
                'Offering_Status',
                'Description',
            ]);
        });
    }
}
