<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReinspectionToGaCleaningInspection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ga_cleaning_inspection', function (Blueprint $table) {
            if (!Schema::hasColumn('ga_cleaning_inspection', 'Audit_Round')) {
                $table->unsignedInteger('Audit_Round')->default(1)->after('Score');
            }
            if (!Schema::hasColumn('ga_cleaning_inspection', 'Initial_Score')) {
                $table->decimal('Initial_Score', 5, 2)->nullable()->after('Audit_Round');
            }
            if (!Schema::hasColumn('ga_cleaning_inspection', 'Initial_Status')) {
                $table->string('Initial_Status', 30)->nullable()->after('Initial_Score');
            }
            if (!Schema::hasColumn('ga_cleaning_inspection', 'Reinspection_At')) {
                $table->timestamp('Reinspection_At')->nullable()->after('Follow_Up_Ticket_No');
            }
            if (!Schema::hasColumn('ga_cleaning_inspection', 'Reinspection_Notes')) {
                $table->text('Reinspection_Notes')->nullable()->after('Reinspection_At');
            }
        });

        if (!Schema::hasTable('ga_cleaning_inspection_log')) {
            Schema::create('ga_cleaning_inspection_log', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->unsignedInteger('Inspection_ID')->index();
                $table->string('Inspection_No', 60)->index();
                $table->unsignedInteger('Audit_Round')->default(1);
                $table->decimal('Score', 5, 2);
                $table->string('Result_Status', 30);
                $table->text('Notes')->nullable();
                $table->string('Inspector_User_Id', 100);
                $table->string('Inspector_Name', 150);
                $table->timestamp('Created_At')->nullable();
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
        Schema::dropIfExists('ga_cleaning_inspection_log');

        Schema::table('ga_cleaning_inspection', function (Blueprint $table) {
            $cols = ['Audit_Round', 'Initial_Score', 'Initial_Status', 'Reinspection_At', 'Reinspection_Notes'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('ga_cleaning_inspection', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
