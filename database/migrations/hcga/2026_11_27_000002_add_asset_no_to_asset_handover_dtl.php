<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssetNoToAssetHandoverDtl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('asset_handover_dtl')) {
            Schema::table('asset_handover_dtl', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_handover_dtl', 'Asset_No')) {
                    $table->string('Asset_No', 100)->nullable()->after('Product_Code');
                    $table->index('Asset_No', 'idx_ahdtl_asset_no');
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
        if (Schema::hasTable('asset_handover_dtl') && Schema::hasColumn('asset_handover_dtl', 'Asset_No')) {
            Schema::table('asset_handover_dtl', function (Blueprint $table) {
                $table->dropIndex('idx_ahdtl_asset_no');
                $table->dropColumn('Asset_No');
            });
        }
    }
}
