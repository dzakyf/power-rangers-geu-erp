<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill opportunity rows into the Model A stage model.
 * Existing terminal stages (Deal/Negotiation) are kept; everything else is
 * mapped from Classification/Qualification/Status and the derived
 * Classification/Qualification columns are recomputed.
 */
class BackfillPipelineStage extends Migration
{
    public function up()
    {
        DB::table('opportunity')->orderBy('ID')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                $status = trim((string) $row->Status);
                $cls    = trim((string) $row->Classification);
                $qual   = trim((string) $row->Qualification);
                $resp   = trim((string) $row->Response);

                $cancelStage = null;
                $reason      = null;

                if ($status === 'Deal') {
                    $newStatus = 'Deal';
                } elseif ($status === 'Negotiation') {
                    $newStatus = 'Negotiation';
                } elseif ($status === 'Cancel') {
                    $newStatus   = 'Cancel';
                    $cancelStage = in_array($cls, ['FU 3', 'Meeting 1', 'Meeting 2'], true) ? $cls : 'FU 3';
                    $reason      = ($qual === 'Unqualified') ? 'Unqualified' : 'Other';
                } elseif ($status === 'Cold' || $qual === 'Unidentified') {
                    $newStatus   = 'Cancel';
                    $cancelStage = 'FU 3';
                    $reason      = 'No Response';
                } elseif (in_array($cls, ['FU 1', 'FU 2', 'FU 3', 'Meeting 1', 'Meeting 2'], true)) {
                    $newStatus = $cls;
                } else {
                    $newStatus = 'Fresh';
                }

                // Response normalization
                if ($newStatus === 'Fresh') {
                    if ($resp === '') {
                        $resp = 'No Response';
                    }
                } elseif ($newStatus === 'Cancel' && $reason === 'No Response') {
                    $resp = 'No Response';
                } elseif ($resp === '') {
                    $resp = 'Response';
                }

                // Derived classification
                $dCls = [
                    'Fresh' => 'Fresh', 'FU 1' => 'FU 1', 'FU 2' => 'FU 2', 'FU 3' => 'FU 3',
                    'Qualified' => 'FU 3', 'Meeting 1' => 'Meeting 1', 'Meeting 2' => 'Meeting 2',
                    'Warm' => 'Meeting 2', 'Negotiation' => 'Meeting 2', 'Deal' => 'Meeting 2',
                ][$newStatus] ?? $cancelStage;

                // Derived qualification
                if (in_array($newStatus, ['Qualified', 'Meeting 1', 'Meeting 2', 'Warm', 'Negotiation', 'Deal'], true)) {
                    $dQual = 'Qualified';
                } elseif ($newStatus === 'Cancel' && $reason === 'Unqualified') {
                    $dQual = 'Unqualified';
                } else {
                    $dQual = null;
                }

                $update = [
                    'Status'         => $newStatus,
                    'Cancel_Stage'   => ($newStatus === 'Cancel') ? $cancelStage : null,
                    'Response'       => ($resp !== '') ? $resp : null,
                    'Classification' => $dCls,
                    'Qualification'  => $dQual,
                ];

                if ($newStatus === 'Cancel') {
                    $update['Reason'] = $reason;
                }

                DB::table('opportunity')->where('ID', $row->ID)->update($update);
            }
        });
    }

    public function down()
    {
        // Data backfill is not reversible.
    }
}
