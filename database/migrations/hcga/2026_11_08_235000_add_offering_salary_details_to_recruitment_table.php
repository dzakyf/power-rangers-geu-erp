<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOfferingSalaryDetailsToRecruitmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('recruitment')) {
            Schema::table('recruitment', function (Blueprint $table) {
                if (!Schema::hasColumn('recruitment', 'Salary_Period_Note')) {
                    $table->text('Salary_Period_Note')->nullable();
                }
                if (!Schema::hasColumn('recruitment', 'Salary_Details_Json')) {
                    $table->longText('Salary_Details_Json')->nullable();
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
        if (Schema::hasTable('recruitment')) {
            Schema::table('recruitment', function (Blueprint $table) {
                if (Schema::hasColumn('recruitment', 'Salary_Period_Note')) {
                    $table->dropColumn('Salary_Period_Note');
                }
                if (Schema::hasColumn('recruitment', 'Salary_Details_Json')) {
                    $table->dropColumn('Salary_Details_Json');
                }
            });
        }
    }
}

