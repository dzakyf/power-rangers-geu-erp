<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFallbackSchemeToPayrollThrBatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_thr_batches') && !Schema::hasColumn('payroll_thr_batches', 'fallback_thr_scheme')) {
            Schema::table('payroll_thr_batches', function (Blueprint $table) {
                $table->string('fallback_thr_scheme', 40)->nullable()->after('prorate_method');
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
        if (Schema::hasTable('payroll_thr_batches') && Schema::hasColumn('payroll_thr_batches', 'fallback_thr_scheme')) {
            Schema::table('payroll_thr_batches', function (Blueprint $table) {
                $table->dropColumn('fallback_thr_scheme');
            });
        }
    }
}
