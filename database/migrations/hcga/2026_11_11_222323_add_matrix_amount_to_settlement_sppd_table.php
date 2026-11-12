<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMatrixAmountToSettlementSppdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settlement_sppd', function (Blueprint $table) {
            if (!Schema::hasColumn('settlement_sppd', 'Matrix_Amount')) {
                $table->decimal('Matrix_Amount', 15, 2)->default(0)->after('Advance_Nominal');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settlement_sppd', function (Blueprint $table) {
            if (Schema::hasColumn('settlement_sppd', 'Matrix_Amount')) {
                $table->dropColumn('Matrix_Amount');
            }
        });
    }
}
