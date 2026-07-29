<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProgressColumnsToRecruitmentTable extends Migration
{
    public function up()
    {
        Schema::table('recruitment', function (Blueprint $table) {
            if (!Schema::hasColumn('recruitment', 'Interview_Schedule')) {
                $table->dateTime('Interview_Schedule')->nullable();
            }
            if (!Schema::hasColumn('recruitment', 'Interview_Result')) {
                $table->decimal('Interview_Result', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('recruitment', 'Psikotes_Result')) {
                $table->text('Psikotes_Result')->nullable();
            }
            if (!Schema::hasColumn('recruitment', 'MCU_Schedule')) {
                $table->dateTime('MCU_Schedule')->nullable();
            }
            if (!Schema::hasColumn('recruitment', 'MCU_Result')) {
                $table->text('MCU_Result')->nullable();
            }
            if (!Schema::hasColumn('recruitment', 'Hasil_Kategori')) {
                $table->string('Hasil_Kategori', 20)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('recruitment', function (Blueprint $table) {
            $table->dropColumn([
                'Interview_Schedule',
                'Interview_Result',
                'Psikotes_Result',
                'MCU_Schedule',
                'MCU_Result',
                'Hasil_Kategori',
            ]);
        });
    }
}
