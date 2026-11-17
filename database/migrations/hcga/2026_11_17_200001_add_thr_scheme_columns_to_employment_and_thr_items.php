<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThrSchemeColumnsToEmploymentAndThrItems extends Migration
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
                if (!Schema::hasColumn('employment', 'thr_scheme')) {
                    $table->string('thr_scheme', 40)->nullable()->after('Employment_Status');
                }
                if (!Schema::hasColumn('employment', 'thr_contract_note')) {
                    $table->string('thr_contract_note', 255)->nullable()->after('thr_scheme');
                }
                if (!Schema::hasColumn('employment', 'thr_updated_by')) {
                    $table->string('thr_updated_by', 150)->nullable()->after('thr_contract_note');
                }
                if (!Schema::hasColumn('employment', 'thr_updated_at')) {
                    $table->dateTime('thr_updated_at')->nullable()->after('thr_updated_by');
                }
            });
        }

        if (Schema::hasTable('payroll_thr_items')) {
            Schema::table('payroll_thr_items', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_thr_items', 'thr_scheme')) {
                    $table->string('thr_scheme', 40)->nullable()->after('prorate_ratio');
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
                foreach (['thr_scheme', 'thr_contract_note', 'thr_updated_by', 'thr_updated_at'] as $col) {
                    if (Schema::hasColumn('employment', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('payroll_thr_items')) {
            Schema::table('payroll_thr_items', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_thr_items', 'thr_scheme')) {
                    $table->dropColumn('thr_scheme');
                }
            });
        }
    }
}
