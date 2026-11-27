<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Approval\ApprovalWorkflowService;
use App\Services\Approval\AssetHandoverApprovalService;
use App\Services\HCGA\AssetHandoverBastService;
use App\Services\HCGA\AssetLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssetHandoverApprovalController extends Controller
{
    /** @var AssetHandoverApprovalService */
    protected $approvalService;

    /** @var AssetHandoverBastService */
    protected $bastService;

    public function __construct(
        AssetHandoverApprovalService $approvalService,
        AssetHandoverBastService $bastService
    ) {
        $this->approvalService = $approvalService;
        $this->bastService = $bastService;
    }

    public function index()
    {
        return view('humanresources.asset-handover.approval.index');
    }

    public function data()
    {
        if (stristr($_SERVER['HTTP_ACCEPT'] ?? '', 'application/xhtml+xml')) {
            header('Content-type: application/xhtml+xml');
        } else {
            header('Content-type: text/xml');
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $productList = [];
        $products = DB::table('asset_handover_dtl')->get();
        foreach ($products as $value) {
            $productList[$value->Handover_No][] = $value->Description;
        }

        $user = Auth::user();
        $priv = $user->Priv_ID;
        $nik = $user->Emp_ID;
        $userId = (int) $user->id;

        $getHandover = DB::table('asset_handover')
            ->whereIn('Status', ['CREATED', 'UPDATED', 'WAITING_APPROVAL'])
            ->orderBy('ID', 'DESC');

        if ($priv != 'HCGA' && $priv != 'ICT') {
            $candidateReqIds = [];
            if (Schema::hasTable('approval_task_candidate')) {
                $candidateReqIds = DB::table('approval_request_task as t')
                    ->join('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
                    ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
                    ->where('r.Request_Type', AssetHandoverApprovalService::TYPE_HANDOVER)
                    ->whereColumn('t.Stage_Order', 'r.Current_Stage')
                    ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
                    ->where('c.User_Id', $userId)
                    ->where('c.Status', 'ACTIVE')
                    ->pluck('r.ID')
                    ->all();
            }

            $getHandover->where(function ($q) use ($nik, $candidateReqIds) {
                $q->where('PIC_NIK', $nik);
                if (Schema::hasColumn('asset_handover', 'Lead_NIK')) {
                    $q->orWhere('Lead_NIK', $nik);
                }
                if (!empty($candidateReqIds) && Schema::hasColumn('asset_handover', 'Approval_Request_ID')) {
                    $q->orWhereIn('Approval_Request_ID', $candidateReqIds);
                }
            });
        }
        $getHandover = $getHandover->get();

        $bastCounts = $this->bastService->getCountsByHandoverNos($getHandover->pluck('Handover_No')->all());

        $reqStages = [];
        $reqIds = $getHandover->pluck('Approval_Request_ID')->filter()->all();
        if (!empty($reqIds) && Schema::hasTable('approval_request')) {
            $reqStages = DB::table('approval_request')
                ->whereIn('ID', $reqIds)
                ->pluck('Current_Stage', 'ID')
                ->all();
        }

        $nomor = 1;
        echo '<rows>';
        foreach ($getHandover as $value) {
            $cntH = $bastCounts[$value->Handover_No]['HANDOVER'] ?? 0;
            $bastLabel = $cntH > 0 ? "Draft PDF + {$cntH} Lamp." : 'Draft PDF';
            $stageLabel = !empty($value->Approval_Request_ID) && isset($reqStages[$value->Approval_Request_ID])
                ? ('Stage ' . $reqStages[$value->Approval_Request_ID])
                : ($value->Status ?? 'CREATED');

            echo "<row id='{$value->ID}'>";
                echo "<cell>{$nomor}</cell>";
                echo '<cell>' . htmlspecialchars((string) $value->Handover_No) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Handover_Date) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $stageLabel) . '</cell>';
                echo '<cell>' . htmlspecialchars($bastLabel) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->User_Name) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->User_NIK) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) ($value->Lead_Name ?? '-')) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->PIC_Name) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->PIC_Department) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->PIC_Phone) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Reference) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Remark) . '</cell>';
                echo '<cell>' . htmlspecialchars(implode(', ', $productList[$value->Handover_No] ?? [])) . '</cell>';
            echo '</row>';

            $nomor++;
        }
        echo '</rows>';
    }

    public function detail(Request $request)
    {
        $ah = DB::table('asset_handover')->where('Handover_No', $request->Handover_No)->first();
        if (!$ah) {
            abort(404, 'Data Handover tidak ditemukan.');
        }

        if (!$this->bastService->canViewHandover($ah, Auth::user())) {
            abort(403, 'Anda tidak berwenang melihat approval handover ini.');
        }

        $bastHandoverDocs = $this->bastService->listDocuments($ah->Handover_No, AssetHandoverBastService::DOC_TYPE_HANDOVER, Auth::user());
        $approvalTrail    = !empty($ah->Approval_Request_ID)
            ? app(ApprovalWorkflowService::class)->buildApprovalTrail((int) $ah->Approval_Request_ID)
            : [];
        $canAct           = true;
        $requiresBastNow  = true;

        if (!empty($ah->Approval_Request_ID)) {
            $tasks = $this->approvalService->pendingTasksForUser((int) $ah->Approval_Request_ID, (int) Auth::id());
            $canAct = !empty($tasks);
            $req = app(ApprovalWorkflowService::class)->getRequest((int) $ah->Approval_Request_ID);
            $requiresBastNow = $req ? $this->approvalService->isOnFinalStage($req) : true;
        }

        return view('humanresources.asset-handover.approval.detail', compact(
            'ah',
            'bastHandoverDocs',
            'approvalTrail',
            'canAct',
            'requiresBastNow'
        ));
    }

    public function detailData(Request $request)
    {
        $ah = DB::table('asset_handover')->where('Handover_No', $request->Handover_No)->first();
        if (!$ah || !$this->bastService->canViewHandover($ah, Auth::user())) {
            abort(403, 'Anda tidak berwenang mengakses detail handover ini.');
        }

        if (stristr($_SERVER['HTTP_ACCEPT'] ?? '', 'application/xhtml+xml')) {
            header('Content-type: application/xhtml+xml');
        } else {
            header('Content-type: text/xml');
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $getHandover = DB::table('asset_handover_dtl')->where('Handover_No', $request->Handover_No)->get();
        $nomor = 1;
        echo '<rows>';
        foreach ($getHandover as $value) {
            echo "<row id='{$value->ID}'>";
                echo "<cell>{$nomor}</cell>";
                echo '<cell>' . htmlspecialchars((string) $value->Purchase_Type) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Product_Code) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Description) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Qty) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Product_Brand) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Product_Type) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Specification) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Condition) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Unit) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Remark) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Category) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Purchase_Category) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Default_Price) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Prices) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Received_Date) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) ($value->Asset_No ?? '')) . '</cell>';
            echo '</row>';
            $nomor++;
        }
        echo '</rows>';
    }

    public function store(Request $request)
    {
        $r = $request->all();

        $handoverNo = trim((string) ($r['Handover_No'] ?? ''));
        $status = strtoupper(trim((string) ($r['status'] ?? 'APPROVED')));
        $approvalNote = (string) ($r['Approval_Note'] ?? '');

        if ($handoverNo === '') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Handover_No tidak ditemukan',
            ], 422);
        }

        if (!in_array($status, ['APPROVED', 'REJECTED'], true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Status approval tidak valid (harus APPROVED atau REJECTED).',
            ], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sesi login tidak ditemukan.',
            ], 401);
        }
        $username = (string) $user->username;

        $dataHandover = DB::table('asset_handover')->where('Handover_No', $handoverNo)->first();
        if (!$dataHandover) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data Handover tidak ditemukan',
            ], 404);
        }

        try {
            // Jalur modern: terintegrasi ke ApprovalWorkflowService (2 tahap: Lead Pemberi -> Penerima PIC)
            if (!empty($dataHandover->Approval_Request_ID)) {
                $action = $status === 'REJECTED' ? 'REJECT' : 'APPROVE';
                $this->approvalService->actOnHandover(
                    (int) $dataHandover->Approval_Request_ID,
                    (int) Auth::id(),
                    $action,
                    $approvalNote
                );

                return response()->json([
                    'status'  => 'success',
                    'message' => "Berhasil memproses approval handover {$handoverNo}",
                ]);
            }

            if (!$this->bastService->canViewHandover($dataHandover, Auth::user())) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Anda tidak berwenang memproses approval handover ini.',
                ], 403);
            }

            if (in_array(strtoupper((string) ($dataHandover->Status ?? '')), ['APPROVED', 'RETURNED', 'REJECTED', 'CANCELLED'], true)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Transaksi {$handoverNo} sudah berstatus {$dataHandover->Status} dan tidak dapat diproses ulang.",
                ], 422);
            }

            // Jalur fallback untuk transaksi legacy sebelum Approval_Request_ID
            DB::transaction(function () use ($handoverNo, $status, $approvalNote, $username, $dataHandover) {
                DB::table('asset_handover')->where('Handover_No', $handoverNo)->update([
                    'Status'        => $status,
                    'Approval_Note' => $approvalNote,
                    'Updated_By'    => $username,
                    'Updated_Date'  => now(),
                ]);

                if ($status === 'APPROVED') {
                    DB::table('asset_handover_dtl')
                        ->where('Handover_No', $handoverNo)
                        ->whereNotIn('Status', ['RETURNED'])
                        ->update([
                            'Status'        => 'APPROVED',
                            'Received_Date' => DB::raw('COALESCE(Received_Date, CURDATE())'),
                        ]);

                    $this->approvalService->syncAssetListOnHandoverApproved($dataHandover, $username, $approvalNote);
                    $fresh = DB::table('asset_handover')->where('ID', $dataHandover->ID)->first();
                    $this->bastService->writeAuditLog($fresh ?: $dataHandover, $username, 'HANDOVER_APPROVED', $approvalNote ?: 'Handover disetujui');
                } elseif ($status === 'REJECTED') {
                    DB::table('asset_handover_dtl')
                        ->where('Handover_No', $handoverNo)
                        ->whereNotIn('Status', ['RETURNED'])
                        ->update(['Status' => 'REJECTED']);

                    $fresh = DB::table('asset_handover')->where('ID', $dataHandover->ID)->first();
                    $this->bastService->writeAuditLog($fresh ?: $dataHandover, $username, 'HANDOVER_REJECTED', $approvalNote ?: 'Handover ditolak');
                }
            });

            return response()->json([
                'status'  => 'success',
                'message' => "Berhasil memproses approval handover {$handoverNo}",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function storeDetail()
    {
        echo '<data>';
        $rawBody = file_get_contents('php://input');

        $param = [];
        $pairs = explode('&', (string) $rawBody);
        foreach ($pairs as $datax) {
            if ($datax === '') {
                continue;
            }
            $idenya = explode('=', $datax, 2);
            if (count($idenya) === 2) {
                $param[urldecode($idenya[0])] = urldecode($idenya[1]);
            }
        }
        if (empty($param['ids'])) {
            echo '</data>';
            return;
        }
        $checkids = explode(',', $param['ids']);
        for ($i = 0; $i < sizeof($checkids); $i++) {
            $action = '';
            $rowId = $checkids[$i];
            $newId = $rowId;
            $mode = $param[$rowId . '_!nativeeditor_status'] ?? '';
            switch ($mode) {
                case 'updated':
                    $action = $this->storeDetailUpdate($rowId, $param);
                    break;
            }
            echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
        }
        echo '</data>';
    }

    function storeDetailUpdate(string $rowId, array $param)
    {
        $check = $param[$rowId . '_c0'] ?? 0;
        $o = $param[$rowId . '_c15'] ?? null;
        $itemStatus = $param[$rowId . '_c16'] ?? 'APPROVED';

        if ($o == '') {
            $o = null;
        }

        if ($check == '' || $check == '0') {
            return 'not update';
        }

        $dtl = DB::table('asset_handover_dtl')->where('ID', $rowId)->first();
        if (!$dtl) {
            return 'not update';
        }

        $parent = DB::table('asset_handover')->where('Handover_No', $dtl->Handover_No)->first();
        if (!$parent || !$this->bastService->canViewHandover($parent, Auth::user())) {
            return 'error';
        }

        if (!empty($parent->Approval_Request_ID)) {
            return 'not update';
        }

        DB::table('asset_handover_dtl')->where('ID', $rowId)->update([
            'Status'        => $itemStatus,
            'Received_Date' => $o,
        ]);

        return 'update';
    }
}
