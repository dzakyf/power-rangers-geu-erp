<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSyncTrackingToApprovalRequest extends Migration
{
    public function up()
    {
        if (Schema::hasTable('approval_request')) {
            Schema::table('approval_request', function (Blueprint $table) {
                if (!Schema::hasColumn('approval_request', 'Sync_Status')) {
                    $table->string('Sync_Status', 20)->nullable()->index()->after('Updated_At');
                }
                if (!Schema::hasColumn('approval_request', 'Sync_Error')) {
                    $table->text('Sync_Error')->nullable()->after('Sync_Status');
                }
                if (!Schema::hasColumn('approval_request', 'Sync_Attempts')) {
                    $table->integer('Sync_Attempts')->default(0)->after('Sync_Error');
                }
                if (!Schema::hasColumn('approval_request', 'Last_Synced_At')) {
                    $table->timestamp('Last_Synced_At')->nullable()->after('Sync_Attempts');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('approval_request')) {
            Schema::table('approval_request', function (Blueprint $table) {
                foreach (['Last_Synced_At', 'Sync_Attempts', 'Sync_Error', 'Sync_Status'] as $col) {
                    if (Schema::hasColumn('approval_request', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
