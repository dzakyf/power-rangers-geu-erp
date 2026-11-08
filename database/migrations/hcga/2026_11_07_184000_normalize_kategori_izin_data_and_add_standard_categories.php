<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeKategoriIzinDataAndAddStandardCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Nonaktifkan kategori lama yang tidak relevan untuk perizinan/cuti (seperti Datang Telat, Cepat Pulang, Lupa Absen)
        // karena hal tersebut masuk ke alur koreksi absensi
        DB::table('kategori_izin')
            ->whereIn('kat_izin_id', [1, 2, 3])
            ->update([
                'status' => 'INACTIVE'
            ]);

        // 2. Hubungkan data legacy dengan kat_izin_code standar
        $legacyMappings = [
            4  => ['kat_izin_code' => 'CUTI_KERABAT_MENINGGAL', 'kat_izin_nama' => 'Cuti Kematian Keluarga (2 Hari)', 'izin_jenis_id' => 1, 'is_need_attachment' => 'NO'],
            5  => ['kat_izin_code' => 'IZIN_SAKIT_SURAT_DOKTER', 'kat_izin_nama' => 'Izin Sakit (Surat Dokter)', 'izin_jenis_id' => 2, 'is_need_attachment' => 'YES'],
            6  => ['kat_izin_code' => 'CUTI_MENIKAH', 'kat_izin_nama' => 'Cuti Menikah (3 Hari)', 'izin_jenis_id' => 1, 'is_need_attachment' => 'NO'],
            7  => ['kat_izin_code' => 'CUTI_ANAK_KHITAN_BAPTIS', 'kat_izin_nama' => 'Cuti Anak Khitanan / Pembaptisan (2 Hari)', 'izin_jenis_id' => 1, 'is_need_attachment' => 'NO'],
            18 => ['kat_izin_code' => 'IZIN_SAKIT_REGULER', 'kat_izin_nama' => 'Izin Sakit (Tanpa Surat Dokter)', 'izin_jenis_id' => 2, 'is_need_attachment' => 'NO'],
            31 => ['kat_izin_code' => 'CUTI_MELAHIRKAN', 'kat_izin_nama' => 'Cuti Melahirkan (3 Bulan)', 'izin_jenis_id' => 1, 'is_need_attachment' => 'YES'],
            35 => ['kat_izin_code' => 'CUTI_ISTRI_MELAHIRKAN', 'kat_izin_nama' => 'Cuti Istri Melahirkan / Keguguran (2 Hari)', 'izin_jenis_id' => 1, 'is_need_attachment' => 'NO'],
            36 => ['kat_izin_code' => 'CUTI_KEGUGURAN', 'kat_izin_nama' => 'Cuti Karyawati Keguguran (1.5 Bulan)', 'izin_jenis_id' => 1, 'is_need_attachment' => 'YES'],
            37 => ['kat_izin_code' => 'CUTI_KERABAT_1_RUMAH', 'kat_izin_nama' => 'Cuti Kerabat Meninggal 1 Rumah (1 Hari)', 'izin_jenis_id' => 1, 'is_need_attachment' => 'NO'],
            38 => ['kat_izin_code' => 'CUTI_ANAK_MENIKAH', 'kat_izin_nama' => 'Cuti Pernikahan Anak Karyawan (2 Hari)', 'izin_jenis_id' => 1, 'is_need_attachment' => 'NO'],
            40 => ['kat_izin_code' => 'IZIN_RAWAT_INAP_KELUARGA', 'kat_izin_nama' => 'Izin Rawat Inap Keluarga Inti (2 Hari)', 'izin_jenis_id' => 2, 'is_need_attachment' => 'YES'],
        ];

        foreach ($legacyMappings as $id => $data) {
            DB::table('kategori_izin')->where('kat_izin_id', $id)->update($data);
        }

        // 3. Pastikan Cuti Istri Melahirkan tersedia dan aktif
        $existsIstri = DB::table('kategori_izin')->where('kat_izin_code', 'CUTI_ISTRI_MELAHIRKAN')->first();
        if (!$existsIstri) {
            DB::table('kategori_izin')->insert([
                'kat_izin_code'          => 'CUTI_ISTRI_MELAHIRKAN',
                'kat_izin_nama'          => 'Cuti Istri Melahirkan (2 Hari)',
                'izin_jenis_id'          => 1,
                'jatah_hari'             => 2,
                'is_deduct_annual_leave' => 'NO',
                'is_need_attachment'     => 'NO',
                'status'                 => 'ACTIVE',
            ]);
        }

        // 4. Pastikan Cuti Anak Khitanan / Pembaptisan tersedia dan aktif
        $existsKhitan = DB::table('kategori_izin')->where('kat_izin_code', 'CUTI_ANAK_KHITAN_BAPTIS')->first();
        if (!$existsKhitan) {
            DB::table('kategori_izin')->insert([
                'kat_izin_code'          => 'CUTI_ANAK_KHITAN_BAPTIS',
                'kat_izin_nama'          => 'Cuti Anak Khitanan / Pembaptisan (2 Hari)',
                'izin_jenis_id'          => 1,
                'jatah_hari'             => 2,
                'is_deduct_annual_leave' => 'NO',
                'is_need_attachment'     => 'NO',
                'status'                 => 'ACTIVE',
            ]);
        }

        // Nonaktifkan duplikat ID 47 jika ada agar tidak dobel tampilan
        DB::table('kategori_izin')->where('kat_izin_id', 47)->update(['status' => 'INACTIVE']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert jika diperlukan
    }
}

