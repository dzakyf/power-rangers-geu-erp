<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateSellerInCnsOfferingLettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cns_offering_letters')) {
            Schema::table('cns_offering_letters', function (Blueprint $table) {
                if (!Schema::hasColumn('cns_offering_letters', 'seller_id')) {
                    $table->unsignedBigInteger('seller_id')->nullable()->after('offer_date')->index();
                }
            });

            try {
                DB::statement('ALTER TABLE cns_offering_letters MODIFY COLUMN seller_code VARCHAR(50)');
            } catch (\Throwable $e) {
                // If already modified or SQLite in testing, silently continue
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
        if (Schema::hasTable('cns_offering_letters')) {
            Schema::table('cns_offering_letters', function (Blueprint $table) {
                if (Schema::hasColumn('cns_offering_letters', 'seller_id')) {
                    $table->dropColumn('seller_id');
                }
            });
        }
    }
}
