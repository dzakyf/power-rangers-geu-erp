<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOvertimeCompensationToPositionAndOrgTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tambah kolom overtime_compensation ke master_position
        if (Schema::hasTable('master_position')) {
            Schema::table('master_position', function (Blueprint $table) {
                if (!Schema::hasColumn('master_position', 'overtime_compensation')) {
                    $table->enum('overtime_compensation', ['PAID', 'TIME_OFF'])->nullable()->default(null)->after('Position_Type')->comment('Default kompensasi lembur: PAID=Uang Lembur, TIME_OFF=Tukar Cuti');
                }
            });
        }

        // 2. Tambah kolom overtime_compensation ke organization_structure
        if (Schema::hasTable('organization_structure')) {
            Schema::table('organization_structure', function (Blueprint $table) {
                if (!Schema::hasColumn('organization_structure', 'overtime_compensation')) {
                    $table->enum('overtime_compensation', ['PAID', 'TIME_OFF'])->nullable()->default(null)->after('Level')->comment('Override kompensasi lembur posisi/node bagan organisasi');
                }
            });
        }

        // 3. Tambah kolom overtime_compensation ke employment
        if (Schema::hasTable('employment')) {
            Schema::table('employment', function (Blueprint $table) {
                if (!Schema::hasColumn('employment', 'overtime_compensation')) {
                    $table->enum('overtime_compensation', ['PAID', 'TIME_OFF'])->nullable()->default(null)->after('Position')->comment('Override kompensasi lembur per karyawan');
                }
            });
        }

        // 4. Tambah kolom overtime_compensation ke users
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'overtime_compensation')) {
                    $table->enum('overtime_compensation', ['PAID', 'TIME_OFF'])->nullable()->default(null)->after('Position')->comment('Override kompensasi lembur per user account');
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
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'overtime_compensation')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('overtime_compensation');
            });
        }

        if (Schema::hasTable('employment') && Schema::hasColumn('employment', 'overtime_compensation')) {
            Schema::table('employment', function (Blueprint $table) {
                $table->dropColumn('overtime_compensation');
            });
        }

        if (Schema::hasTable('organization_structure') && Schema::hasColumn('organization_structure', 'overtime_compensation')) {
            Schema::table('organization_structure', function (Blueprint $table) {
                $table->dropColumn('overtime_compensation');
            });
        }

        if (Schema::hasTable('master_position') && Schema::hasColumn('master_position', 'overtime_compensation')) {
            Schema::table('master_position', function (Blueprint $table) {
                $table->dropColumn('overtime_compensation');
            });
        }
    }
}

