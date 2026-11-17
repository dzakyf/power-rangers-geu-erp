<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusAndSiteIdToSalesOrderCnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sales_order_cns')) {
            Schema::table('sales_order_cns', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_order_cns', 'status')) {
                    $table->string('status', 50)->default('PENDING')->index();
                }
                if (!Schema::hasColumn('sales_order_cns', 'site_id')) {
                    $table->unsignedBigInteger('site_id')->nullable()->index();
                }
                if (!Schema::hasColumn('sales_order_cns', 'approved_by')) {
                    $table->string('approved_by', 150)->nullable();
                }
                if (!Schema::hasColumn('sales_order_cns', 'approved_date')) {
                    $table->dateTime('approved_date')->nullable();
                }
                if (!Schema::hasColumn('sales_order_cns', 'rejected_by')) {
                    $table->string('rejected_by', 150)->nullable();
                }
                if (!Schema::hasColumn('sales_order_cns', 'rejected_date')) {
                    $table->dateTime('rejected_date')->nullable();
                }
                if (!Schema::hasColumn('sales_order_cns', 'rejection_reason')) {
                    $table->string('rejection_reason', 255)->nullable();
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
        if (Schema::hasTable('sales_order_cns')) {
            Schema::table('sales_order_cns', function (Blueprint $table) {
                $columns = ['status', 'site_id', 'approved_by', 'approved_date', 'rejected_by', 'rejected_date', 'rejection_reason'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('sales_order_cns', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}
