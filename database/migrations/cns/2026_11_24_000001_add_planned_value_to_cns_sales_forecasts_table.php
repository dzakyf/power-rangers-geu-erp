<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlannedValueToCnsSalesForecastsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cns_sales_forecasts') && !Schema::hasColumn('cns_sales_forecasts', 'planned_value')) {
            Schema::table('cns_sales_forecasts', function (Blueprint $table) {
                $table->decimal('planned_value', 18, 2)->default(0)->after('planned_mt');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('cns_sales_forecasts') && Schema::hasColumn('cns_sales_forecasts', 'planned_value')) {
            Schema::table('cns_sales_forecasts', function (Blueprint $table) {
                $table->dropColumn('planned_value');
            });
        }
    }
}
