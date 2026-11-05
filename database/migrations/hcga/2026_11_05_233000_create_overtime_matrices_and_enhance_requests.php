<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOvertimeMatricesAndEnhanceRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tambah Kolom Zipcode & Region pada Master Lokasi Absensi jika belum ada
        if (Schema::hasTable('attendance_locations')) {
            Schema::table('attendance_locations', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_locations', 'zipcode')) {
                    $table->string('zipcode', 10)->nullable()->after('address');
                }
                if (!Schema::hasColumn('attendance_locations', 'region_group')) {
                    $table->string('region_group', 50)->nullable()->after('zipcode')->comment('WILAYAH_1, WILAYAH_2, DLL');
                }
            });
        }

        // 2. Master Header Matriks Lembur
        if (!Schema::hasTable('overtime_rate_matrices')) {
            Schema::create('overtime_rate_matrices', function (Blueprint $table) {
                $table->increments('id');
                $table->string('matrix_code', 50)->unique();
                $table->string('matrix_name', 150);
                $table->string('grade_group', 100)->nullable()->comment('Contoh: Staff-SPV (Grading 8-5), WHL/Coor WH (Grading 8-7), Kru/Driver/OB/QC');
                $table->text('job_titles')->nullable()->comment('Daftar jabatan atau JSON keywords');
                $table->string('region_type', 50)->nullable()->comment('WILAYAH_1, WILAYAH_2, ALL');
                $table->enum('pricing_type', ['TIERED_HOURLY', 'FLAT_TASK', 'MINUTE_RANGE'])->default('TIERED_HOURLY');
                $table->decimal('max_hours', 4, 2)->default(8.00);
                $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
            });
        }

        // 3. Detail Tier Matriks Lembur
        if (!Schema::hasTable('overtime_matrix_tiers')) {
            Schema::create('overtime_matrix_tiers', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('matrix_id');
                $table->integer('tier_order')->default(1);
                $table->integer('min_minutes')->default(0);
                $table->integer('max_minutes')->nullable();
                $table->string('tier_label', 100)->nullable();
                $table->decimal('base_rate', 12, 2)->default(0.00);
                $table->decimal('next_per_hour_rate', 12, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('matrix_id')->references('id')->on('overtime_rate_matrices')->onDelete('cascade');
            });
        }

        // 4. Modifikasi / Lengkapi Tabel Pengajuan Lembur
        if (Schema::hasTable('attendance_overtime_requests')) {
            Schema::table('attendance_overtime_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_overtime_requests', 'location_id')) {
                    $table->integer('location_id')->nullable()->after('nik');
                }
                if (!Schema::hasColumn('attendance_overtime_requests', 'zipcode')) {
                    $table->string('zipcode', 10)->nullable()->after('location_id');
                }
                if (!Schema::hasColumn('attendance_overtime_requests', 'overtime_type')) {
                    $table->enum('overtime_type', ['BEFORE_SHIFT', 'AFTER_SHIFT'])->default('AFTER_SHIFT')->after('overtime_date');
                }
                if (!Schema::hasColumn('attendance_overtime_requests', 'compensation_type')) {
                    $table->enum('compensation_type', ['PAID', 'TIME_OFF'])->default('PAID')->after('duration_minutes');
                }
                if (!Schema::hasColumn('attendance_overtime_requests', 'matrix_id')) {
                    $table->unsignedInteger('matrix_id')->nullable()->after('compensation_type');
                }
                if (!Schema::hasColumn('attendance_overtime_requests', 'calculated_amount')) {
                    $table->decimal('calculated_amount', 12, 2)->default(0.00)->after('matrix_id');
                }
                if (!Schema::hasColumn('attendance_overtime_requests', 'hardcopy_file')) {
                    $table->string('hardcopy_file', 255)->nullable()->after('calculated_amount');
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
        if (Schema::hasTable('attendance_overtime_requests')) {
            Schema::table('attendance_overtime_requests', function (Blueprint $table) {
                $cols = ['location_id', 'zipcode', 'overtime_type', 'compensation_type', 'matrix_id', 'calculated_amount', 'hardcopy_file'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('attendance_overtime_requests', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('overtime_matrix_tiers');
        Schema::dropIfExists('overtime_rate_matrices');

        if (Schema::hasTable('attendance_locations')) {
            Schema::table('attendance_locations', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_locations', 'region_group')) {
                    $table->dropColumn('region_group');
                }
                if (Schema::hasColumn('attendance_locations', 'zipcode')) {
                    $table->dropColumn('zipcode');
                }
            });
        }
    }
}

