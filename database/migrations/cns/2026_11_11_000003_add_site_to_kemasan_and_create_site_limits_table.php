<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSiteToKemasanAndCreateSiteLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cns_inventory_kemasan')) {
            Schema::table('cns_inventory_kemasan', function (Blueprint $table) {
                if (!Schema::hasColumn('cns_inventory_kemasan', 'site_id')) {
                    $table->unsignedBigInteger('site_id')->nullable()->after('transaction_date')->index();
                }
                if (!Schema::hasColumn('cns_inventory_kemasan', 'site_name')) {
                    $table->string('site_name', 100)->nullable()->after('site_id')->index();
                }
            });
        }

        if (!Schema::hasTable('cns_kemasan_site_limits')) {
            Schema::create('cns_kemasan_site_limits', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('kemasan_id')->index();
                $table->unsignedBigInteger('site_id')->index();
                $table->decimal('max_stock', 12, 2)->default(0);
                $table->timestamps();

                $table->unique(['kemasan_id', 'site_id']);
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
        Schema::dropIfExists('cns_kemasan_site_limits');

        if (Schema::hasTable('cns_inventory_kemasan')) {
            Schema::table('cns_inventory_kemasan', function (Blueprint $table) {
                foreach (['site_name', 'site_id'] as $col) {
                    if (Schema::hasColumn('cns_inventory_kemasan', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
