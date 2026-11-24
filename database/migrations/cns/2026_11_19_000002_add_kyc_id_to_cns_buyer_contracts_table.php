<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKycIdToCnsBuyerContractsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cns_buyer_contracts') && !Schema::hasColumn('cns_buyer_contracts', 'kyc_id')) {
            Schema::table('cns_buyer_contracts', function (Blueprint $table) {
                $table->unsignedBigInteger('kyc_id')->nullable()->index()->after('Supplier_ID');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('cns_buyer_contracts') && Schema::hasColumn('cns_buyer_contracts', 'kyc_id')) {
            Schema::table('cns_buyer_contracts', function (Blueprint $table) {
                $table->dropColumn('kyc_id');
            });
        }
    }
}
