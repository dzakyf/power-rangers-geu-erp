<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalRequestIdToSppdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sppd')) {
            Schema::table('sppd', function (Blueprint $table) {
                if (!Schema::hasColumn('sppd', 'Approval_Request_ID')) {
                    $table->unsignedBigInteger('Approval_Request_ID')->nullable()->after('Status')->index();
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
        if (Schema::hasTable('sppd')) {
            Schema::table('sppd', function (Blueprint $table) {
                if (Schema::hasColumn('sppd', 'Approval_Request_ID')) {
                    $table->dropColumn('Approval_Request_ID');
                }
            });
        }
    }
}

