<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddBpjsKesExtraFamilyConfigAndColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employment')) {
            Schema::table('employment', function (Blueprint $table) {
                if (!Schema::hasColumn('employment', 'bpjs_kes_extra_family')) {
                    $table->unsignedTinyInteger('bpjs_kes_extra_family')
                        ->default(0)
                        ->after('bpjs_kes_deduction')
                        ->comment('Jumlah anggota keluarga tambahan BPJS Kesehatan (> 5 jiwa inti: anak ke-4 dst, orang tua, mertua), +1% per orang');
                }
            });
        }

        if (Schema::hasTable('payroll_deduction_configs')) {
            $now = date('Y-m-d H:i:s');

            DB::table('payroll_deduction_configs')->updateOrInsert(
                ['config_key' => 'bpjs_kes_max_core_family'],
                [
                    'config_name' => 'Batas Anggota Keluarga Inti BPJS Kesehatan',
                    'category'    => 'BPJS',
                    'value_type'  => 'NUMERIC',
                    'value_data'  => '5',
                    'unit'        => 'ORANG',
                    'description' => 'Maksimal anggota keluarga inti yang tercakup iuran standar 1% (Pegawai + Suami/Istri + 3 Anak = 5 jiwa).',
                    'is_active'   => 1,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]
            );

            DB::table('payroll_deduction_configs')->updateOrInsert(
                ['config_key' => 'bpjs_kes_extra_family_rate'],
                [
                    'config_name' => 'Tarif BPJS Kesehatan Keluarga Tambahan (> 5 Jiwa)',
                    'category'    => 'BPJS',
                    'value_type'  => 'PERCENT',
                    'value_data'  => '1.00',
                    'unit'        => '%',
                    'description' => 'Tarif tambahan per 1 anggota keluarga lain di luar 5 tanggungan inti (anak ke-4 dst, orang tua, mertua) sesuai Perpres 82/2018 & PER-7/PB/2021.',
                    'is_active'   => 1,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('employment')) {
            Schema::table('employment', function (Blueprint $table) {
                if (Schema::hasColumn('employment', 'bpjs_kes_extra_family')) {
                    $table->dropColumn('bpjs_kes_extra_family');
                }
            });
        }

        if (Schema::hasTable('payroll_deduction_configs')) {
            DB::table('payroll_deduction_configs')
                ->whereIn('config_key', ['bpjs_kes_max_core_family', 'bpjs_kes_extra_family_rate'])
                ->delete();
        }
    }
}
