<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Approval\ApprovalWorkflowService;
use App\Services\Approval\AssetHandoverApprovalService;
use App\Services\HCGA\AssetHandoverBastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssetHandoverReturnedController extends Controller
{
    /** @var AssetHandoverBastService */
    protected $bastService;

    /** @var AssetHandoverApprovalService */
    protected $approvalService;

    public function __construct(
        AssetHandoverBastService $bastService,
        AssetHandoverApprovalService $approvalService
    ) {
        $this->bastService = $bastService;
        $this->approvalService = $approvalService;
    }

    public function index()
    {
        return view('humanresources.asset-handover.returned.index');
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

        $priv = Auth::user()->Priv_ID;
        $nik = Auth::user()->Emp_ID;

        // Menampilkan dokumen handover yang sudah APPROVED, UPDATED, atau RETURNED sebagian/penuh
        $getHandover = DB::table('asset_handover')
            ->whereIn('Status', ['APPROVED', 'UPDATED', 'RETURNED'])
            ->orderBy('ID', 'DESC');

        if ($priv != 'HCGA' && $priv != 'ICT') {
            $getHandover->where(function ($q) use ($nik) {
                $q->where('User_NIK', $nik)
                  ->orWhere('PIC_NIK', $nik);
                if (Schema::hasColumn('asset_handover', 'Lead_NIK')) {
                    $q->orWhere('Lead_NIK', $nik);
                }
                if (Schema::hasColumn('asset_handover', 'Return_Lead_NIK')) {
                    $q->orWhere('Return_Lead_NIK', $nik)
                      ->orWhere('Return_Receiver_NIK', $nik);
                }
            });
        }
        $getHandover = $getHandover->get();

        $bastCounts = $this->bastService->getCountsByHandoverNos($getHandover->pluck('Handover_No')->all());

        $nomor = 1;
        echo '<rows>';
        foreach ($getHandover as $value) {
            $cntR = $bastCounts[$value->Handover_No]['RETURN'] ?? 0;
            $bastRStatus = strtoupper((string) ($value->Status ?? '')) === 'RETURNED' ? 'Resmi (PDF)' : 'Draft (PDF)';
            $bastRLabel = $cntR > 0 ? "{$bastRStatus} + {$cntR} Lamp." : $bastRStatus;

            echo "<row id='{$value->ID}'>";
                echo "<cell>{$nomor}</cell>";
                echo '<cell>' . htmlspecialchars((string) $value->Handover_No) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Handover_Date) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) ($value->Status ?? '')) . '</cell>';
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
            abort(403, 'Anda tidak berwenang mengakses pengembalian handover ini.');
        }

        $bastReturnDocs      = $this->bastService->listDocuments($ah->Handover_No, AssetHandoverBastService::DOC_TYPE_RETURN, Auth::user());
        $bastHandoverDocs    = $this->bastService->listDocuments($ah->Handover_No, AssetHandoverBastService::DOC_TYPE_HANDOVER, Auth::user());
        $returnApprovalTrail = !empty($ah->Return_Approval_Request_ID)
            ? app(ApprovalWorkflowService::class)->buildApprovalTrail((int) $ah->Return_Approval_Request_ID)
            : [];

        return view('humanresources.asset-handover.returned.detail', compact(
            'ah',
            'bastReturnDocs',
            'bastHandoverDocs',
            'returnApprovalTrail'
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
        echo '<rows>';
        foreach ($getHandover as $value) {
            echo "<row id='{$value->ID}'>";
                echo '<cell>0</cell>';
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
        }
        echo '</rows>';
    }

    public function storeDetail(Request $request)
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

        $handoverNo = trim((string) ($request->query('Handover_No') ?? ($param['Handover_No'] ?? '')));
        $checkids = explode(',', $param['ids']);
        for ($i = 0; $i < sizeof($checkids); $i++) {
            $action = '';
            $rowId = $checkids[$i];
            $newId = $rowId;
            $mode = $param[$rowId . '_!nativeeditor_status'] ?? '';
            switch ($mode) {
                case 'updated':
                    $action = $this->storeDetailUpdate($rowId, $param, $handoverNo);
                    break;
            }
            echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
        }
        echo '</data>';
    }

    function storeDetailUpdate(string $rowId, array $param, string $handoverNo = '')
    {
        $check = $param[$rowId . '_c0'] ?? 0;
        $retQtyRaw = str_replace(',', '', (string) ($param[$rowId . '_c17'] ?? 0));
        $retQty = is_numeric($retQtyRaw) ? (float) $retQtyRaw : 0.0;
        $retCond = trim((string) ($param[$rowId . '_c18'] ?? 'Baik')) ?: 'Baik';
        $retDate = trim((string) ($param[$rowId . '_c19'] ?? ''));
        $retRemark = trim((string) ($param[$rowId . '_c20'] ?? ''));

        if ($retDate === '') {
            $retDate = date('Y-m-d');
        }

        if ($check == '' || $check == '0') {
            return 'not update';
        }

        $dtl = DB::table('asset_handover_dtl')->where('ID', $rowId)->first();
        if (!$dtl) {
            return 'error';
        }

        if ($handoverNo !== '' && $dtl->Handover_No !== $handoverNo) {
            return 'error';
        }

        $parent = DB::table('asset_handover')->where('Handover_No', $dtl->Handover_No)->first();
        if (!$parent || !$this->bastService->canViewHandover($parent, Auth::user())) {
            return 'error';
        }

        if (!in_array(strtoupper((string) ($parent->Status ?? '')), ['APPROVED', 'UPDATED'], true)) {
            return 'error';
        }

        if (!empty($parent->Return_Approval_Request_ID)) {
            $activeReq = DB::table('approval_request')->where('ID', (int) $parent->Return_Approval_Request_ID)->first();
            if ($activeReq && in_array(strtoupper((string) $activeReq->Status), ['IN_PROGRESS', 'BLOCKED', 'PENDING', 'HELD'], true)) {
                return 'error';
            }
        }

        $dtlStatus = strtoupper((string) ($dtl->Status ?? ''));
        if (!in_array($dtlStatus, ['APPROVED', 'RETURN_PENDING'], true)) {
            return 'error';
        }

        $origQty = (float) ($dtl->Qty ?? 0);
        if ($retQty <= 0 || ($origQty > 0 && $retQty > $origQty)) {
            return 'error';
        }

        $hasAssetNo = Schema::hasColumn('asset_handover_dtl', 'Asset_No') && trim((string) ($dtl->Asset_No ?? '')) !== '';
        if ($hasAssetNo && $origQty == 1.0 && $retQty != 1.0) {
            return 'error';
        }

        DB::table('asset_handover_dtl')->where('ID', $rowId)->update([
            'Status'             => 'RETURN_PENDING',
            'Returned_Qty'       => $retQty,
            'Returned_Condition' => mb_substr($retCond, 0, 100),
            'Returned_Date'      => $retDate,
            'Returned_Remark'    => $retRemark,
        ]);

        return 'update';
    }

    public function submitReturn(Request $request)
    {
        $handoverNo         = trim((string) $request->input('Handover_No', ''));
        $returnLeadNik      = trim((string) $request->input('Return_Lead_NIK', ''));
        $returnLeadName     = trim((string) $request->input('Return_Lead_Name', ''));
        $returnReceiverNik  = trim((string) $request->input('Return_Receiver_NIK', ''));
        $returnReceiverName = trim((string) $request->input('Return_Receiver_Name', ''));
        $note               = trim((string) $request->input('Approval_Note', ''));

        if ($handoverNo === '' || $returnLeadNik === '' || $returnReceiverNik === '') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Handover No, PIC Approval Pengembalian, dan Penerima Pengembalian wajib diisi.',
            ], 422);
        }

        $handover = DB::table('asset_handover')->where('Handover_No', $handoverNo)->first();
        if (!$handover) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data Handover tidak ditemukan.',
            ], 404);
        }

        if (!$this->bastService->canViewHandover($handover, Auth::user())) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak berwenang mengajukan pengembalian untuk transaksi ini.',
            ], 403);
        }

        try {
            $req = DB::transaction(function () use ($handover, $returnLeadNik, $returnLeadName, $returnReceiverNik, $returnReceiverName, $note) {
                return $this->approvalService->submitReturn(
                    (int) $handover->ID,
                    (int) Auth::id(),
                    $returnLeadNik,
                    $returnLeadName,
                    $returnReceiverNik,
                    $returnReceiverName,
                    $note
                );
            });

            return response()->json([
                'status'              => 'success',
                'message'             => "Pengajuan pengembalian aset {$handoverNo} berhasil dikirim ke ApprovalWorkflowService.",
                'approval_request_id' => (int) $req->ID,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function log(Request $request)
    {
        return view('humanresources.asset-handover.returned.log');
    }
}
