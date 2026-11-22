<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMandatoryInterviewFieldsToRecruitmentDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('recruitment_detail')) {
            Schema::table('recruitment_detail', function (Blueprint $table) {
                if (!Schema::hasColumn('recruitment_detail', 'Usia')) {
                    $table->string('Usia', 50)->nullable()->after('Salary_Expectation');
                }
                if (!Schema::hasColumn('recruitment_detail', 'Domisili')) {
                    $table->string('Domisili', 255)->nullable()->after('Usia');
                }
                if (!Schema::hasColumn('recruitment_detail', 'Notice_Period')) {
                    $table->string('Notice_Period', 100)->nullable()->after('Computer');
                }
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
        if (Schema::hasTable('recruitment_detail')) {
            Schema::table('recruitment_detail', function (Blueprint $table) {
                $dropCols = [];
                foreach (['Usia', 'Domisili', 'Notice_Period'] as $col) {
                    if (Schema::hasColumn('recruitment_detail', $col)) {
                        $dropCols[] = $col;
                    }
                }
                if (!empty($dropCols)) {
                    $table->dropColumn($dropCols);
                }
            });
        }
    }
}
