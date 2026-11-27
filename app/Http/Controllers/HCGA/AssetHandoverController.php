<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Approval\ApprovalWorkflowService;
use App\Services\Approval\AssetHandoverApprovalService;
use App\Services\HCGA\AssetHandoverBastPdfService;
use App\Services\HCGA\AssetHandoverBastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssetHandoverController extends Controller
{
    /** @var AssetHandoverBastService */
    protected $bastService;

    /** @var AssetHandoverBastPdfService */
    protected $bastPdfService;

    /** @var AssetHandoverApprovalService */
    protected $approvalService;

    public function __construct(
        AssetHandoverBastService $bastService,
        AssetHandoverBastPdfService $bastPdfService,
        AssetHandoverApprovalService $approvalService
    ) {
        $this->bastService = $bastService;
        $this->bastPdfService = $bastPdfService;
        $this->approvalService = $approvalService;
    }

    public function index()
    {
        return view('humanresources.asset-handover.index');
    }

    public function searchEmployee(Request $request)
    {
        $chars = trim((string) $request->input('chars', ''));
        $arr = [];

        if (strlen($chars) >= 2 && Schema::hasTable('employment')) {
            $today = date('Y-m-d');
            $query = DB::table('employment')
                ->where(function ($q) use ($chars) {
                    $q->where('Name', 'LIKE', '%' . $chars . '%')
                      ->orWhere('NIK', 'LIKE', '%' . $chars . '%');
                });

            if (Schema::hasColumn('employment', 'Status')) {
                $query->where(function ($q) {
                    $q->whereNull('Status')->orWhere('Status', '')->orWhere('Status', 'ACTIVE');
                });
            }
            if (Schema::hasColumn('employment', 'End_Date')) {
                $query->where(function ($q) use ($today) {
                    $q->whereNull('End_Date')
                      ->orWhere('End_Date', '')
                      ->orWhere('End_Date', '0000-00-00')
                      ->orWhere('End_Date', '>=', $today);
                });
            }

            $rows = $query->orderBy('Name', 'ASC')->limit(30)->get();
            foreach ($rows as $row) {
                $desc = e((string) $row->Name)
                    . '<br>Department : ' . e((string) ($row->Department ?? '-'))
                    . '<br>NIK : ' . e((string) ($row->NIK ?? '-'))
                    . '<br>Position : ' . e((string) ($row->Position ?? '-'));

                $arr[] = [
                    'id'          => $row->NIK,
                    'data'        => $row->Name,
                    'thumbnail'   => $row->Department ?? '',
                    'description' => $desc,
                    'phone'       => $row->Handphone ?? '',
                ];
            }
        }

        return response()->json($arr);
    }

    public function data(Request $request)
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
            $label = $value->Description;
            if (!empty($value->Asset_No)) {
                $label .= ' [' . $value->Asset_No . ']';
            }
            $productList[$value->Handover_No][] = $label;
        }

        $user = Auth::user();
        $nik = trim((string) ($user->Emp_ID ?? ''));
        $username = trim((string) ($user->username ?? ''));
        $userId = (int) ($user->id ?? 0);

        $getHandover = DB::table('asset_handover')->orderBy('ID', 'DESC');
        if (!$this->bastService->isAdminUser($user)) {
            $candidateReqIds = [];
            if ($userId > 0 && Schema::hasTable('approval_task_candidate')) {
                $candidateReqIds = DB::table('approval_request_task as t')
                    ->join('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
                    ->where('c.User_Id', $userId)
                    ->where('c.Status', 'ACTIVE')
                    ->pluck('t.Request_ID')
                    ->all();
            }

            $getHandover->where(function ($q) use ($nik, $username, $candidateReqIds) {
                if ($nik !== '') {
                    $q->where('User_NIK', $nik)
                      ->orWhere('PIC_NIK', $nik);
                    if (Schema::hasColumn('asset_handover', 'Lead_NIK')) {
                        $q->orWhere('Lead_NIK', $nik);
                    }
                    if (Schema::hasColumn('asset_handover', 'Return_Lead_NIK')) {
                        $q->orWhere('Return_Lead_NIK', $nik)
                          ->orWhere('Return_Receiver_NIK', $nik);
                    }
                }
                if ($username !== '') {
                    $q->orWhere('Created_By', $username);
                }
                if (!empty($candidateReqIds)) {
                    if (Schema::hasColumn('asset_handover', 'Approval_Request_ID')) {
                        $q->orWhereIn('Approval_Request_ID', $candidateReqIds);
                    }
                    if (Schema::hasColumn('asset_handover', 'Return_Approval_Request_ID')) {
                        $q->orWhereIn('Return_Approval_Request_ID', $candidateReqIds);
                    }
                }
            });
        }
        $getHandover = $getHandover->get();

        $handoverNos = $getHandover->pluck('Handover_No')->all();
        $bastCounts = $this->bastService->getCountsByHandoverNos($handoverNos);

        $nomor = 1;
        echo '<rows>';
        foreach ($getHandover as $value) {
            $statusUpper = strtoupper((string) ($value->Status ?? 'CREATED'));
            $bastHStatus = in_array($statusUpper, ['APPROVED', 'RETURNED'], true) ? 'Resmi (PDF)' : 'Draft (PDF)';
            $bastRStatus = $statusUpper === 'RETURNED' ? 'Resmi (PDF)' : 'Draft (PDF)';

            $cntH = $bastCounts[$value->Handover_No]['HANDOVER'] ?? 0;
            $cntR = $bastCounts[$value->Handover_No]['RETURN'] ?? 0;
            $bastHLabel = $cntH > 0 ? "{$bastHStatus} + {$cntH} Lamp." : $bastHStatus;
            $bastRLabel = $cntR > 0 ? "{$bastRStatus} + {$cntR} Lamp." : ($statusUpper === 'RETURNED' ? $bastRStatus : '-');

            $leadDisplay = trim((string) ($value->Lead_Name ?? ''));
            if ($leadDisplay === '' && !empty($value->Lead_NIK)) {
                $leadDisplay = (string) $value->Lead_NIK;
            }

            echo "<row id='{$value->ID}'>";
                echo "<cell>{$nomor}</cell>";
                echo '<cell>' . htmlspecialchars((string) $value->Handover_No) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->Handover_Date) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) ($value->Status ?? 'CREATED')) . '</cell>';
                echo '<cell>' . htmlspecialchars($bastHLabel) . '</cell>';
                echo '<cell>' . htmlspecialchars($bastRLabel) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->User_Name) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $value->User_NIK) . '</cell>';
                echo '<cell>' . htmlspecialchars($leadDisplay ?: '-') . '</cell>';
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

    public function new()
    {
        $user = Auth::user();
        $LSP = trim((string) ($user->Location ?? '')) ?: 'HO';
        $NIK = (string) ($user->Emp_ID ?? '');
        $Saya = (string) ($user->username ?? '');
        $emp = ($NIK !== '' && Schema::hasTable('employment'))
            ? DB::table('employment')->where('NIK', $NIK)->first()
            : null;
        $defaultLeadNik  = $emp ? (string) $emp->NIK : $NIK;
        $defaultLeadName = $emp ? (string) $emp->Name : (string) ($user->Name ?: $Saya);

        $counter = DB::table('asset_handover')->where('Created_Date', 'LIKE', date('Y-m') . '%')->count() + 1;
        $Handover_No = "HA/GA/$LSP/" . date('ymdHis') . '-' . $counter;

        return view('humanresources.asset-handover.create', compact(
            'Handover_No',
            'NIK',
            'Saya',
            'defaultLeadNik',
            'defaultLeadName'
        ));
    }

    public function product()
    {
        return view('humanresources.asset-handover.product');
    }

    public function productData()
    {
        if (stristr($_SERVER['HTTP_ACCEPT'] ?? '', 'application/xhtml+xml')) {
            header('Content-type: application/xhtml+xml');
        } else {
            header('Content-type: text/xml');
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $productsByCode = DB::table('product_code')->get()->keyBy('Product_Code');

        $reservedAssetNos = [];
        if (Schema::hasColumn('asset_handover_dtl', 'Asset_No')) {
            $reservedAssetNos = DB::table('asset_handover_dtl as d')
                ->join('asset_handover as h', 'h.Handover_No', '=', 'd.Handover_No')
                ->whereIn('h.Status', ['CREATED', 'UPDATED', 'WAITING_APPROVAL'])
                ->whereNotNull('d.Asset_No')
                ->where('d.Asset_No', '!=', '')
                ->pluck('d.Asset_No')
                ->all();
        }

        $availableAssetsQuery = DB::table('asset_list')
            ->where(function ($q) {
                $q->whereNull('User_Responsible')
                  ->orWhere('User_Responsible', '')
                  ->orWhere('User_Responsible', 'GA / Pool');
            })
            ->where(function ($q) {
                $q->whereNull('Status')->orWhereNotIn('Status', ['DISPOSED', 'MAINTENANCE']);
            });

        if (!empty($reservedAssetNos)) {
            $availableAssetsQuery->whereNotIn('Asset_No', $reservedAssetNos);
        }

        $availableAssets = $availableAssetsQuery
            ->orderBy('Product_Code', 'ASC')
            ->orderBy('Asset_No', 'ASC')
            ->get();

        $getSupplies = DB::table('supplies')
            ->groupBy('Product_Code')
            ->selectRaw('Product_Code, SUM(Qty) as qty')
            ->havingRaw('SUM(Qty) > 0')
            ->get()
            ->pluck('qty', 'Product_Code');

        $nomor = 1;
        echo '<rows>';

        // 1. Unit aset spesifik dari asset_list (terisi Asset_No & SN)
        foreach ($availableAssets as $asset) {
            $prod = $productsByCode->get($asset->Product_Code);
            $desc = $asset->Product_Description ?: ($prod->Description ?? '');
            $spec = $prod->Specification ?? '';
            $mainCat = $asset->Asset_Category ?: ($prod->Main_Category ?? '');
            $cat = $asset->Asset_Sub_Category ?: ($prod->Category ?? '');
            $supType = $prod->Supplies_Type ?? ($asset->Asset_Type ?: 'ASSET');
            $unit = $asset->Unit_Name ?: ($prod->Unit_Name ?? 'Unit');
            $foto = $prod->Foto ?? '';
            $dim = $prod->Dimension ?? '';
            $price = $asset->Buy_Val ?? ($prod->Price ?? 0);
            $brand = $asset->Product_Brand ?: ($prod->Product_Brand ?? '');

            echo "<row id=\"asset-{$asset->ID}\">";
                echo "<cell>{$nomor}</cell>";
                echo '<cell>' . htmlspecialchars((string) $asset->Product_Code) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $desc) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $spec) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $mainCat) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $cat) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $supType) . '</cell>';
                echo '<cell>1</cell>';
                echo '<cell>' . htmlspecialchars((string) $unit) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $foto) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $dim) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $price) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $brand) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) $asset->Asset_No) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) ($asset->SN ?? '')) . '</cell>';
            echo '</row>';
            $nomor++;
        }

        // 2. Item supplies non-aset dari tabel supplies
        foreach ($productsByCode as $value) {
            $supQty = $getSupplies[$value->Product_Code] ?? 0;
            if ($supQty > 0) {
                echo "<row id=\"sup-{$value->ID}\">";
                    echo "<cell>{$nomor}</cell>";
                    echo '<cell>' . htmlspecialchars((string) $value->Product_Code) . '</cell>';
                    echo '<cell>' . htmlspecialchars((string) $value->Description) . '</cell>';
                    echo '<cell>' . htmlspecialchars((string) $value->Specification) . '</cell>';
                    echo '<cell>' . htmlspecialchars((string) $value->Main_Category) . '</cell>';
                    echo '<cell>' . htmlspecialchars((string) $value->Category) . '</cell>';
                    echo '<cell>' . htmlspecialchars((string) $value->Supplies_Type) . '</cell>';
                    echo '<cell>' . $supQty . '</cell>';
                    echo '<cell>' . htmlspecialchars((string) $value->Unit_Name) . '</cell>';
                    echo '<cell>' . htmlspecialchars((string) $value->Foto) . '</cell>';
                    echo '<cell>' . htmlspecialchars((string) $value->Dimension) . '</cell>';
                    echo '<cell>' . htmlspecialchars((string) $value->Price) . '</cell>';
                    echo '<cell>' . htmlspecialchars((string) $value->Product_Brand) . '</cell>';
                    echo '<cell></cell>';
                    echo '<cell></cell>';
                echo '</row>';
                $nomor++;
            }
        }
        echo '</rows>';
    }

    public function store(Request $request)
    {
        $handoverNo = trim((string) $request->input('Handover_No', ''));
        $leadNik    = trim((string) $request->input('Lead_NIK', '')) ?: trim((string) (Auth::user()->Emp_ID ?? ''));
        $leadName   = trim((string) $request->input('Lead_Name', ''));
        $picNik     = trim((string) $request->input('PIC_NIK', ''));
        $picName    = trim((string) $request->input('PIC_Name', ''));
        $picDept    = trim((string) $request->input('PIC_Department', ''));
        $picPhone   = trim((string) $request->input('PIC_Phone', ''));
        $remark     = trim((string) $request->input('Remark', ''));

        if ($handoverNo === '' || $leadNik === '' || $picNik === '' || $picDept === '' || strlen($remark) < 5) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Handover No, Lead Pemberi, Penerima PIC, Department, dan Remark (min. 5 karakter) wajib diisi.',
            ], 422);
        }

        try {
            $approvalReq = DB::transaction(function () use ($request, $handoverNo, $leadNik, $leadName, $picNik, $picName, $picDept, $picPhone, $remark) {
                $alreadyExists = DB::table('asset_handover')
                    ->where('Handover_No', $handoverNo)
                    ->lockForUpdate()
                    ->exists();
                if ($alreadyExists) {
                    throw new \RuntimeException("Nomor Handover {$handoverNo} sudah terdaftar.");
                }

                $payload = [
                    'Handover_No'    => $handoverNo,
                    'Handover_Date'  => $request->input('Handover_Date') ?: date('Y-m-d'),
                    'User_NIK'       => trim((string) $request->input('User_NIK', Auth::user()->Emp_ID)),
                    'User_Name'      => trim((string) $request->input('User_Name', Auth::user()->username)),
                    'PIC_NIK'        => $picNik,
                    'PIC_Name'       => $picName,
                    'PIC_Department' => $picDept,
                    'PIC_Phone'      => $picPhone,
                    'Reference'      => (string) ($request->input('Reference') ?? ''),
                    'Remark'         => $remark,
                    'Status'         => 'CREATED',
                    'Created_By'     => Auth::user()->username,
                    'Created_Date'   => now(),
                ];

                if (Schema::hasColumn('asset_handover', 'Lead_NIK')) {
                    $payload['Lead_NIK']  = $leadNik;
                    $payload['Lead_Name'] = $leadName;
                }

                $handoverId = DB::table('asset_handover')->insertGetId($payload);

                return $this->approvalService->submitHandover($handoverId, (int) Auth::id());
            });

            return response()->json([
                'status'              => 'success',
                'message'             => "Berhasil menyimpan dan mengirim pengajuan approval ({$handoverNo})",
                'approval_request_id' => (int) $approvalReq->ID,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
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
        if ($handoverNo === '') {
            echo '</data>';
            return;
        }

        $existingHeader = DB::table('asset_handover')->where('Handover_No', $handoverNo)->first();
        if ($existingHeader) {
            if (!$this->canEditHandover($existingHeader, Auth::user())) {
                echo '</data>';
                return;
            }
            $hdrStatus = strtoupper((string) ($existingHeader->Status ?? ''));
            if (in_array($hdrStatus, ['APPROVED', 'RETURNED'], true)) {
                echo '</data>';
                return;
            }
            if ($hdrStatus === 'WAITING_APPROVAL' && $this->hasHumanApprovalStarted((int) ($existingHeader->Approval_Request_ID ?? 0))) {
                echo '</data>';
                return;
            }
        }

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
                case 'inserted':
                    $action = $this->storeDetailInsert($rowId, $param, $handoverNo);
                    break;
                case 'deleted':
                    $action = $this->storeDetailDelete($rowId, $handoverNo);
                    break;
            }
            echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
        }
        echo '</data>';
    }

    function storeDetailInsert(string $rowId, array $param, string $handoverNo = '')
    {
        $handoverNo = $handoverNo !== '' ? $handoverNo : trim((string) ($_GET['Handover_No'] ?? ($param['Handover_No'] ?? '')));
        $a = $param[$rowId . '_c1'] ?? '';
        $b = $param[$rowId . '_c2'] ?? '';
        $c = $param[$rowId . '_c3'] ?? '';
        $d = $this->parseNumeric($param[$rowId . '_c4'] ?? 0);
        $e = $param[$rowId . '_c5'] ?? '';
        $f = $param[$rowId . '_c6'] ?? '';
        $g = $param[$rowId . '_c7'] ?? '';
        $h = $param[$rowId . '_c8'] ?? '';
        $ix = $param[$rowId . '_c9'] ?? '';
        $j = $param[$rowId . '_c10'] ?? '';
        $k = $param[$rowId . '_c11'] ?? '';
        $l = $param[$rowId . '_c12'] ?? '';
        $m = $this->parseNumeric($param[$rowId . '_c13'] ?? 0);
        $n = $this->parseNumeric($param[$rowId . '_c14'] ?? 0);
        $o = $param[$rowId . '_c15'] ?? null;
        $assetNo = trim((string) ($param[$rowId . '_c17'] ?? ''));

        if ($o == '') {
            $o = null;
        }

        if ($handoverNo === '' || $d <= 0) {
            return 'error';
        }

        if ($assetNo !== '' && !$this->validateAssetForHandoverDetail($assetNo, $d, $handoverNo, null)) {
            return 'error';
        }

        $LSP = Auth::user()->Location;

        $insert = [
            'Handover_No'       => $handoverNo,
            'Purchase_Category' => $l,
            'Purchase_Type'     => $a,
            'Category'          => $k,
            'Product_Code'      => $b,
            'Description'       => $c,
            'Specification'     => $g,
            'Product_Type'      => $f,
            'Product_Brand'     => $e,
            'Condition'         => $h,
            'Qty'               => $d,
            'Default_Price'     => $m,
            'Unit'              => $ix,
            'Remark'            => $j,
            'Location'          => $LSP,
            'Received_Date'     => $o,
            'Prices'            => $n > 0 ? $n : ($m * $d),
            'Status'            => 'WAITING_APPROVAL',
        ];
        if (Schema::hasColumn('asset_handover_dtl', 'Asset_No')) {
            $insert['Asset_No'] = $assetNo !== '' ? $assetNo : null;
        }

        DB::table('asset_handover_dtl')->insert($insert);

        return 'insert';
    }

    function storeDetailUpdate(string $rowId, array $param, string $handoverNo = '')
    {
        $dtl = DB::table('asset_handover_dtl')->where('ID', $rowId)->first();
        if (!$dtl || ($handoverNo !== '' && $dtl->Handover_No !== $handoverNo)) {
            return 'error';
        }
        if (in_array(strtoupper((string) ($dtl->Status ?? '')), ['APPROVED', 'RETURN_PENDING', 'RETURNED'], true)) {
            return 'error';
        }

        $a = $param[$rowId . '_c1'] ?? '';
        $b = $param[$rowId . '_c2'] ?? '';
        $c = $param[$rowId . '_c3'] ?? '';
        $d = $this->parseNumeric($param[$rowId . '_c4'] ?? 0);
        $e = $param[$rowId . '_c5'] ?? '';
        $f = $param[$rowId . '_c6'] ?? '';
        $g = $param[$rowId . '_c7'] ?? '';
        $h = $param[$rowId . '_c8'] ?? '';
        $ix = $param[$rowId . '_c9'] ?? '';
        $j = $param[$rowId . '_c10'] ?? '';
        $k = $param[$rowId . '_c11'] ?? '';
        $l = $param[$rowId . '_c12'] ?? '';
        $m = $this->parseNumeric($param[$rowId . '_c13'] ?? 0);
        $n = $this->parseNumeric($param[$rowId . '_c14'] ?? 0);
        $o = $param[$rowId . '_c15'] ?? null;
        $assetNo = trim((string) ($param[$rowId . '_c17'] ?? ''));

        if ($o == '') {
            $o = null;
        }

        if ($d <= 0) {
            return 'error';
        }

        if ($assetNo !== '' && !$this->validateAssetForHandoverDetail($assetNo, $d, $dtl->Handover_No, (int) $dtl->ID)) {
            return 'error';
        }

        $LSP = Auth::user()->Location;

        $update = [
            'Purchase_Category' => $l,
            'Purchase_Type'     => $a,
            'Category'          => $k,
            'Product_Code'      => $b,
            'Description'       => $c,
            'Specification'     => $g,
            'Product_Type'      => $f,
            'Product_Brand'     => $e,
            'Condition'         => $h,
            'Qty'               => $d,
            'Default_Price'     => $m,
            'Unit'              => $ix,
            'Remark'            => $j,
            'Location'          => $LSP,
            'Received_Date'     => $o,
            'Prices'            => $n > 0 ? $n : ($m * $d),
        ];
        if (Schema::hasColumn('asset_handover_dtl', 'Asset_No')) {
            $update['Asset_No'] = $assetNo !== '' ? $assetNo : null;
        }

        DB::table('asset_handover_dtl')->where('ID', $rowId)->update($update);

        return 'update';
    }

    function storeDetailDelete(string $rowId, string $handoverNo = '')
    {
        $dtl = DB::table('asset_handover_dtl')->where('ID', $rowId)->first();
        if (!$dtl || ($handoverNo !== '' && $dtl->Handover_No !== $handoverNo)) {
            return 'error';
        }

        if (in_array(strtoupper((string) ($dtl->Status ?? '')), ['APPROVED', 'RETURN_PENDING', 'RETURNED'], true)) {
            return 'error';
        }

        DB::table('asset_handover_dtl')->where('ID', $rowId)->delete();

        return 'delete';
    }

    protected function parseNumeric($val): float
    {
        $clean = str_replace(',', '', trim((string) $val));
        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    protected function validateAssetForHandoverDetail(string $assetNo, float $qty, string $handoverNo, ?int $excludeDtlId): bool
    {
        if ($qty != 1.0) {
            return false;
        }

        $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
        if (!$asset) {
            return false;
        }

        $assetStatus = strtoupper((string) ($asset->Status ?? ''));
        if (in_array($assetStatus, ['DISPOSED', 'MAINTENANCE'], true)) {
            return false;
        }

        $holder = trim((string) ($asset->User_Responsible ?? ''));
        if ($holder !== '' && strcasecmp($holder, 'GA / Pool') !== 0) {
            return false;
        }

        if (!Schema::hasColumn('asset_handover_dtl', 'Asset_No')) {
            return true;
        }

        $dupInSame = DB::table('asset_handover_dtl')
            ->where('Handover_No', $handoverNo)
            ->where('Asset_No', $assetNo)
            ->when($excludeDtlId !== null, function ($q) use ($excludeDtlId) {
                $q->where('ID', '!=', $excludeDtlId);
            })
            ->exists();
        if ($dupInSame) {
            return false;
        }

        $inOtherActive = DB::table('asset_handover_dtl as d')
            ->join('asset_handover as h', 'h.Handover_No', '=', 'd.Handover_No')
            ->where('d.Handover_No', '!=', $handoverNo)
            ->where('d.Asset_No', $assetNo)
            ->whereIn('h.Status', ['CREATED', 'UPDATED', 'WAITING_APPROVAL'])
            ->exists();

        return !$inOtherActive;
    }

    protected function canEditHandover(object $handover, $user): bool
    {
        if (!$user) {
            return false;
        }
        if ($this->bastService->isAdminUser($user)) {
            return true;
        }

        $empId = trim((string) ($user->Emp_ID ?? ''));
        $username = trim((string) ($user->username ?? ''));
        if ($username !== '' && strcasecmp(trim((string) ($handover->Created_By ?? '')), $username) === 0) {
            return true;
        }
        if ($empId !== '') {
            $allowedNiks = array_filter([
                trim((string) ($handover->User_NIK ?? '')),
                trim((string) ($handover->Lead_NIK ?? '')),
            ]);
            if (in_array($empId, $allowedNiks, true)) {
                return true;
            }
        }

        return false;
    }

    protected function hasHumanApprovalStarted(int $approvalRequestId): bool
    {
        if ($approvalRequestId <= 0 || !Schema::hasTable('approval_request_task')) {
            return false;
        }

        return DB::table('approval_request_task')
            ->where('Request_ID', $approvalRequestId)
            ->where('Status', 'APPROVED')
            ->exists();
    }

    public function detail(Request $request)
    {
        $ah = DB::table('asset_handover')->where('Handover_No', $request->Handover_No)->first();
        if (!$ah) {
            abort(404, 'Data Handover tidak ditemukan.');
        }

        $user = Auth::user();
        if (!$this->bastService->canViewHandover($ah, $user)) {
            abort(403, 'Anda tidak berwenang mengakses detail handover ini.');
        }
        $bastHandoverDocs = $this->bastService->listDocuments($ah->Handover_No, AssetHandoverBastService::DOC_TYPE_HANDOVER, $user);
        $bastReturnDocs   = $this->bastService->listDocuments($ah->Handover_No, AssetHandoverBastService::DOC_TYPE_RETURN, $user);
        $approvalTrail    = !empty($ah->Approval_Request_ID)
            ? app(ApprovalWorkflowService::class)->buildApprovalTrail((int) $ah->Approval_Request_ID)
            : [];
        $returnApprovalTrail = !empty($ah->Return_Approval_Request_ID)
            ? app(ApprovalWorkflowService::class)->buildApprovalTrail((int) $ah->Return_Approval_Request_ID)
            : [];

        return view('humanresources.asset-handover.detail', compact(
            'ah',
            'bastHandoverDocs',
            'bastReturnDocs',
            'approvalTrail',
            'returnApprovalTrail'
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
                echo '<cell>' . htmlspecialchars((string) $value->Status) . '</cell>';
                echo '<cell>' . htmlspecialchars((string) ($value->Asset_No ?? '')) . '</cell>';
            echo '</row>';

            $nomor++;
        }
        echo '</rows>';
    }

    public function update(Request $request)
    {
        $id = (int) $request->input('ID', 0);
        $existing = DB::table('asset_handover')->where('ID', $id)->first();
        if (!$existing) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data Handover tidak ditemukan.',
            ], 404);
        }

        if (!$this->canEditHandover($existing, Auth::user())) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak berwenang mengubah transaksi handover ini.',
            ], 403);
        }

        $statusUpper = strtoupper((string) ($existing->Status ?? ''));
        if (in_array($statusUpper, ['APPROVED', 'RETURNED'], true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Handover yang sudah disetujui/dikembalikan tidak dapat diubah.',
            ], 422);
        }

        $newPicNik  = trim((string) $request->input('PIC_NIK', $existing->PIC_NIK));
        $newLeadNik = Schema::hasColumn('asset_handover', 'Lead_NIK')
            ? trim((string) $request->input('Lead_NIK', $existing->Lead_NIK ?? ''))
            : '';

        if ($newPicNik === '' || trim((string) $request->input('PIC_Department', $existing->PIC_Department)) === '' || strlen(trim((string) $request->input('Remark', $existing->Remark))) < 5) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Penerima PIC, Department, dan Remark (min. 5 karakter) wajib diisi.',
            ], 422);
        }

        $partiesChanged = strcasecmp($newPicNik, trim((string) ($existing->PIC_NIK ?? ''))) !== 0
            || ($newLeadNik !== '' && strcasecmp($newLeadNik, trim((string) ($existing->Lead_NIK ?? ''))) !== 0);

        if ($statusUpper === 'WAITING_APPROVAL' && $partiesChanged && $this->hasHumanApprovalStarted((int) ($existing->Approval_Request_ID ?? 0))) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tahap approval sudah berjalan dan disetujui sebagian; Lead Pemberi atau Penerima PIC tidak dapat diubah.',
            ], 422);
        }

        $updateData = [
            'PIC_NIK'        => $newPicNik,
            'PIC_Name'       => trim((string) $request->input('PIC_Name', $existing->PIC_Name)),
            'PIC_Department' => trim((string) $request->input('PIC_Department', $existing->PIC_Department)),
            'PIC_Phone'      => trim((string) $request->input('PIC_Phone', $existing->PIC_Phone)),
            'Reference'      => (string) ($request->input('Reference') ?? ''),
            'Remark'         => trim((string) $request->input('Remark', $existing->Remark)),
            'Updated_By'     => Auth::user()->username,
            'Updated_Date'   => now(),
        ];

        if (Schema::hasColumn('asset_handover', 'Lead_NIK')) {
            $updateData['Lead_NIK']  = $newLeadNik !== '' ? $newLeadNik : trim((string) ($existing->Lead_NIK ?? ''));
            $updateData['Lead_Name'] = trim((string) $request->input('Lead_Name', $existing->Lead_Name ?? ''));
        }

        try {
            $resubmitted = DB::transaction(function () use ($id, $existing, $statusUpper, $partiesChanged, $updateData) {
                DB::table('asset_handover')->where('ID', $id)->update($updateData);

                $needsSubmit = in_array($statusUpper, ['CREATED', 'UPDATED', 'REJECTED', 'CANCELLED'], true)
                    || empty($existing->Approval_Request_ID);

                if (!$needsSubmit && $statusUpper === 'WAITING_APPROVAL' && $partiesChanged && !empty($existing->Approval_Request_ID)) {
                    $activeReq = DB::table('approval_request')->where('ID', (int) $existing->Approval_Request_ID)->first();
                    if ($activeReq && !in_array(strtoupper((string) $activeReq->Status), ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'], true)) {
                        app(ApprovalWorkflowService::class)->cancel(
                            (int) $existing->Approval_Request_ID,
                            (int) Auth::id(),
                            'Diperbarui sebelum approval selesai'
                        );
                    }
                    $needsSubmit = true;
                }

                if ($needsSubmit) {
                    $this->approvalService->submitHandover($id, (int) Auth::id());
                    return true;
                }

                return false;
            });

            return response()->json([
                'status'  => 'success',
                'message' => $resubmitted
                    ? 'Berhasil memperbarui dan mengirim ulang pengajuan approval handover'
                    : 'Berhasil update data handover',
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
        $ID = (string) ($request->input('Handover_No') ?? $request->input('ID') ?? '');
        if ($ID !== '') {
            $ah = DB::table('asset_handover')->where('Handover_No', $ID)->first();
            if ($ah && !$this->bastService->canViewHandover($ah, Auth::user())) {
                abort(403, 'Anda tidak berwenang melihat log handover ini.');
            }
        }
        $logs = app(\App\Services\HCGA\AssetLegacyLogService::class)->getHandoverLogs($ID);

        return view('humanresources.asset-handover.log', compact('ID', 'logs'));
    }

    public function bastPrint(Request $request)
    {
        $handoverNo = trim((string) $request->input('Handover_No', ''));
        $docType    = $this->bastPdfService->normalizeDocType($request->input('doc_type', AssetHandoverBastPdfService::DOC_TYPE_HANDOVER));

        if ($handoverNo === '') {
            abort(422, 'Handover_No wajib diisi.');
        }

        $ah = DB::table('asset_handover')->where('Handover_No', $handoverNo)->first();
        if (!$ah) {
            abort(404, 'Data Handover tidak ditemukan.');
        }

        if (!$this->bastService->canViewHandover($ah, Auth::user())) {
            abort(403, 'Anda tidak berwenang mencetak BAST transaksi ini.');
        }

        $pdf = $this->bastPdfService->renderPdf($handoverNo, $docType);

        return response($pdf['binary'], 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pdf['filename'] . '"',
        ]);
    }

    public function bastVerifyPublic(Request $request)
    {
        $handoverNo = trim((string) $request->input('Handover_No', ''));
        $docType    = $this->bastPdfService->normalizeDocType($request->input('doc_type', AssetHandoverBastPdfService::DOC_TYPE_HANDOVER));
        $sig        = (string) $request->input('sig', '');

        $isValidSignature = $this->bastPdfService->verifySignature($handoverNo, $docType, $sig);
        $data = $isValidSignature
            ? $this->bastPdfService->buildDocumentData($handoverNo, $docType)
            : [
                'handover'      => null,
                'docType'       => $docType,
                'bastNo'        => $this->bastPdfService->buildBastNumber($handoverNo, $docType),
                'items'         => collect(),
                'approvalTrail' => [],
                'isOfficial'    => false,
            ];

        $data['isValidSignature'] = $isValidSignature;

        return view('humanresources.asset-handover.pdf.bast-verify', $data);
    }

    public function bastList(Request $request)
    {
        $handoverNo = trim((string) $request->input('Handover_No', ''));
        $docType    = $request->input('doc_type');

        if ($handoverNo === '') {
            return response()->json(['status' => 'error', 'message' => 'Handover_No wajib diisi.'], 422);
        }

        $handover = DB::table('asset_handover')->where('Handover_No', $handoverNo)->first();
        if (!$handover || !$this->bastService->canViewHandover($handover, Auth::user())) {
            return response()->json(['status' => 'error', 'message' => 'Tidak memiliki akses ke data handover ini.'], 403);
        }

        $docs = $this->bastService->listDocuments($handoverNo, $docType, Auth::user());

        return response()->json([
            'status' => 'success',
            'data'   => $docs,
        ]);
    }

    public function bastUpload(Request $request)
    {
        $handoverNo = trim((string) $request->input('Handover_No', ''));
        $docType    = trim((string) $request->input('doc_type', AssetHandoverBastService::DOC_TYPE_HANDOVER));
        $notes      = $request->input('notes');

        if ($handoverNo === '') {
            return response()->json(['status' => 'error', 'message' => 'Handover_No tidak valid.'], 422);
        }

        $files = $request->file('files');
        if (empty($files)) {
            $single = $request->file('file');
            $files = $single ? [$single] : [];
        }
        if (!is_array($files)) {
            $files = [$files];
        }
        if (empty($files)) {
            return response()->json(['status' => 'error', 'message' => 'Pilih minimal satu berkas lampiran untuk diunggah.'], 422);
        }

        try {
            $result = $this->bastService->uploadDocuments($handoverNo, $docType, $files, $notes, Auth::user());
            $uploadedCount = count($result['uploaded']);
            $msg = "{$uploadedCount} lampiran BAST berhasil diunggah.";
            if (!empty($result['rejected'])) {
                $msg .= ' Ditolak: ' . implode('; ', $result['rejected']);
            }

            return response()->json([
                'status'   => $uploadedCount > 0 ? 'success' : 'error',
                'message'  => $msg,
                'uploaded' => $result['uploaded'],
                'rejected' => $result['rejected'],
                'docs'     => $this->bastService->listDocuments($handoverNo, $docType, Auth::user()),
            ], $uploadedCount > 0 ? 200 : 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function bastPreview(int $id)
    {
        return $this->bastService->streamDocument($id, Auth::user(), false);
    }

    public function bastDownload(int $id)
    {
        return $this->bastService->streamDocument($id, Auth::user(), true);
    }

    public function bastDelete(Request $request)
    {
        $docId = (int) $request->input('id', 0);
        if ($docId <= 0) {
            return response()->json(['status' => 'error', 'message' => 'ID dokumen tidak valid.'], 422);
        }

        try {
            $this->bastService->deleteDocument($docId, Auth::user());

            return response()->json([
                'status'  => 'success',
                'message' => 'Lampiran BAST berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            $code = strpos($e->getMessage(), 'terkunci') !== false ? 403 : 422;

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $code);
        }
    }

    public function bastModal(Request $request)
    {
        $handoverNo = trim((string) $request->input('Handover_No', ''));
        $docType    = strtoupper(trim((string) $request->input('doc_type', AssetHandoverBastService::DOC_TYPE_HANDOVER)));
        if (!in_array($docType, [AssetHandoverBastService::DOC_TYPE_HANDOVER, AssetHandoverBastService::DOC_TYPE_RETURN], true)) {
            $docType = AssetHandoverBastService::DOC_TYPE_HANDOVER;
        }

        $ah = DB::table('asset_handover')->where('Handover_No', $handoverNo)->first();
        if (!$ah) {
            abort(404, 'Data Handover tidak ditemukan.');
        }

        if (!$this->bastService->canViewHandover($ah, Auth::user())) {
            abort(403, 'Anda tidak berwenang melihat dokumen BAST transaksi ini.');
        }

        $bastDocs = $this->bastService->listDocuments($ah->Handover_No, $docType, Auth::user());
        $panelId = 'bastModal_' . preg_replace('/[^A-Za-z0-9]/', '', $handoverNo) . '_' . $docType;

        return view('humanresources.asset-handover.partials.bast-modal', compact('ah', 'docType', 'bastDocs', 'panelId'));
    }
}
