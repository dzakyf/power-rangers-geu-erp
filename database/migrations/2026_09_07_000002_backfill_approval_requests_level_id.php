<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillApprovalRequestsLevelId extends Migration
{
    public function up()
    {
        foreach (['RENEWAL', 'TERMINATION', 'TRANSFER_MEMBERSHIP'] as $type) {
            $levelOneId = DB::table('approval_level')
                ->where('Request_Type', $type)
                ->where('Level_Number', 1)
                ->value('ID');

            if (!$levelOneId) {
                continue;
            }

            DB::table('approval_request')
                ->where('Request_Type', $type)
                ->where('Status', 'NEED_APPROVAL')
                ->where('Current_Level', 0)
                ->whereNull('Level_Id')
                ->update([
                    'Level_Id' => $levelOneId,
                    'Submitted_At' => DB::raw('`Created_At`'),
                ]);
        }
    }

    public function down()
    {
        // Data backfill only; there is no schema change to revert.
    }
}
