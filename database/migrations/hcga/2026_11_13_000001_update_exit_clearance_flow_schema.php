<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateExitClearanceFlowSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('exit_clearances')) {
            Schema::table('exit_clearances', function (Blueprint $table) {
                if (!Schema::hasColumn('exit_clearances', 'Hcga_Interview_By')) {
                    $table->unsignedInteger('Hcga_Interview_By')->nullable()->after('exit_interview_notes');
                }
                if (!Schema::hasColumn('exit_clearances', 'Hcga_Interview_At')) {
                    $table->timestamp('Hcga_Interview_At')->nullable()->after('Hcga_Interview_By');
                }
            });

            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `exit_clearances` MODIFY COLUMN `Status` ENUM('DRAFT', 'PENDING_INTERVIEW', 'PENDING_MANAGER_APPROVAL', 'PENDING_HCGA_APPROVAL', 'COMPLETED', 'REJECTED') NOT NULL DEFAULT 'DRAFT'");
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('exit_clearances')) {
            if (DB::getDriverName() === 'mysql') {
                DB::table('exit_clearances')
                    ->where('Status', 'PENDING_MANAGER_APPROVAL')
                    ->update(['Status' => 'PENDING_INTERVIEW']);

                DB::statement("ALTER TABLE `exit_clearances` MODIFY COLUMN `Status` ENUM('DRAFT', 'PENDING_INTERVIEW', 'PENDING_HCGA_APPROVAL', 'COMPLETED', 'REJECTED') NOT NULL DEFAULT 'DRAFT'");
            }

            Schema::table('exit_clearances', function (Blueprint $table) {
                if (Schema::hasColumn('exit_clearances', 'Hcga_Interview_At')) {
                    $table->dropColumn('Hcga_Interview_At');
                }
                if (Schema::hasColumn('exit_clearances', 'Hcga_Interview_By')) {
                    $table->dropColumn('Hcga_Interview_By');
                }
            });
        }
    }
}

