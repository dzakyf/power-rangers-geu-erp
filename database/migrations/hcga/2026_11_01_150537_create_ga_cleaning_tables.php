<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaCleaningTables extends Migration
{
    public function up()
    {
        // 1. Area Pembersihan (Zones/Areas/Rooms)
        if (!Schema::hasTable('ga_cleaning_area')) {
            Schema::create('ga_cleaning_area', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Area_Code', 60)->unique();
                $table->string('Area_Name', 150);
                $table->string('Building', 100);
                $table->string('Floor', 50);
                $table->string('Location', 50)->default('');
                $table->string('Qr_Code', 100)->nullable()->index(); // Terhubung ke ga_qr_point / QR Point
                $table->string('Category', 50)->default('OFFICE'); // OFFICE, TOILET, PANTRY, CORRIDOR, LOBBY, OUTDOOR
                $table->string('Status', 20)->default('ACTIVE')->index(); // ACTIVE, INACTIVE
                $table->text('Remarks')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }

        // 2. Master Checklist Item Template
        if (!Schema::hasTable('ga_cleaning_checklist_template')) {
            Schema::create('ga_cleaning_checklist_template', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Area_Category', 50)->index(); // OFFICE, TOILET, etc
                $table->string('Item_Name', 150);
                $table->string('Item_Description', 255)->nullable();
                $table->integer('Sort_Order')->default(1);
                $table->string('Status', 20)->default('ACTIVE')->index();
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }

        // 3. Jadwal Pembersihan Rutin (Schedules)
        if (!Schema::hasTable('ga_cleaning_schedule')) {
            Schema::create('ga_cleaning_schedule', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Schedule_No', 60)->unique();
                $table->unsignedInteger('Area_ID')->index();
                $table->string('Frequency', 30)->default('DAILY'); // DAILY, WEEKLY, MONTHLY, ON_DEMAND
                $table->string('Shift', 30)->default('MORNING'); // MORNING, AFTERNOON, NIGHT
                $table->time('Planned_Time_Start')->nullable();
                $table->time('Planned_Time_End')->nullable();
                $table->string('Assignee_Type', 30)->default('INTERNAL'); // INTERNAL, VENDOR
                $table->string('Assignee_ID', 100)->nullable()->index(); // User ID atau Vendor ID
                $table->string('Assignee_Name', 150)->nullable();
                $table->string('Status', 20)->default('ACTIVE')->index(); // ACTIVE, INACTIVE
                $table->text('Notes')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }

        // 4. Pelaksanaan Pembersihan (Cleaning Task / Execution Log)
        if (!Schema::hasTable('ga_cleaning_task')) {
            Schema::create('ga_cleaning_task', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Task_No', 60)->unique();
                $table->unsignedInteger('Schedule_ID')->nullable()->index();
                $table->unsignedInteger('Area_ID')->index();
                $table->date('Task_Date')->index();
                $table->string('Shift', 30)->default('MORNING');
                $table->string('Executor_Type', 30)->default('INTERNAL'); // INTERNAL, VENDOR
                $table->string('Executor_User_Id', 100)->nullable()->index();
                $table->string('Executor_Name', 150)->nullable();
                $table->string('Qr_Code_Scanned', 100)->nullable();
                $table->dateTime('Scan_In_Time')->nullable();
                $table->dateTime('Completed_Time')->nullable();
                $table->string('Status', 30)->default('SCHEDULED')->index(); // SCHEDULED, IN_PROGRESS, COMPLETED, MISSED, CANCELLED
                $table->text('Notes')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }

        // 5. Checklist Hasil Pelaksanaan Task
        if (!Schema::hasTable('ga_cleaning_task_checklist')) {
            Schema::create('ga_cleaning_task_checklist', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->unsignedInteger('Task_ID')->index();
                $table->string('Item_Name', 150);
                $table->string('Result', 20)->default('PASS'); // PASS, FAIL, NA
                $table->string('Photo_Proof', 255)->nullable(); // Foto jika FAIL / anomali
                $table->text('Remarks')->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }

        // 6. Inspeksi / Audit Kebersihan Supervisor
        if (!Schema::hasTable('ga_cleaning_inspection')) {
            Schema::create('ga_cleaning_inspection', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Inspection_No', 60)->unique();
                $table->unsignedInteger('Area_ID')->index();
                $table->unsignedInteger('Task_ID')->nullable()->index();
                $table->date('Inspection_Date')->index();
                $table->string('Inspector_User_Id', 100)->index();
                $table->string('Inspector_Name', 150);
                $table->decimal('Score', 5, 2)->default(100.00); // 0 - 100
                $table->string('Result_Status', 30)->default('PASSED'); // PASSED, NEEDS_IMPROVEMENT, FAILED
                $table->text('Finding_Notes')->nullable();
                $table->string('Follow_Up_Ticket_No', 60)->nullable()->index(); // Tiket perbaikan jika gagal
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ga_cleaning_inspection');
        Schema::dropIfExists('ga_cleaning_task_checklist');
        Schema::dropIfExists('ga_cleaning_task');
        Schema::dropIfExists('ga_cleaning_schedule');
        Schema::dropIfExists('ga_cleaning_checklist_template');
        Schema::dropIfExists('ga_cleaning_area');
    }
}
