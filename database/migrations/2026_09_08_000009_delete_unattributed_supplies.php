<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Remove legacy stock-in rows with no container attribution (Container_ID NULL).
 * Unattributed stock is not part of any container balance and caused confusing
 * "system qty = 0" states; the concept is dropped.
 */
class DeleteUnattributedSupplies extends Migration
{
    public function up()
    {
        DB::table('warehouse_partner_supplies')->whereNull('Container_ID')->delete();
    }

    public function down()
    {
        // Irreversible data cleanup.
    }
}
