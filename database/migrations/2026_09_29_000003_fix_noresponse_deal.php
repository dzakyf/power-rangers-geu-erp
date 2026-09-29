<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rule #5: a no-response lead cannot be Deal -> move to Cancel (reason No Response).
 */
class FixNoresponseDeal extends Migration
{
    public function up()
    {
        DB::table('opportunity')
            ->where('Status', 'Deal')
            ->where(function ($q) {
                $q->where('Response', 'No Response')
                  ->orWhere('Qualification', 'Unidentified');
            })
            ->update([
                'Status'         => 'Cancel',
                'Cancel_Stage'   => 'FU 3',
                'Reason'         => 'No Response',
                'Response'       => 'No Response',
                'Classification' => 'FU 3',
                'Qualification'  => null,
            ]);
    }

    public function down()
    {
        // not reversible
    }
}
