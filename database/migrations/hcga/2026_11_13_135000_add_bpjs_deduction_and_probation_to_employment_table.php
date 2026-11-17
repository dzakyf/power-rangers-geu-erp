<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBpjsDeductionAndProbationToEmploymentTable extends Migration
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
                if (!Schema::hasColumn('employment', 'bpjs_tk_deduction')) {
                    $table->tinyInteger('bpjs_tk_deduction')->default(1)->after('bpjs_tk')->comment('1=Dipotong BPJS Ketenagakerjaan, 0=Tidak Dipotong / Bebas');
                }
                if (!Schema::hasColumn('employment', 'bpjs_kes_deduction')) {
                    $table->tinyInteger('bpjs_kes_deduction')->default(1)->after('bpjs_kes')->comment('1=Dipotong BPJS Kesehatan, 0=Tidak Dipotong / Bebas');
                }
                if (!Schema::hasColumn('employment', 'bpjs_probation_exempt')) {
                    $table->tinyInteger('bpjs_probation_exempt')->default(0)->after('bpjs_kes_deduction')->comment('1=Tunda/Bebaskan BPJS selama masa probation, 0=Tetap dipotong normal');
                }
                if (!Schema::hasColumn('employment', 'probation_end_date')) {
                    $table->date('probation_end_date')->nullable()->after('bpjs_probation_exempt')->comment('Tanggal batas akhir masa probation');
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
        if (Schema::hasTable('employment')) {
            Schema::table('employment', function (Blueprint $table) {
                if (Schema::hasColumn('employment', 'probation_end_date')) {
                    $table->dropColumn('probation_end_date');
                }
                if (Schema::hasColumn('employment', 'bpjs_probation_exempt')) {
                    $table->dropColumn('bpjs_probation_exempt');
                }
                if (Schema::hasColumn('employment', 'bpjs_kes_deduction')) {
                    $table->dropColumn('bpjs_kes_deduction');
                }
                if (Schema::hasColumn('employment', 'bpjs_tk_deduction')) {
                    $table->dropColumn('bpjs_tk_deduction');
                }
            });
        }
    }
}
