<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddBpjsKesTotalFamilyAndDisplayMode extends Migration
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
                if (!Schema::hasColumn('employment', 'bpjs_kes_total_family')) {
                    $table->unsignedTinyInteger('bpjs_kes_total_family')
                        ->nullable()
                        ->after('bpjs_kes_deduction')
                        ->comment('Total anggota keluarga BPJS Kesehatan (sinkron 2 arah dengan bpjs_kes_extra_family)');
                }
            });

            // Backfill baris yang sudah memiliki bpjs_kes_extra_family > 0
            if (Schema::hasColumn('employment', 'bpjs_kes_extra_family')) {
                DB::table('employment')
                    ->whereNull('bpjs_kes_total_family')
                    ->where('bpjs_kes_extra_family', '>', 0)
                    ->update([
                        'bpjs_kes_total_family' => DB::raw('5 + bpjs_kes_extra_family'),
                    ]);
            }
        }

        if (Schema::hasTable('payroll_deduction_configs')) {
            $now = date('Y-m-d H:i:s');

            DB::table('payroll_deduction_configs')->updateOrInsert(
                ['config_key' => 'bpjs_kes_extra_family_display_mode'],
                [
                    'config_name' => 'Mode Tampilan BPJS Kes Keluarga Tambahan (Payroll & Slip)',
                    'category'    => 'BPJS',
                    'value_type'  => 'SELECT',
                    'value_data'  => 'SEPARATE',
                    'unit'        => 'MODE',
                    'description' => 'SEPARATE = Pisahkan baris iuran keluarga tambahan di payroll/slip gaji; COMBINED = Gabungkan ke 1 baris BPJS Kesehatan.',
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
                if (Schema::hasColumn('employment', 'bpjs_kes_total_family')) {
                    $table->dropColumn('bpjs_kes_total_family');
                }
            });
        }

        if (Schema::hasTable('payroll_deduction_configs')) {
            DB::table('payroll_deduction_configs')
                ->where('config_key', 'bpjs_kes_extra_family_display_mode')
                ->delete();
        }
    }
}
