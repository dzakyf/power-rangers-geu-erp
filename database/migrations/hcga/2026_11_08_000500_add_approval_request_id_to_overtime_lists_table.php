<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalRequestIdToOvertimeListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('overtime_lists')) {
            Schema::table('overtime_lists', function (Blueprint $table) {
                if (!Schema::hasColumn('overtime_lists', 'approval_request_id')) {
                    $table->bigInteger('approval_request_id')->unsigned()->nullable()->after('Submit_Approval');
                    $table->index('approval_request_id');
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
        if (Schema::hasTable('overtime_lists')) {
            Schema::table('overtime_lists', function (Blueprint $table) {
                if (Schema::hasColumn('overtime_lists', 'approval_request_id')) {
                    $table->dropIndex(['approval_request_id']);
                    $table->dropColumn('approval_request_id');
                }
            });
        }
    }
}

