<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnhanceKategoriIzinAndSeedDefaults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Pastikan kat_izin_id adalah AUTO_INCREMENT
        DB::statement("ALTER TABLE `kategori_izin` MODIFY `kat_izin_id` INT(11) NOT NULL AUTO_INCREMENT");

        // 2. Tambah kolom baru jika belum ada
        Schema::table('kategori_izin', function (Blueprint $table) {
            if (!Schema::hasColumn('kategori_izin', 'kat_izin_code')) {
                $table->string('kat_izin_code', 50)->nullable();
            }
            if (!Schema::hasColumn('kategori_izin', 'is_deduct_annual_leave')) {
                $table->enum('is_deduct_annual_leave', ['YES', 'NO'])->default('NO');
            }
            if (!Schema::hasColumn('kategori_izin', 'is_need_attachment')) {
                $table->enum('is_need_attachment', ['YES', 'NO'])->default('NO');
            }
            if (!Schema::hasColumn('kategori_izin', 'status')) {
                $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            }
        });

        // 3. Default Data Kategori Izin / Cuti Dinamis Lengkap
        $defaults = [
            [
                'kat_izin_code'          => 'CUTI_TAHUNAN',
                'kat_izin_nama'          => 'Cuti Tahunan',
                'izin_jenis_id'          => 1,
                'jatah_hari'             => 12,
                'is_deduct_annual_leave' => 'YES',
                'is_need_attachment'     => 'NO',
                'status'                 => 'ACTIVE',
            ],
            [
                'kat_izin_code'          => 'CUTI_BERSAMA',
                'kat_izin_nama'          => 'Cuti Bersama (Pemerintah)',
                'izin_jenis_id'          => 1,
                'jatah_hari'             => 1,
                'is_deduct_annual_leave' => 'YES',
                'is_need_attachment'     => 'NO',
                'status'                 => 'ACTIVE',
            ],
            [
                'kat_izin_code'          => 'CUTI_MELAHIRKAN',
                'kat_izin_nama'          => 'Cuti Melahirkan / Bersalin',
                'izin_jenis_id'          => 1,
                'jatah_hari'             => 90,
                'is_deduct_annual_leave' => 'NO',
                'is_need_attachment'     => 'YES',
                'status'                 => 'ACTIVE',
            ],
            [
                'kat_izin_code'          => 'CUTI_KEGUGURAN',
                'kat_izin_nama'          => 'Cuti Keguguran Kandungan',
                'izin_jenis_id'          => 1,
                'jatah_hari'             => 45,
                'is_deduct_annual_leave' => 'NO',
                'is_need_attachment'     => 'YES',
                'status'                 => 'ACTIVE',
            ],
            [
                'kat_izin_code'          => 'CUTI_KERABAT_MENINGGAL',
                'kat_izin_nama'          => 'Cuti Kerabat / Keluarga Inti Meninggal',
                'izin_jenis_id'          => 1,
                'jatah_hari'             => 2,
                'is_deduct_annual_leave' => 'NO',
                'is_need_attachment'     => 'NO',
                'status'                 => 'ACTIVE',
            ],
            [
                'kat_izin_code'          => 'CUTI_MENIKAH',
                'kat_izin_nama'          => 'Cuti Pernikahan Karyawan',
                'izin_jenis_id'          => 1,
                'jatah_hari'             => 3,
                'is_deduct_annual_leave' => 'NO',
                'is_need_attachment'     => 'NO',
                'status'                 => 'ACTIVE',
            ],
            [
                'kat_izin_code'          => 'CUTI_ANAK_MENIKAH_KHITAN',
                'kat_izin_nama'          => 'Cuti Anak Menikah / Khitanan / Baptis',
                'izin_jenis_id'          => 1,
                'jatah_hari'             => 2,
                'is_deduct_annual_leave' => 'NO',
                'is_need_attachment'     => 'NO',
                'status'                 => 'ACTIVE',
            ],
            [
                'kat_izin_code'          => 'CUTI_HAJI',
                'kat_izin_nama'          => 'Cuti Ibadah Haji (Pertama Kali)',
                'izin_jenis_id'          => 1,
                'jatah_hari'             => 40,
                'is_deduct_annual_leave' => 'NO',
                'is_need_attachment'     => 'YES',
                'status'                 => 'ACTIVE',
            ],
            [
                'kat_izin_code'          => 'CUTI_UMRAH',
                'kat_izin_nama'          => 'Cuti Ibadah Umrah',
                'izin_jenis_id'          => 1,
                'jatah_hari'             => 14,
                'is_deduct_annual_leave' => 'YES',
                'is_need_attachment'     => 'YES',
                'status'                 => 'ACTIVE',
            ],
            [
                'kat_izin_code'          => 'IZIN_SAKIT_SURAT_DOKTER',
                'kat_izin_nama'          => 'Izin Sakit (Dengan Surat Dokter)',
                'izin_jenis_id'          => 2,
                'jatah_hari'             => 3,
                'is_deduct_annual_leave' => 'NO',
                'is_need_attachment'     => 'YES',
                'status'                 => 'ACTIVE',
            ],
            [
                'kat_izin_code'          => 'IZIN_KEPERLUAN_PRIBADI',
                'kat_izin_nama'          => 'Izin Keperluan Pribadi / Mendesak',
                'izin_jenis_id'          => 2,
                'jatah_hari'             => 1,
                'is_deduct_annual_leave' => 'YES',
                'is_need_attachment'     => 'NO',
                'status'                 => 'ACTIVE',
            ],
            [
                'kat_izin_code'          => 'DINAS_LUAR_KANTOR',
                'kat_izin_nama'          => 'Tugas / Dinas Luar Kantor',
                'izin_jenis_id'          => 3,
                'jatah_hari'             => 7,
                'is_deduct_annual_leave' => 'NO',
                'is_need_attachment'     => 'NO',
                'status'                 => 'ACTIVE',
            ],
            [
                'kat_izin_code'          => 'ALPHA_TIDAK_HADIR',
                'kat_izin_nama'          => 'Alpha (Tidak Hadir Tanpa Keterangan)',
                'izin_jenis_id'          => 4,
                'jatah_hari'             => 0,
                'is_deduct_annual_leave' => 'NO',
                'is_need_attachment'     => 'NO',
                'status'                 => 'ACTIVE',
            ],
        ];

        foreach ($defaults as $def) {
            $exists = DB::table('kategori_izin')
                ->where('kat_izin_nama', $def['kat_izin_nama'])
                ->orWhere('kat_izin_code', $def['kat_izin_code'])
                ->first();

            if (!$exists) {
                DB::table('kategori_izin')->insert($def);
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
    }
}
