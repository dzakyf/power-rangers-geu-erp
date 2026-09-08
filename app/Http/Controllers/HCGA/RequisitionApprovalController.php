<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequisitionApprovalController extends Controller
{
    /**
     * Handle dhtmlx dataProcessor POST from requisition-approval-update grid.
     * Accepts: ids=ID1,ID2&ID1_c0=1&ID1_!nativeeditor_status=updated&customPayload_notes=...
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $saya  = $user->username;
        $LSP   = $user->Location;

        // Parse dhtmlx proprietary POST body
        $raw    = $request->getContent();
        $pairs  = explode('&', $raw);
        $params = [];
        foreach ($pairs as $pair) {
            $kv = explode('=', $pair, 2);
            if (count($kv) === 2) {
                $params[urldecode($kv[0])] = urldecode($kv[1]);
            }
        }

        $ids = isset($params['ids']) ? explode(',', $params['ids']) : [];

        $output = ['actions' => []];

        foreach ($ids as $rowId) {
            if ($rowId === 'customPayload' || $rowId === '') {
                continue;
            }

            $status  = $params[$rowId . '_!nativeeditor_status'] ?? '';
            $approvedToggle = $params[$rowId . '_c0'] ?? '0';
            $notes   = isset($params['customPayload_notes'])
                ? htmlspecialchars($params['customPayload_notes'])
                : '';

            $actionType = 'error';
            $sid        = $rowId;

            try {
                // --- APPROVE ---
                if ($status === 'updated' || $status === 'updated-dir') {
                    if ($approvedToggle == 1 || $approvedToggle === 'On' || $approvedToggle === 'on') {
                        $req = DB::table('requisition')->where('ID', $rowId)->first();
                        if (!$req) {
                            $actionType = 'error';
                            continue;
                        }

                        // Update the specific approval record belonging to this user
                        DB::table('requisition_approval')
                            ->where('Task_ID', $req->Task_ID)
                            ->where('Approved_By', $saya)
                            ->update([
                                'Approved_Status' => 1,
                                'Approved_Date'   => now()->format('Y-m-d H:i:s'),
                                'Approved_Note'   => $notes,
                            ]);

                        // Log
                        $logData = (array) $req;
                        $logData['Last_ID']       = $req->ID;
                        $logData['ID']            = null;
                        $logData['Action_By']     = $saya;
                        $logData['Action_Date']   = now()->format('Y-m-d H:i:s');
                        $logData['Action_Status'] = 'APPROVED by ' . $saya;
                        unset($logData['updated_at']);
                        DB::table('requisition_log')->insert($logData);

                        // Compute overall status
                        $pendingCount = DB::table('requisition_approval')
                            ->where('Task_ID', $req->Task_ID)
                            ->whereIn('Approved_Status', [0, 2])
                            ->count();

                        $newStatus = $pendingCount === 0 ? 'APPROVED' : 'ON_PROGRESS';
                        DB::table('requisition')->where('ID', $rowId)->update(['Status' => $newStatus]);

                        $actionType = 'update';
                    }
                }

                // --- REJECT ---
                if ($status === 'deleted') {
                    if ($approvedToggle == 1 || $approvedToggle === 'On' || $approvedToggle === 'on') {
                        $req = DB::table('requisition')->where('ID', $rowId)->first();
                        if (!$req) {
                            $actionType = 'error';
                            continue;
                        }

                        DB::table('requisition_approval')
                            ->where('Task_ID', $req->Task_ID)
                            ->where('Approved_By', $saya)
                            ->update([
                                'Approved_Status' => 2,
                                'Approved_Date'   => now()->format('Y-m-d H:i:s'),
                                'Approved_Note'   => $notes,
                            ]);

                        $logData = (array) $req;
                        $logData['Last_ID']       = $req->ID;
                        $logData['ID']            = null;
                        $logData['Action_By']     = $saya;
                        $logData['Action_Date']   = now()->format('Y-m-d H:i:s');
                        $logData['Action_Status'] = 'REJECTED by ' . $saya;
                        unset($logData['updated_at']);
                        DB::table('requisition_log')->insert($logData);

                        DB::table('requisition')->where('ID', $rowId)->update(['Status' => 'REJECTED']);

                        $actionType = 'delete';
                    }
                }
            } catch (\Exception $e) {
                Log::error('RequisitionApprovalController.update failed: ' . $e->getMessage(), [
                    'rowId' => $rowId,
                    'status' => $status,
                ]);
                $actionType = 'error';
            }

            $output['actions'][] = [
                'type' => $actionType,
                'sid'  => $sid,
                'tid'  => $rowId,
            ];
        }

        // Return XML response (matching the dhtmlx dataProcessor expectation)
        return response($this->buildXmlResponse($output), 200, [
            'Content-Type' => 'text/xml',
        ]);
    }

    private function buildXmlResponse(array $output): string
    {
        $xml  = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";
        $xml .= "<data>\n";
        foreach ($output['actions'] as $a) {
            $xml .= sprintf(
                "  <action type='%s' sid='%s' tid='%s'/>\n",
                htmlspecialchars($a['type']),
                htmlspecialchars($a['sid']),
                htmlspecialchars($a['tid'])
            );
        }
        $xml .= "</data>";
        return $xml;
    }
}
