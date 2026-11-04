<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateMasterAttendanceCutoffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('master_attendance_cutoff')) {
            Schema::create('master_attendance_cutoff', function (Blueprint $table) {
                $table->increments('id');
                $table->string('cutoff_code', 50)->unique();
                $table->string('name', 150);
                $table->unsignedTinyInteger('start_day')->default(21)->comment('Tanggal mulai perhitungan, cth: 21 atau 26');
                $table->unsignedTinyInteger('end_day')->default(20)->comment('Tanggal cutoff perhitungan, cth: 20 atau 25');
                $table->enum('cutoff_cycle', ['PREV_TO_CURRENT', 'CURRENT_TO_NEXT', 'SAME_MONTH'])->default('PREV_TO_CURRENT')
                    ->comment('PREV_TO_CURRENT: 21 bln sebelumnya s/d 20 bln periode berjalan (Cth periode Nov = 21 Okt s/d 20 Nov)');
                $table->enum('target_type', ['GLOBAL', 'DEPARTMENT', 'EMPLOYEE_TYPE', 'EMPLOYEE'])->default('GLOBAL');
                $table->string('target_value', 100)->nullable()->comment('Nilai departemen, tipe karyawan, atau NIK jika spesifik');
                $table->boolean('is_default')->default(false);
                $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
                $table->text('notes')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();

                $table->index(['target_type', 'target_value', 'status'], 'idx_cutoff_target');
            });

            // Seed default cutoff: 21 bln sebelumnya s/d 20 bln periode berjalan (Periode Nov = 21 Okt s/d 20 Nov)
            DB::table('master_attendance_cutoff')->insert([
                [
                    'cutoff_code'   => 'CUTOFF_REGULAR_21_20',
                    'name'          => 'Cutoff Reguler (21 s/d 20 Bulan Berjalan)',
                    'start_day'     => 21,
                    'end_day'       => 20,
                    'cutoff_cycle'  => 'PREV_TO_CURRENT',
                    'target_type'   => 'GLOBAL',
                    'target_value'  => null,
                    'is_default'    => true,
                    'status'        => 'ACTIVE',
                    'notes'         => 'Default sistem: Perhitungan periode bulan M dihitung mulai tanggal 21 bulan M-1 hingga tanggal 20 bulan M.',
                    'created_by'    => 'SYSTEM',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'cutoff_code'   => 'CUTOFF_SITE_26_25',
                    'name'          => 'Cutoff Operasional / Site (26 s/d 25 Bulan Berjalan)',
                    'start_day'     => 26,
                    'end_day'       => 25,
                    'cutoff_cycle'  => 'PREV_TO_CURRENT',
                    'target_type'   => 'EMPLOYEE_TYPE',
                    'target_value'  => 'SITE',
                    'is_default'    => false,
                    'status'        => 'ACTIVE',
                    'notes'         => 'Perhitungan periode bulan M dihitung mulai tanggal 26 bulan M-1 hingga tanggal 25 bulan M.',
                    'created_by'    => 'SYSTEM',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_attendance_cutoff');
    }
}

