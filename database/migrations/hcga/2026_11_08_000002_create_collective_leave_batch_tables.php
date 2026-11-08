<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectiveLeaveBatchTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tabel Header Batch Cuti Bersama yang diinisiasi HCGA
        if (!Schema::hasTable('attendance_collective_leaves')) {
            Schema::create('attendance_collective_leaves', function (Blueprint $table) {
                $table->increments('id');
                $table->string('batch_no', 100)->unique();
                $table->string('title', 200); // e.g. "Cuti Bersama Hari Raya Idul Fitri 1447H"
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('total_days')->default(1);
                $table->enum('is_deduct_annual_leave', ['YES', 'NO'])->default('YES');
                $table->string('target_scope', 50)->default('ALL'); // ALL, DEPARTMENT, LOCATION, SPECIFIC
                $table->string('department', 100)->nullable();
                $table->string('location', 100)->nullable();
                $table->integer('total_employees_affected')->default(0);
                $table->text('description')->nullable();
                $table->enum('status', ['DRAFT', 'APPLIED', 'REVERTED'])->default('APPLIED');
                $table->unsignedBigInteger('approval_request_id')->nullable()->index();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
            });
        }

        // 2. Tambah kolom collective_leave_id pada attendance_leave_requests jika belum ada
        if (Schema::hasTable('attendance_leave_requests')) {
            Schema::table('attendance_leave_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_leave_requests', 'collective_leave_id')) {
                    $table->unsignedBigInteger('collective_leave_id')->nullable()->index()->after('legacy_permission_id');
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
        if (Schema::hasTable('attendance_leave_requests')) {
            Schema::table('attendance_leave_requests', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_leave_requests', 'collective_leave_id')) {
                    $table->dropColumn('collective_leave_id');
                }
            });
        }

        Schema::dropIfExists('attendance_collective_leaves');
    }
}

