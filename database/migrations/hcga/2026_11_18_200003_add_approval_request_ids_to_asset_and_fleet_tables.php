<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalRequestIdsToAssetAndFleetTables extends Migration
{
    public function up()
    {
        if (Schema::hasTable('asset_maintenance_schedule') && !Schema::hasColumn('asset_maintenance_schedule', 'Approval_Request_ID')) {
            Schema::table('asset_maintenance_schedule', function (Blueprint $table) {
                $table->unsignedBigInteger('Approval_Request_ID')->nullable()->after('Status')->index('idx_ams_approval_req');
            });
        }

        if (Schema::hasTable('vehicle_tax_request') && !Schema::hasColumn('vehicle_tax_request', 'Approval_Request_ID')) {
            Schema::table('vehicle_tax_request', function (Blueprint $table) {
                $table->unsignedBigInteger('Approval_Request_ID')->nullable()->after('Status')->index('idx_vtr_approval_req');
            });
        }

        if (Schema::hasTable('asset_list') && !Schema::hasColumn('asset_list', 'Fleet_Approval_Request_ID')) {
            Schema::table('asset_list', function (Blueprint $table) {
                $table->unsignedBigInteger('Fleet_Approval_Request_ID')->nullable()->after('Fleet_Approval_Status')->index('idx_asset_fleet_approval_req');
            });
        }

        if (Schema::hasTable('vehicle_logbook') && !Schema::hasColumn('vehicle_logbook', 'Approval_Request_ID')) {
            Schema::table('vehicle_logbook', function (Blueprint $table) {
                $table->unsignedBigInteger('Approval_Request_ID')->nullable()->after('Approval_Status')->index('idx_vlog_approval_req');
            });
        }
    }

    public function down()
    {
        foreach ([
            ['asset_maintenance_schedule', 'Approval_Request_ID'],
            ['vehicle_tax_request', 'Approval_Request_ID'],
            ['asset_list', 'Fleet_Approval_Request_ID'],
            ['vehicle_logbook', 'Approval_Request_ID'],
        ] as $pair) {
            if (Schema::hasTable($pair[0]) && Schema::hasColumn($pair[0], $pair[1])) {
                Schema::table($pair[0], function (Blueprint $table) use ($pair) {
                    $table->dropColumn($pair[1]);
                });
            }
        }
    }
}
