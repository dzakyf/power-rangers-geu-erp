<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMcuFileToRecruitmentTable extends Migration
{
    public function up()
    {
        Schema::table('recruitment', function (Blueprint $table) {
            if (!Schema::hasColumn('recruitment', 'MCU_Note')) {
                $table->text('MCU_Note')->nullable();
            }
            if (!Schema::hasColumn('recruitment', 'MCU_File')) {
                $table->string('MCU_File', 500)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('recruitment', function (Blueprint $table) {
            if (Schema::hasColumn('recruitment', 'MCU_Note')) {
                $table->dropColumn('MCU_Note');
            }
            if (Schema::hasColumn('recruitment', 'MCU_File')) {
                $table->dropColumn('MCU_File');
            }
        });
    }
}

