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

class AssetHandoverReturnedApprovalController extends Controller
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
        return view('humanresources.asset-handover.returned.approval.index');
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
        $products = DB::table('asset_handover_dtl')->whereIn('Status', ['RETURN_PENDING', 'RETURNED'])->get();
        foreach ($products as $value) {
            $productList[$value->Handover_No][] = $value->Description;
        }

        $user = Auth::user();
        $priv = $user->Priv_ID;
        $nik = $user->Emp_ID;
        $userId = (int) $user->id;

        $pendingHandoverNos = DB::table('asset_handover_dtl')
            ->whereIn('Status', ['RETURN_PENDING', 'RETURNED'])
            ->pluck('Handover_No')
            ->toArray();

        $getHandover = DB::table('asset_handover')
            ->whereIn('Handover_No', $pendingHandoverNos ?: ['__NONE__'])
            ->orderBy('ID', 'DESC');

        if ($priv != 'HCGA' && $priv != 'ICT') {
            $candidateReqIds = [];
            if (Schema::hasTable('approval_task_candidate')) {
                $candidateReqIds = DB::table('approval_request_task as t')
                    ->join('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
                    ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
                    ->where('r.Request_Type', AssetHandoverApprovalService::TYPE_RETURN)
                    ->whereColumn('t.Stage_Order', 'r.Current_Stage')
                    ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
                    ->where('c.User_Id', $userId)
                    ->where('c.Status', 'ACTIVE')
                    ->pluck('r.ID')
                    ->all();
            }

            $getHandover->where(function ($q) use ($nik, $candidateReqIds) {
                $q->where('PIC_NIK', $nik);
                if (Schema::hasColumn('asset_handover', 'Return_Lead_NIK')) {
                    $q->orWhere('Return_Lead_NIK', $nik)
                      ->orWhere('Return_Receiver_NIK', $nik);
                }
                if (!empty($candidateReqIds) && Schema::hasColumn('asset_handover', 'Return_Approval_Request_ID')) {
                    $q->orWhereIn('Return_Approval_Request_ID', $candidateReqIds);
                }
            });
        }
        $getHandover = $getHandover->get();

        $bastCounts = $this->bastService->getCountsByHandoverNos($getHandover->pluck('Handover_No')->all());

        $reqStages = [];
        if (Schema::hasColumn('asset_handover', 'Return_Approval_Request_ID') && Schema::hasTable('approval_request')) {
            $reqIds = $getHandover->pluck('Return_Approval_Request_ID')->filter()->all();
            if (!empty($reqIds)) {
                $reqStages = DB::table('approval_request')
                    ->whereIn('ID', $reqIds)
                    ->pluck('Current_Stage', 'ID')
                    ->all();
            }
        }

        $nomor = 1;
        echo '<rows>';
        foreach ($getHandover as $value) {
            $cntR = $bastCounts[$value->Handover_No]['RETURN'] ?? 0;
            $bastRStatus = strtoupper((string) ($value->Status ?? '')) === 'RETURNED' ? 'Resmi (PDF)' : 'Draft (PDF)';
            $bastRLabel = $cntR > 0 ? "{$bastRStatus} + {$cntR} Lamp." : $bastRStatus;
            $stageLabel = !empty($value->Return_Approval_Request_ID) && isset($reqStages[$value->Return_Approval_Request_ID])
                ? ('Stage ' . $reqStages[$value->Return_Approval_Request_ID])
                : ($value->Status ?? '');

            echo "<row id='{$value->ID}'>";
                echo "<cell>{$nomor}</cell>";
                echo '<cell>' . htmlspecialchars((string) $value->Handover_No) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Handover_Date) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $stageLabel) . '</cell>';
                echo '<cell>' . htmlspecialchars($bastRLabel) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->User_Name) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->User_NIK) . '</cell>';
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
            abort(403, 'Anda tidak berwenang mengakses approval pengembalian ini.');
        }

        $bastReturnDocs      = $this->bastService->listDocuments($ah->Handover_No, AssetHandoverBastService::DOC_TYPE_RETURN, Auth::user());
        $returnApprovalTrail = !empty($ah->Return_Approval_Request_ID)
            ? app(ApprovalWorkflowService::class)->buildApprovalTrail((int) $ah->Return_Approval_Request_ID)
            : [];
        $canAct              = true;
        $requiresBastNow     = true;

        if (!empty($ah->Return_Approval_Request_ID)) {
            $tasks = $this->approvalService->pendingTasksForUser((int) $ah->Return_Approval_Request_ID, (int) Auth::id());
            $canAct = !empty($tasks);
            $req = app(ApprovalWorkflowService::class)->getRequest((int) $ah->Return_Approval_Request_ID);
            $requiresBastNow = $req ? $this->approvalService->isOnFinalStage($req) : true;
        }

        return view('humanresources.asset-handover.returned.approval.detail', compact(
            'ah',
            'bastReturnDocs',
            'returnApprovalTrail',
            'canAct',
            'requiresBastNow'
        ));
    }

    public function detailData(Request $request)
    {
        $ah = DB::table('asset_handover')->where('Handover_No', $request->Handover_No)->first();
        if (!$ah || !$this->bastService->canViewHandover($ah, Auth::user())) {
            abort(403, 'Anda tidak berwenang mengakses detail pengembalian ini.');
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
                echo '<cell>' . htmlspecialchars((string) $value->Status) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) ($value->Returned_Qty ?? $value->Qty)) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) ($value->Returned_Condition ?? 'Baik')) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) ($value->Returned_Date ?? date('Y-m-d'))) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) ($value->Returned_Remark ?? '')) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) ($value->Asset_No ?? '')) . '</cell>';
            echo '</row>';
            $nomor++;
        }
        echo '</rows>';
    }

    public function store(Request $request)
    {
        $r = $request->all();
        $handoverNo   = trim((string) ($r['Handover_No'] ?? ''));
        $status       = strtoupper(trim((string) ($r['status'] ?? 'RETURNED')));
        $approvalNote = (string) ($r['Approval_Note'] ?? '');

        if ($handoverNo === '') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Handover_No tidak ditemukan',
            ], 422);
        }

        if (!in_array($status, ['RETURNED', 'APPROVED', 'REJECTED'], true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Status approval pengembalian tidak valid.',
            ], 422);
        }

        $handover = DB::table('asset_handover')->where('Handover_No', $handoverNo)->first();
        if (!$handover) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data Handover tidak ditemukan',
            ], 404);
        }

        $action = $status === 'RETURNED' ? 'APPROVE' : 'REJECT';

        try {
            // Jalur modern: terintegrasi dengan ApprovalWorkflowService (2 tahap: PIC Approval -> Penerima Pengembalian)
            if (!empty($handover->Return_Approval_Request_ID)) {
                $this->approvalService->actOnReturn(
                    (int) $handover->Return_Approval_Request_ID,
                    (int) Auth::id(),
                    $action,
                    $approvalNote
                );

                return response()->json([
                    'status'  => 'success',
                    'message' => "Berhasil memproses approval pengembalian {$handoverNo}",
                ]);
            }

            if (!$this->bastService->canViewHandover($handover, Auth::user())) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Anda tidak berwenang memproses approval pengembalian ini.',
                ], 403);
            }

            // Jalur fallback legacy tanpa Return_Approval_Request_ID
            DB::transaction(function () use ($handover, $action, $approvalNote) {
                $username = Auth::user()->username ?? 'system';
                $this->approvalService->processReturnApprovalOutcome(
                    $handover,
                    $action === 'APPROVE',
                    $username,
                    $approvalNote
                );
            });

            return response()->json([
                'status'  => 'success',
                'message' => "Berhasil memproses approval pengembalian {$handoverNo}",
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
        $targetStatus = $param[$rowId . '_c16'] ?? 'RETURNED';

        if ($check == '' || $check == '0') {
            return 'not update';
        }

        // Jika sudah terhubung Return_Approval_Request_ID, jangan ubah status menjadi RETURNED
        // sebelum seluruh stage pada ApprovalWorkflowService selesai (syncReturnFromWorkflow).
        $dtl = DB::table('asset_handover_dtl')->where('ID', $rowId)->first();
        if (!$dtl) {
            return 'not update';
        }

        $parent = DB::table('asset_handover')->where('Handover_No', $dtl->Handover_No)->first();
        if (!$parent || !$this->bastService->canViewHandover($parent, Auth::user())) {
            return 'error';
        }
        if (!empty($parent->Return_Approval_Request_ID)) {
            return 'not update';
        }

        DB::table('asset_handover_dtl')->where('ID', $rowId)->update([
            'Status' => $targetStatus,
        ]);

        return 'update';
    }
}
