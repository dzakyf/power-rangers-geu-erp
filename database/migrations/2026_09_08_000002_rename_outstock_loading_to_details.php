<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameOutstockLoadingToDetails extends Migration
{
    public function up()
    {
        Schema::table('outstock_loading', function (Blueprint $table) {
            $table->unsignedBigInteger('Warehouse_ID')->nullable()->after('Container_ID');
            $table->index('Warehouse_ID');
        });

        DB::statement("
            UPDATE outstock_loading ol
            JOIN master_container mc ON ol.Container_ID = mc.ID
            SET ol.Warehouse_ID = mc.Warehouse_ID
        ");

        DB::statement('RENAME TABLE outstock_loading TO outstock_details');
        DB::statement('CREATE OR REPLACE VIEW outstock_loading AS SELECT * FROM outstock_details');
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS outstock_loading');
        DB::statement('RENAME TABLE outstock_details TO outstock_loading');

        Schema::table('outstock_loading', function (Blueprint $table) {
            $table->dropIndex(['Warehouse_ID']);
            $table->dropColumn('Warehouse_ID');
        });
    }
}
