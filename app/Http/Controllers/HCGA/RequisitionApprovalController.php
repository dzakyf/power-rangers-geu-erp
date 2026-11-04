<?php

namespace App\Http\Controllers\HCGA;

use App\Exceptions\ApprovalWorkflowException;
use App\Http\Controllers\Controller;
use App\Services\Approval\RequisitionApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequisitionApprovalController extends Controller
{
    protected $approval;

    public function __construct(RequisitionApprovalService $approval)
    {
        $this->approval = $approval;
    }

    /**
     * Halaman utama persetujuan PTK (Requisition Approval).
     */
    public function index()
    {
        $user = Auth::user();
        $location = $user->Location;
        $username = $user->username;

        $cekLevel = DB::table('master_hr_approval')
            ->where('Location', $location)
            ->whereIn('Level', [2, 3])
            ->where('username', $username)
            ->first();

        $requisitionPolicies = DB::table('approval_policy')
            ->where('Request_Type', 'REQUISITION')
            ->orderByRaw("CASE WHEN Status = 'ACTIVE' THEN 0 ELSE 1 END")
            ->orderBy('ID', 'asc')
            ->get();

        foreach ($requisitionPolicies as $pol) {
            $pol->steps = DB::table('approval_policy_step')
                ->where('Policy_ID', $pol->ID)
                ->orderBy('Stage_Order', 'asc')
                ->orderBy('Level_Number', 'asc')
                ->orderBy('ID', 'asc')
                ->get();
        }

        return view('humanresources.recruitment.requisition-approval', compact(
            'location',
            'username',
            'cekLevel',
            'requisitionPolicies'
        ));
    }

    /**
     * Data grid approval PTK.
     *
     * Requisition baru dibaca dari approval_request_task (policy engine).
     * Requisition lama yang belum punya Approval_Request_ID tetap dibaca dari
     * requisition_approval supaya approval yang sedang berjalan tidak hilang.
     */
    public function data()
    {
        $user = Auth::user();
        $rows = [];

        foreach ($this->approval->pendingRequisitions($user->Location, (int) $user->id) as $row) {
            $rows[(int) $row->ID] = $row;
        }

        foreach ($this->legacyPendingRequisitions($user->Location, $user->username) as $row) {
            if (!isset($rows[(int) $row->ID])) {
                $rows[(int) $row->ID] = $row;
            }
        }

        usort($rows, function ($a, $b) {
            return (int) $b->ID <=> (int) $a->ID;
        });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rows>';

        $nomor = 1;
        foreach ($rows as $fetch) {
            $hari = $this->agingDays($fetch->End_Date);

            $xml .= '<row id="' . (int) $fetch->ID . '">';
            $xml .= '<cell></cell>';
            $xml .= '<cell>' . $nomor . '</cell>';
            $xml .= '<cell>' . $this->xml($fetch->Task_ID) . '</cell>';
            $xml .= '<cell>' . $this->xml($fetch->Subject) . '</cell>';
            $xml .= '<cell>' . $this->xml($fetch->Job_Position) . '</cell>';
            $xml .= '<cell>' . $this->xml($fetch->PO_Desc) . '</cell>';
            $xml .= '<cell>' . $this->xml($fetch->Requirement) . '</cell>';
            $xml .= '<cell>' . $this->xml($fetch->Department) . '</cell>';
            $xml .= '<cell>' . $this->xml($fetch->Priority) . '</cell>';
            $xml .= '<cell>' . $this->xml($fetch->Qty) . '</cell>';
            $xml .= '<cell>' . $this->xml($hari) . '</cell>';
            $xml .= '<cell>' . $this->xml($fetch->Start_Date) . '</cell>';
            $xml .= '<cell>' . $this->xml($fetch->End_Date) . '</cell>';
            $xml .= '<cell>' . $this->xml($fetch->PO_No) . '</cell>';
            $xml .= '<cell>' . $this->xml($fetch->PIC_Production) . '</cell>';
            $xml .= '<cell>' . $this->xml($fetch->Status) . '</cell>';
            $xml .= '</row>';

            $nomor++;
        }

        $xml .= '</rows>';

        return response($xml, 200, [
            'Content-Type' => 'text/xml; charset=UTF-8',
        ]);
    }

    /**
     * Handle dhtmlx dataProcessor POST from requisition-approval-update grid.
     * Accepts: ids=ID1,ID2&ID1_c0=1&ID1_!nativeeditor_status=updated&customPayload_notes=...
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $params = $this->parsePayload($request->getContent());
        $ids = isset($params['ids']) ? explode(',', $params['ids']) : [];

        $output = ['actions' => []];

        foreach ($ids as $rowId) {
            $rowId = trim((string) $rowId);
            if ($rowId === 'customPayload' || $rowId === '') {
                continue;
            }

            $status = $params[$rowId . '_!nativeeditor_status'] ?? '';
            $approvedToggle = $params[$rowId . '_c0'] ?? '0';
            $notes = trim((string) ($params['customPayload_notes'] ?? ($params['customPayload[notes]'] ?? '')));

            $actionType = 'error';
            $sid = $rowId;

            try {
                if (!in_array((string) $approvedToggle, ['1', 'On', 'on', 'true'], true)) {
                    $output['actions'][] = ['type' => $actionType, 'sid' => $sid, 'tid' => $rowId];
                    continue;
                }

                $req = DB::table('requisition')->where('ID', (int) $rowId)->first();
                if (!$req) {
                    $output['actions'][] = ['type' => $actionType, 'sid' => $sid, 'tid' => $rowId];
                    continue;
                }

                if ($status === 'updated' || $status === 'updated-dir') {
                    $this->approve($req, $user, $notes);
                    $actionType = 'update';
                } elseif ($status === 'deleted') {
                    $this->reject($req, $user, $notes);
                    $actionType = 'delete';
                }
            } catch (ApprovalWorkflowException $e) {
                Log::warning('RequisitionApprovalController.update ditolak: ' . $e->getMessage(), [
                    'rowId' => $rowId,
                    'status' => $status,
                    'code' => $e->getErrorCode(),
                ]);
                $actionType = 'error';
            } catch (\Throwable $e) {
                Log::error('RequisitionApprovalController.update failed: ' . $e->getMessage(), [
                    'rowId' => $rowId,
                    'status' => $status,
                ]);
                $actionType = 'error';
            }

            $output['actions'][] = [
                'type' => $actionType,
                'sid' => $sid,
                'tid' => $rowId,
            ];
        }

        return response($this->buildXmlResponse($output), 200, [
            'Content-Type' => 'text/xml; charset=UTF-8',
        ]);
    }

    protected function approve($requisition, $user, string $notes): void
    {
        if (!empty($requisition->Approval_Request_ID)) {
            $this->approval->actOnRequisition((int) $requisition->ID, (int) $user->id, 'APPROVE', $notes);
            $this->writeLog($requisition, $user->username, 'APPROVED by ' . $user->username);
            return;
        }

        // Fallback untuk requisition lama yang belum dimigrasikan ke policy engine.
        DB::table('requisition_approval')
            ->where('Task_ID', $requisition->Task_ID)
            ->where('Approved_By', $user->username)
            ->update([
                'Approved_Status' => 1,
                'Approved_Date' => now()->format('Y-m-d H:i:s'),
                'Approved_Note' => $notes,
            ]);

        $this->writeLog($requisition, $user->username, 'APPROVED by ' . $user->username);

        $pendingCount = DB::table('requisition_approval')
            ->where('Task_ID', $requisition->Task_ID)
            ->whereIn('Approved_Status', [0, 2])
            ->count();

        DB::table('requisition')->where('ID', $requisition->ID)->update([
            'Status' => $pendingCount === 0 ? 'APPROVED' : 'ON_PROGRESS',
        ]);
    }

    protected function reject($requisition, $user, string $notes): void
    {
        if (!empty($requisition->Approval_Request_ID)) {
            $this->approval->actOnRequisition((int) $requisition->ID, (int) $user->id, 'REJECT', $notes);
            $this->writeLog($requisition, $user->username, 'REJECTED by ' . $user->username);
            return;
        }

        // Fallback untuk requisition lama yang belum dimigrasikan ke policy engine.
        DB::table('requisition_approval')
            ->where('Task_ID', $requisition->Task_ID)
            ->where('Approved_By', $user->username)
            ->update([
                'Approved_Status' => 2,
                'Approved_Date' => now()->format('Y-m-d H:i:s'),
                'Approved_Note' => $notes,
            ]);

        $this->writeLog($requisition, $user->username, 'REJECTED by ' . $user->username);

        DB::table('requisition')->where('ID', $requisition->ID)->update([
            'Status' => 'REJECTED',
        ]);
    }

    protected function writeLog($requisition, string $username, string $actionStatus): void
    {
        $logData = (array) $requisition;
        $logData['Last_ID'] = $requisition->ID;
        $logData['ID'] = null;
        $logData['Action_By'] = $username;
        $logData['Action_Date'] = now()->format('Y-m-d H:i:s');
        $logData['Action_Status'] = $actionStatus;
        unset($logData['updated_at'], $logData['Approval_Request_ID']);

        DB::table('requisition_log')->insert($logData);
    }

    protected function legacyPendingRequisitions(string $location, string $username)
    {
        return DB::table('requisition')
            ->where('Location', $location)
            ->whereNull('Approval_Request_ID')
            ->where('Status', '!=', 'CLOSED')
            ->where('Status', '!=', 'CANCELED')
            ->whereIn('Task_ID', function ($q) use ($username) {
                $q->select('ra.Task_ID')
                    ->from('requisition_approval as ra')
                    ->where('ra.Approved_By', $username)
                    ->where('ra.Approved_Status', 0)
                    ->whereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('requisition_approval as prev')
                            ->whereColumn('prev.Task_ID', 'ra.Task_ID')
                            ->whereColumn('prev.Level', '<', 'ra.Level')
                            ->where('prev.Approved_Status', 0);
                    });
            })
            ->orderBy('ID', 'DESC')
            ->get();
    }

    protected function parsePayload(string $raw): array
    {
        $params = [];

        foreach (explode('&', $raw) as $pair) {
            $kv = explode('=', $pair, 2);
            if (count($kv) === 2) {
                $params[urldecode($kv[0])] = urldecode($kv[1]);
            }
        }

        return $params;
    }

    protected function agingDays($endDate): int
    {
        if (empty($endDate)) {
            return 0;
        }

        $end = strtotime((string) $endDate);
        $now = strtotime(date('Y-m-d'));

        return (int) round(($now - $end) / 86400);
    }

    protected function xml($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    protected function buildXmlResponse(array $output): string
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
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
