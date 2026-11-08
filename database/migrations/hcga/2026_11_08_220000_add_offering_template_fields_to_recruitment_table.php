<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOfferingTemplateFieldsToRecruitmentTable extends Migration
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
                if (!Schema::hasColumn('recruitment', 'Grading')) {
                    $table->string('Grading', 50)->nullable()->after('Position');
                }
                if (!Schema::hasColumn('recruitment', 'Work_Location')) {
                    $table->string('Work_Location', 150)->nullable()->after('Grading');
                }
                if (!Schema::hasColumn('recruitment', 'Contract_Type')) {
                    $table->string('Contract_Type', 255)->nullable()->after('Work_Location');
                }
                if (!Schema::hasColumn('recruitment', 'Direct_Supervisor')) {
                    $table->string('Direct_Supervisor', 150)->nullable()->after('Contract_Type');
                }
                if (!Schema::hasColumn('recruitment', 'Working_Schedule')) {
                    $table->text('Working_Schedule')->nullable()->after('Direct_Supervisor');
                }
                if (!Schema::hasColumn('recruitment', 'Salary_Base')) {
                    $table->decimal('Salary_Base', 15, 2)->default(0)->after('Salary_Accepted');
                }
                if (!Schema::hasColumn('recruitment', 'Salary_Cola')) {
                    $table->decimal('Salary_Cola', 15, 2)->default(0)->after('Salary_Base');
                }
                if (!Schema::hasColumn('recruitment', 'Salary_Allowance')) {
                    $table->decimal('Salary_Allowance', 15, 2)->default(0)->after('Salary_Cola');
                }
                if (!Schema::hasColumn('recruitment', 'Benefit_Description')) {
                    $table->text('Benefit_Description')->nullable()->after('Salary_Allowance');
                }
                if (!Schema::hasColumn('recruitment', 'Offering_Terms')) {
                    $table->text('Offering_Terms')->nullable()->after('Benefit_Description');
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
                $cols = [
                    'Grading', 'Work_Location', 'Contract_Type', 'Direct_Supervisor',
                    'Working_Schedule', 'Salary_Base', 'Salary_Cola', 'Salary_Allowance',
                    'Benefit_Description', 'Offering_Terms'
                ];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('recruitment', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }
    }
}

