<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Services\Approval\ProcurementApprovalService;
use App\Services\Approval\ApprovalWorkflowService;
use App\Services\Approval\ApprovalAuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcurementApprovalController extends Controller
{
    protected $procurementApprovalService;
    protected $workflowService;
    protected $authorizationService;

    public function __construct(
        ProcurementApprovalService $procurementApprovalService,
        ApprovalWorkflowService $workflowService,
        ApprovalAuthorizationService $authorizationService
    ) {
        $this->procurementApprovalService = $procurementApprovalService;
        $this->workflowService = $workflowService;
        $this->authorizationService = $authorizationService;
    }
    public function index()
    {
        return view('humanresources.procurement.approval');
    }

    public function data()
    {
        $user = Auth::user();
        $userId = (int) $user->id;
        $LSP = $user->Location;

        echo "<rows>";

        // 1. Ambil PR yang terhubung ke Approval Workflow Policy Engine
        $isWildcard = $this->authorizationService->isWildcardAdmin($user);

        if ($isWildcard) {
            $pendingTasks = DB::table('approval_request_task as art')
                ->join('approval_request as ar', 'art.Request_ID', '=', 'ar.ID')
                ->where('ar.Request_Type', 'PROCUREMENT')
                ->whereIn('art.Status', ['PENDING', 'CLAIMED'])
                ->select('ar.Map_To as PR_ID', 'art.ID as Task_ID', 'art.Stage_Order')
                ->get();
        } else {
            $pendingTasks = DB::table('approval_request_task as art')
                ->join('approval_request as ar', 'art.Request_ID', '=', 'ar.ID')
                ->join('approval_task_candidate as atc', 'art.ID', '=', 'atc.Task_ID')
                ->where('ar.Request_Type', 'PROCUREMENT')
                ->whereIn('art.Status', ['PENDING', 'CLAIMED'])
                ->where('atc.User_ID', $userId)
                ->select('ar.Map_To as PR_ID', 'art.ID as Task_ID', 'art.Stage_Order')
                ->get();
        }

        $prIdsFromPolicy = $pendingTasks->pluck('PR_ID')->filter()->all();

        // 2. Query data PR (dukung PR baru via Approval Policy + PR legacy yang masih in progress)
        $kueri = DB::table('purchase_request')->where("Location", $LSP)->orderBy('ID', "DESC");

        if ($isWildcard) {
            $kueri->where(function ($q) use ($prIdsFromPolicy) {
                if (!empty($prIdsFromPolicy)) {
                    $q->whereIn('ID', $prIdsFromPolicy);
                }
                $q->orWhere(function ($sub) {
                    $sub->whereNull('Approval_Request_ID')
                        ->whereIn('Status', ['APPROVAL_UPPER', 'APPROVAL_DEPT', 'APPROVAL_GA']);
                });
            });
        } elseif (!empty($prIdsFromPolicy)) {
            $kueri->where(function ($q) use ($prIdsFromPolicy, $user) {
                $q->whereIn('ID', $prIdsFromPolicy)
                  ->orWhere(function ($sub) use ($user) {
                      // Fallback support untuk PR legacy yang belum punya Approval_Request_ID
                      $sub->whereNull('Approval_Request_ID')
                          ->whereIn('Status', ['APPROVAL_UPPER', 'APPROVAL_DEPT', 'APPROVAL_GA']);
                  });
            });
        } else {
            // Jika belum ada task policy, fallback ke legacy status
            $dept = $user->Department;
            if ($dept != 'HCGA') {
                $kueri->where("Approval_Department", $dept)
                      ->whereIn("Status", ["APPROVAL_UPPER", "APPROVAL_DEPT", "REJECTED_GA", "REJECTED_DEPT"]);
            } else {
                $kueri->where("Status", "APPROVAL_GA");
            }
        }

        $kueri = $kueri->get();

        $nomor = 1;
        foreach ($kueri as $Fetch) {
            echo "<row id=\"$Fetch->ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->PR_No)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->PR_Date)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->Category)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->Sub_Category)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->Supplies_Type)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->Status)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->Note)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->Created_Date)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->Created_By)) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function detail(Request $request)
    {
        $dpt = Auth::user()->Department;
        $pr = DB::table('purchase_request')->where("PR_No", $request->PR_No)->first();
        if (!$pr) {
            abort(404, "PR not found");
        }

        $Query = DB::table("master_department");
        if ($dpt != 'ICT' && $dpt != 'BOD' && $dpt != 'HCGA') {
            $Query->where("Initial", $dpt);
        }

        $Department = $Query->get();
        $company = DB::table('supplier')->where("Status", 'NORMAL')->get();

        $pr->details = DB::table('purchase_request_dtl')->where("PR_No", $request->PR_No)->get();
        return view('humanresources.procurement.approval-detail', compact('pr', 'Department', 'company'));
    }

    /**
     * Normalisasi nilai angka untuk komparasi (menghilangkan format koma/spasi)
     */
    private function normalizeNum($val)
    {
        if (is_null($val) || $val === '') return 0.0;
        $clean = str_replace([' ', ','], ['', ''], (string)$val);
        return (float)$clean;
    }

    /**
     * Normalisasi string untuk komparasi
     */
    private function normalizeStr($val)
    {
        return trim((string)($val ?? ''));
    }

    public function approval(Request $request)
    {
        // Ambil data PR yang sedang di-approve
        $cek = DB::table("purchase_request")->where("ID", $request->ID)->first();
        if (!$cek && $request->PR_No) {
            $cek = DB::table("purchase_request")->where("PR_No", $request->PR_No)->first();
        }

        if (!$cek) {
            echo "error: PR not found";
            return;
        }

        $Saya = Auth::User()->username;
        $LSP = Auth::user()->Location;
        $dept = Auth::user()->Department;
        $Name = Auth::user()->Name;

        // ============================================================
        // DETECT PERUBAHAN DATA PRODUCT OLEH APPROVER
        // ============================================================
        $changesDetected = false;
        $changedFields = [];

        // stat: 1 = Approve, 2 = Reject, 3 = Approve Changes (creator)
        $statReq = (int)($request->stat ?? 1);

        // Nilai input dari form
        $newReference = $this->normalizeStr($request->Reference);
        $newCategory = $this->normalizeStr($request->Category);
        $newSubCategory = $this->normalizeStr($request->Sub_Category);
        $newSuppliesType = $this->normalizeStr($request->Supplies_Type ?: $request->Purchase_Type);
        $newPIC = $this->normalizeStr($request->PIC);
        $newPICPhone = $this->normalizeStr($request->PIC_Phone);
        $newPICAddress = $this->normalizeStr($request->PIC_Address);
        $newEstimatedBudget = $this->normalizeNum($request->Estimated_Budget);
        $newDepartment = $this->normalizeStr($request->Department);
        $newSupplierID = $this->normalizeStr($request->Supplier_ID);
        $newPriorityStatus = $this->normalizeStr($request->Priority_Status);

        // Nilai acuan dari database
        $oldReference = $this->normalizeStr($cek->Reference);
        $oldCategory = $this->normalizeStr($cek->Category);
        $oldSubCategory = $this->normalizeStr($cek->Sub_Category);
        $oldSuppliesType = $this->normalizeStr($cek->Supplies_Type);
        $oldPIC = $this->normalizeStr($cek->PIC);
        $oldPICPhone = $this->normalizeStr($cek->PIC_Phone);
        $oldPICAddress = $this->normalizeStr($cek->PIC_Address);
        $oldEstimatedBudget = $this->normalizeNum($cek->Estimated_Budget);
        $oldDepartment = $this->normalizeStr($cek->Department);
        $oldSupplierID = $this->normalizeStr($cek->Supplier_ID);
        $oldPriorityStatus = $this->normalizeStr($cek->Priority_Status);

        // Hanya deteksi perubahan header jika bukan aksi APPROVE_CHANGES
        if ($statReq == 1) {
            if ($request->has('Reference') && $newReference !== $oldReference) {
                $changesDetected = true;
                $changedFields[] = "Reference: '{$oldReference}' -> '{$newReference}'";
            }
            if ($request->has('Category') && $newCategory !== $oldCategory) {
                $changesDetected = true;
                $changedFields[] = "Category: '{$oldCategory}' -> '{$newCategory}'";
            }
            if ($request->has('Sub_Category') && $newSubCategory !== $oldSubCategory) {
                $changesDetected = true;
                $changedFields[] = "Sub_Category: '{$oldSubCategory}' -> '{$newSubCategory}'";
            }
            if (($request->has('Supplies_Type') || $request->has('Purchase_Type')) && $newSuppliesType !== '' && $newSuppliesType !== $oldSuppliesType) {
                $changesDetected = true;
                $changedFields[] = "Supplies_Type: '{$oldSuppliesType}' -> '{$newSuppliesType}'";
            }
            if ($request->has('PIC') && $newPIC !== $oldPIC) {
                $changesDetected = true;
                $changedFields[] = "PIC: '{$oldPIC}' -> '{$newPIC}'";
            }
            if ($request->has('PIC_Phone') && $newPICPhone !== $oldPICPhone) {
                $changesDetected = true;
                $changedFields[] = "PIC_Phone: '{$oldPICPhone}' -> '{$newPICPhone}'";
            }
            if ($request->has('PIC_Address') && $newPICAddress !== $oldPICAddress) {
                $changesDetected = true;
                $changedFields[] = "PIC_Address: '{$oldPICAddress}' -> '{$newPICAddress}'";
            }
            if ($request->has('Estimated_Budget') && abs($newEstimatedBudget - $oldEstimatedBudget) > 0.01) {
                $changesDetected = true;
                $changedFields[] = "Estimated_Budget: {$oldEstimatedBudget} -> {$newEstimatedBudget}";
            }
            if ($request->has('Department') && $newDepartment !== '' && $newDepartment !== $oldDepartment) {
                $changesDetected = true;
                $changedFields[] = "Department: '{$oldDepartment}' -> '{$newDepartment}'";
            }
            if ($request->has('Supplier_ID') && $newSupplierID !== '' && $newSupplierID !== $oldSupplierID) {
                $changesDetected = true;
                $changedFields[] = "Supplier_ID: '{$oldSupplierID}' -> '{$newSupplierID}'";
            }
            if ($request->has('Priority_Status') && $newPriorityStatus !== $oldPriorityStatus) {
                $changesDetected = true;
                $changedFields[] = "Priority_Status: '{$oldPriorityStatus}' -> '{$newPriorityStatus}'";
            }

            // Cek detail produk jika dikirim via GridData JSON
            $gridDataJson = $request->input('GridData');
            if (!empty($gridDataJson)) {
                $gridData = json_decode($gridDataJson, true);
                if (is_array($gridData)) {
                    $dtlOld = DB::table('purchase_request_dtl')->where("PR_No", $cek->PR_No)->orderBy('ID')->get();
                    if (count($gridData) != count($dtlOld)) {
                        $changesDetected = true;
                        $changedFields[] = "Jumlah item produk berubah: " . count($dtlOld) . " item -> " . count($gridData) . " item";
                    } else {
                        foreach ($gridData as $idx => $row) {
                            $oldRow = $dtlOld[$idx] ?? null;
                            if (!$oldRow) {
                                $changesDetected = true;
                                $changedFields[] = "Item produk #" . ($idx + 1) . " baru ditambahkan";
                                break;
                            }
                            if ($this->normalizeStr($row['Product_Desc'] ?? '') !== $this->normalizeStr($oldRow->Product_Desc)) {
                                $changesDetected = true;
                                $changedFields[] = "Item #" . ($idx + 1) . " Deskripsi: '{$oldRow->Product_Desc}' -> '" . ($row['Product_Desc'] ?? '') . "'";
                            }
                            if ($this->normalizeNum($row['Qty'] ?? 0) != $this->normalizeNum($oldRow->Qty)) {
                                $changesDetected = true;
                                $changedFields[] = "Item #" . ($idx + 1) . " Qty: {$oldRow->Qty} -> " . ($row['Qty'] ?? 0);
                            }
                            if ($this->normalizeStr($row['Specification'] ?? '') !== $this->normalizeStr($oldRow->Specification)) {
                                $changesDetected = true;
                                $changedFields[] = "Item #" . ($idx + 1) . " Spesifikasi diubah";
                            }
                            if ($this->normalizeStr($row['Unit'] ?? '') !== $this->normalizeStr($oldRow->Unit)) {
                                $changesDetected = true;
                                $changedFields[] = "Item #" . ($idx + 1) . " Unit: '{$oldRow->Unit}' -> '" . ($row['Unit'] ?? '') . "'";
                            }
                            if ($this->normalizeNum($row['Price'] ?? 0) != $this->normalizeNum($oldRow->Default_Price)) {
                                $changesDetected = true;
                                $changedFields[] = "Item #" . ($idx + 1) . " Price: {$oldRow->Default_Price} -> " . ($row['Price'] ?? 0);
                            }
                        }
                    }
                }
            } else if ($request->has('Product_Desc') && !$request->has('GridData')) {
                // Fallback untuk single item pada form legacy
                $firstDtl = DB::table('purchase_request_dtl')->where("PR_No", $cek->PR_No)->first();
                if ($firstDtl) {
                    if ($this->normalizeStr($request->Product_Desc) !== $this->normalizeStr($firstDtl->Product_Desc)) {
                        $changesDetected = true;
                        $changedFields[] = "Product_Desc: '{$firstDtl->Product_Desc}' -> '" . $request->Product_Desc . "'";
                    }
                    if ($this->normalizeNum($request->Qty) != $this->normalizeNum($firstDtl->Qty)) {
                        $changesDetected = true;
                        $changedFields[] = "Qty: {$firstDtl->Qty} -> " . $request->Qty;
                    }
                    if ($this->normalizeStr($request->Specification) !== $this->normalizeStr($firstDtl->Specification)) {
                        $changesDetected = true;
                        $changedFields[] = "Specification diubah";
                    }
                    if ($this->normalizeStr($request->Unit) !== $this->normalizeStr($firstDtl->Unit)) {
                        $changesDetected = true;
                        $changedFields[] = "Unit: '{$firstDtl->Unit}' -> '" . $request->Unit . "'";
                    }
                }
            }
        }

        // Catatan approval baru dari textarea Remark
        $approvalRemark = trim((string)$request->Remark);

        // Jika PR terhubung ke Approval Workflow Policy Engine, gunakan service
        if (!empty($cek->Approval_Request_ID)) {
            try {
                DB::beginTransaction();

                if ($statReq == 2) {
                    $this->procurementApprovalService->reject((int) $cek->ID, Auth::user(), $approvalRemark ?: 'Rejected by approver');
                } else {
                    $this->procurementApprovalService->approve((int) $cek->ID, Auth::user(), $approvalRemark ?: 'Approved');
                }

                DB::commit();
                echo "success";
                return;
            } catch (\Throwable $th) {
                DB::rollBack();
                Log::error('Procurement Approval Error: ' . $th->getMessage());
                echo "Gagal: " . $th->getMessage();
                return;
            }
        }

        // ============================================================
        // TENTUKAN STATUS BARU (LEGACY FALLBACK)
        // ============================================================
        $nik = "";
        $uppername = "";

        if ($statReq == 2) {
            // === REJECT ===
            switch ($cek->Status) {
                case "APPROVAL_DEPT":
                    $nstatus = "REJECTED_DEPT";
                    break;
                case "APPROVAL_UPPER":
                    $nstatus = "REJECTED_UPPER";
                    break;
                case "APPROVAL_GA":
                    $nstatus = "REJECTED_GA";
                    break;
                default:
                    $nstatus = 'REJECTED';
                    break;
            }

            // Untuk reject, notifikasi dikirim ke creator PR
            $creatorUser = DB::table('users')->where('username', $cek->Created_By)->select('Emp_ID', 'Name', 'Email')->first();
            if ($creatorUser) {
                $nik = $creatorUser->Emp_ID;
                $uppername = $creatorUser->Name;
            }
        } else if ($statReq == 3 || $request->Action == 'APPROVE_CHANGES') {
            // === CREATOR APPROVE CHANGES ===
            if ($cek->Approval_Department == 'HCGA' || $cek->Department == 'HCGA') {
                $nstatus = 'APPROVAL_GA';
            } else {
                $nstatus = 'APPROVAL_UPPER';
            }

            $getlevel = DB::table('level_general')->where("Status_Approval", "LIKE", "%$nstatus%")->first();
            if ($getlevel) {
                $getupper = DB::table("organization_structure")
                    ->where("Level" . $getlevel->Level, 1)
                    ->where("Department", $cek->Approval_Department ?: $cek->Department)
                    ->first();
                if ($getupper) {
                    $nik = $getupper->NIK ?? '';
                    $uppername = $getupper->Name ?? '';
                }
            }
        } else {
            // === APPROVE ===
            if ($changesDetected) {
                // Ada perubahan data -> kembalikan ke creator untuk konfirmasi
                $nstatus = "CHANGES_REVIEWED_BY_CREATOR";

                $creatorUser = DB::table('users')->where('username', $cek->Created_By)->select('Emp_ID', 'Name', 'Email')->first();
                if ($creatorUser) {
                    $nik = $creatorUser->Emp_ID;
                    $uppername = $creatorUser->Name;
                }

                $changeLog = "Approver ($Saya) melakukan perubahan data berikut:\n" . implode("\n", $changedFields) . "\n\nPR dikembalikan ke creator untuk konfirmasi.";

                DB::table('purchase_request_log')->insert([
                    'PR_No' => $cek->PR_No,
                    'Action_By' => $Saya,
                    'Action_Date' => now(),
                    'Action_Status' => "CHANGES_DETECTED_BY_APPROVER: dikembalikan ke creator",
                    'Last_ID' => $cek->ID,
                    'Category' => $newCategory ?: $cek->Category,
                    'Sub_Category' => $newSubCategory ?: $cek->Sub_Category,
                    'Supplies_Type' => $newSuppliesType ?: $cek->Supplies_Type,
                    'Note' => $changeLog,
                    'Reference' => $newReference ?: $cek->Reference,
                ]);
            } else {
                // Tidak ada perubahan -> approval normal
                switch ($cek->Status) {
                    case "CREATED":
                        $nstatus = "PROPOSED";
                        break;
                    case "APPROVAL_UPPER":
                        $nstatus = 'APPROVAL_DEPT';

                        $getlevel = DB::table('level_general')->where("Status_Approval", "LIKE", "%$nstatus%")->first();
                        if ($getlevel) {
                            $getupper = DB::table("organization_structure")->where("Level" . $getlevel->Level, 1)->where("Department", $cek->Department)->first();
                            $nik = $getupper->NIK ?? '';
                            $uppername = $getupper->Name ?? '';
                        }
                        break;
                    case "APPROVAL_DEPT":
                        $nstatus = 'APPROVAL_GA';

                        $getlevel = DB::table('level_general')->where("Status_Approval", "LIKE", "%$nstatus%")->first();
                        if ($getlevel) {
                            $getupper = DB::table("organization_structure")->where("Level" . $getlevel->Level, 1)->where("Department", 'HCGA')->first();
                            $nik = $getupper->NIK ?? '';
                            $uppername = $getupper->Name ?? '';
                        }
                        break;
                    case "APPROVAL_GA":
                        $nstatus = "APPROVAL_FINANCE";

                        $getlevel = DB::table('level_general')->where("Status_Approval", "LIKE", "%$nstatus%")->first();
                        if ($getlevel) {
                            $getupper = DB::table("organization_structure")->where("Level" . $getlevel->Level, 1)->where("Department", 'FIN')->first();
                            $nik = $getupper->NIK ?? '';
                            $uppername = $getupper->Name ?? '';
                        }
                        break;
                    case "APPROVAL_FINANCE":
                        $nstatus = "APPROVED";
                        break;
                    case "PROPOSED":
                        $nstatus = "APPROVAL_UPPER";
                        break;
                    case "CHANGES_REVIEWED_BY_CREATOR":
                        if ($cek->Approval_Department == 'HCGA' || $cek->Department == 'HCGA') {
                            $nstatus = 'APPROVAL_GA';
                        } else {
                            $nstatus = 'APPROVAL_UPPER';
                        }
                        break;
                    default:
                        $nstatus = 'CREATED';
                        break;
                }
            }
        }

        // ============================================================
        // COMPUTE NOTE TERBARU
        // ============================================================
        $updatedNote = $cek->Note;
        if (!empty($approvalRemark)) {
            $updatedNote .= date("Y-m-d H:i:s") . " [$Saya] => " . $approvalRemark . "\n";
        }

        $data = [
            'PIC'               => $newPIC ?: $cek->PIC,
            'PIC_Phone'         => $newPICPhone ?: $cek->PIC_Phone,
            'PIC_Address'       => $newPICAddress ?: $cek->PIC_Address,
            'Estimated_Budget'  => $newEstimatedBudget > 0 ? $newEstimatedBudget : $cek->Estimated_Budget,
            'Department'        => $newDepartment ?: $cek->Department,
            'Category'          => $newCategory ?: $cek->Category,
            'Sub_Category'      => $newSubCategory ?: $cek->Sub_Category,
            'Supplies_Type'     => $newSuppliesType ?: $cek->Supplies_Type,
            'Note'              => $updatedNote,
            'Reference'         => $request->has('Reference') ? $newReference : $cek->Reference,
            'Status'            => $nstatus,
        ];

        if ($newSupplierID !== '') {
            $data['Supplier_ID'] = $newSupplierID;
            $sup = DB::table('supplier')->where('Supplier_ID', $newSupplierID)->first();
            if ($sup) {
                $data['Supplier_Name'] = $sup->Company_Name;
            }
        }
        if ($newPriorityStatus !== '') {
            $data['Priority_Status'] = $newPriorityStatus;
        }

        // ============================================================
        // PERSIST DETAIL PRODUK JIKA ADA DARI GRID / FORM
        // ============================================================
        $gridDataJson = $request->input('GridData');
        if (!empty($gridDataJson)) {
            $gridData = json_decode($gridDataJson, true);
            if (is_array($gridData) && count($gridData) > 0) {
                // Hapus detail lama dan ganti dengan detail baru
                DB::table('purchase_request_dtl')->where("PR_No", $cek->PR_No)->delete();
                foreach ($gridData as $product) {
                    DB::table('purchase_request_dtl')->insert([
                        'PR_No' => $cek->PR_No,
                        'Product_Code' => $product['Product_Code'] ?? '',
                        'Product_Desc' => $product['Product_Desc'] ?? '',
                        'Qty' => $this->normalizeNum($product['Qty'] ?? 0),
                        'Specification' => $product['Specification'] ?? '',
                        'Unit' => $product['Unit'] ?? 'PCS',
                        'Default_Price' => $this->normalizeNum($product['Price'] ?? 0),
                        'Location' => $LSP,
                        'Purchase_Category' => $data['Sub_Category'],
                        'Purchase_Type' => $data['Category'],
                        'Category' => $product['Category'] ?? $data['Category'],
                        'Remark' => $product['Remark'] ?? '',
                        'Attachment' => $product['Image'] ?? '',
                        'Reference' => $product['Reference'] ?? $data['Reference'],
                    ]);
                }
            }
        } else if ($request->has('Product_Desc')) {
            // Form single-product
            $firstDtl = DB::table('purchase_request_dtl')->where("PR_No", $cek->PR_No)->first();
            if ($firstDtl) {
                DB::table('purchase_request_dtl')->where("ID", $firstDtl->ID)->update([
                    'Product_Desc' => $request->Product_Desc,
                    'Specification' => $request->Specification ?? $firstDtl->Specification,
                    'Qty' => $this->normalizeNum($request->Qty),
                    'Unit' => $request->Unit ?? $firstDtl->Unit,
                    'Reference' => $request->Reference ?? $firstDtl->Reference,
                    'Purchase_Category' => $data['Sub_Category'],
                    'Purchase_Type' => $data['Category'],
                    'Category' => $data['Category'],
                ]);
            }
        }

        // Jika status APPROVAL_FINANCE, buat PO
        if ($nstatus == 'APPROVAL_FINANCE') {
            $PO_No = $this->createPurchaseSummary($cek);
            $PONO = $this->createPurchaseOrder($cek, $PO_No);
            $data['Processing_Status'] = $nstatus;
            $data['Processing_Date'] = now();
        }

        // ============================================================
        // LOG TRANSAKSI
        // ============================================================
        try {
            if ($nstatus !== 'CHANGES_REVIEWED_BY_CREATOR') {
                $stat = $nstatus == 'APPROVED' ? $nstatus : "APPROVAL STATUS {$cek->Status} => $nstatus";
                $prOld = json_decode(json_encode($cek), true);
                $prOld['Action_By'] = $Saya;
                $prOld['Action_Date'] = now();
                $prOld['Action_Status'] = $stat;
                $prOld["Last_ID"] = $prOld['ID'];
                unset($prOld["ID"]);
                DB::table('purchase_request_log')->insert($prOld);

                if ($nstatus != 'APPROVED') {
                    $prOld['Action_Status'] = "WAITING FOR $nstatus";
                    DB::table('purchase_request_log')->insert($prOld);
                }
            }
        } catch (\Throwable $th) {
            Log::error($th);
        }

        DB::table('purchase_request')->where("ID", $cek->ID)->update($data);

        DB::insert(DB::raw("INSERT INTO purchase_log (Location, Task_ID, Category, Sub_Category, Purchase_Type, Transaction, Status, Remark, Date_Trx, User_Trx) VALUES ('$LSP', '$cek->PR_No', '{$data['Category']}', '{$data['Sub_Category']}', '{$data['Supplies_Type']}', 'PURCHASE-REQUEST', '$nstatus', " . DB::getPdo()->quote($approvalRemark ?: 'PROCUREMENT_APPROVAL') . ", NOW(), '$Saya')"));

        // ============================================================
        // NOTIFIKASI
        // ============================================================
        $subNotif = "";

        if ($statReq == 2) {
            // REJECT
            $subjek = "IMPORTANT : PURCHASE REQUEST REJECTED REQUEST PR NO $cek->PR_No # , Request By : $Name";
            $subNotif = "PR {$cek->PR_No} Rejected, tolong di revisi sesuai catatan";
        } else if ($changesDetected && $nstatus == "CHANGES_REVIEWED_BY_CREATOR") {
            // PERUBAHAN DATA - NOTIFIKASI KE CREATOR
            $subjek = "PERUBAHAN DATA: PURCHASE REQUEST PR NO $cek->PR_No # , Request By : $Name";
            $subNotif = "PR {$cek->PR_No} mengalami perubahan data. Mohon konfirmasi perubahan.";
        } else {
            // APPROVE NORMAL - NOTIFIKASI KE ATASAN BERIKUTNYA
            $subjek = "IMPORTANT : PURCHASE REQUEST APPROVAL REQUEST PR NO $cek->PR_No # , Request By : $Name";
            $subNotif = "PR {$cek->PR_No} Need to be Approved";
        }

        if ($nik != '') {
            $us = DB::table('users')->where("Emp_ID", $nik)->select("id")->first();
            if ($us) {
                (new NotificationService)->makeNotification(
                    ($changesDetected && $nstatus == "CHANGES_REVIEWED_BY_CREATOR" ? "PR PERUBAHAN DATA" : "PR APPROVAL"),
                    $subNotif,
                    'approval',
                    Auth::user()->id ?? null,
                    $us->id
                );
            }
        }

        echo "success";
    }

    protected function createPurchaseSummary($pr)
    {
        $date = date('Y-m-d');
        $LSP = Auth::user()->Location;
        $Saya = Auth::user()->username;
        $ps = DB::table('purchase_summary')->where("Location", $LSP)->where("Purchase_Date", "LIKE", $date . "%")->count();

        $NoR = $ps + 1;
        if ($NoR < 10) $NoR = "0$NoR";
        $dateR = date('YmdHis');
        $PO_No = "PS/" . $LSP . "/$dateR-$NoR";

        DB::table('purchase_summary')->insert([
            'Location' => $LSP ?? '',
            'PR_No' => $pr->PR_No ?? '',
            'Task_ID' => $PO_No ?? '',
            'Purchase_Category' => $pr->Category ?? '',
            'Purchase_Sub_Category' => $pr->Sub_Category ?? '',
            'Purchase_Type' => $pr->Supplies_Type ?? '',
            'Status' => 'CLOSED',
            'Purchase_Date' => now(),
            'V1_ID' => $pr->Supplier_ID ?? '',
            'V1_Cur' => 'RP',
            'V1_Discount' => 0,
            'V1_Fee_Mgt' => 0,
            'V1_VAT' => 0,
            'V1_DC' => 0,
            'V1_Total' => 0,
            'V2_ID' => '',
            'V3_ID' => '',
            'V4_ID' => '',
            'V5_ID' => '',
            'Created_By' => $Saya,
            'Note' => $pr->Note ?? '',
            'Priority' => $pr->Priority_Status ?? '',
        ]);

        $prds = DB::table('purchase_request_dtl')->where("PR_No", $pr->PR_No)->get();
        foreach ($prds as $key => $value) {
            $price = $value->Default_Price == 0 || $value->Default_Price == null ? $value->Estimated_Budget : $value->Default_Price;
            DB::table('purchase_summary_detail')->insert([
                'PR_Related' => $value->PR_No,
                'Task_ID' => $PO_No,
                'Purchase_Category' => $value->Purchase_Category,
                'Purchase_Sub_Category' => $value->Purchase_Type,
                'Purchase_Type' => $value->Category,
                'Product_Code' => $value->Product_Code,
                'Qty' => $value->Qty,
                'Product_Desc' => $value->Product_Desc,
                'Specification' => $value->Specification,
                'Recommended_Spec' => $value->Specification,
                'Created_By' => $Saya,
                'Location' => $LSP,
                'Proposed' => $value->Qty,
                'Unit_Name' => $value->Unit,
                'Default_Price' => $price,
                'V1_Propose' => $price,
                'V1_TOP' => $price * $value->Qty,
                'V1_Win' => 1,
            ]);

            DB::table('purchase_request_dtl')->where("ID", $value->ID)->update([
                'PO_Task_No' => $PO_No,
                'PO_Task_Date' => now(),
            ]);
        }

        DB::table('purchase_request')->where('ID', $pr->ID)->update([
            'PO_Task_No' => $PO_No,
            'Processing_Status' => 'CREATED TO PO',
            'Processing_Date' => now(),
        ]);

        DB::table('purchase_log')->insert([
            'Location' => $LSP,
            'Task_ID' => $pr->PR_No,
            'Category' => $pr->Category,
            'Sub_Category' => $pr->Sub_Category,
            'Purchase_Type' => $pr->Supplies_Type,
            'Transaction' => 'PURCHASE-REQUEST-TO-PO',
            'Status' => 'CREATE TO PO',
            'Remark' => $pr->Note,
            'Date_Trx' => now(),
            'User_Trx' => $Saya,
        ]);

        DB::table('purchase_log')->insert([
            'Location' => $LSP,
            'Task_ID' => $pr->PR_No,
            'Category' => $pr->Category,
            'Sub_Category' => $pr->Sub_Category,
            'Purchase_Type' => $pr->Supplies_Type,
            'Transaction' => 'PURCHASE-ORDER',
            'Status' => 'NEW PO PROCESS',
            'Remark' => $pr->Note,
            'Date_Trx' => now(),
            'User_Trx' => $Saya,
        ]);

        return $PO_No;
    }

    protected function createPurchaseOrder($pr, $ps)
    {
        $LSP = Auth::user()->Location;
        $Saya = Auth::user()->username;
        $date = date('Y');
        $gg = DB::table('purchase_summary')->where('PR_No', $pr->PR_No)->where('Task_ID', $ps)->first();
        $query = DB::table("purchase_order")->where("PO_Date", "LIKE", "$date%")->get();
        $CQuery = count($query);
        $NoR = $CQuery + 1;
        if ($NoR < 10) $NoR = "0$NoR";
        $dateR = date('Ymd');
        $totalx = 0;
        $subtotalx = 0;
        $PONO = "PO/" . $LSP . "/INV/$dateR-$NoR";
        $sels = DB::table('purchase_summary_detail')->where('PR_Related', $pr->PR_No)->where('V1_Win', 1)->where('Task_ID', $ps)->get();
        $nums = count($sels);

        if ($nums != 0) {
            foreach ($sels as $task) {
                $totalx += $task->V1_TOP;
                $subtotalx += $task->V1_TOP;
                DB::table('purchase_order_dtl')->insert([
                    'Location' => $LSP,
                    'PO_No' => $PONO,
                    'Category' => $task->Purchase_Category,
                    'Sub_Category' => $task->Purchase_Sub_Category,
                    'Purchase_Type' => $task->Purchase_Type,
                    'Product_Code' => $task->Product_Code,
                    'Barcode' => $task->Product_Code,
                    'Product_Desc' => $task->Product_Desc,
                    'Product_Type' => $task->Product_Code,
                    'Qty' => $task->Qty,
                    'Unit' => $task->Unit_Name,
                    'Price' => $task->V1_Propose,
                    'Subtotal' => $task->V1_TOP,
                    'PPN' => 0,
                    'Total' => $task->V1_TOP,
                    'Remark' => addslashes($task->Specification ?? ''),
                    'PR_No' => $task->PR_Related,
                    'Currency' => 'RP',
                ]);
            }

            DB::table('purchase_request')->where("PR_No", $pr->PR_No)->update([
                'PO_No' => $PONO,
                'Processing_Status' => 'PO APPROVED',
                'Processing_Date' => now(),
            ]);

            if ($gg->V1_Discount != 0) {
                $totalx -= $gg->V1_Discount;
            }

            $ppn1 = 0;
            if ($gg->V1_Fee_Mgt != 0) {
                if ($gg->V1_Fee_VAT != 0) {
                    $totalx += $gg->V1_Fee_Mgt;
                    $ppn1 = round(($totalx * $gg->V1_VAT) / 100, 0);
                    $totalx += $ppn1;
                } else {
                    $ppn1 = round(($totalx * $gg->V1_VAT) / 100, 0);
                    $totalx += $ppn1;
                    $totalx += $gg->V1_Fee_Mgt;
                }
            } else {
                $ppn1 = round(($totalx * $gg->V1_VAT) / 100, 0);
                $totalx += $ppn1;
            }
            if ($gg->V1_DC != 0) {
                $totalx += $gg->V1_DC;
            }

            if ($subtotalx == 0) {
                $subtotalx = $pr->Estimated_Budget;
            }

            if ($totalx == 0) {
                $totalx = $pr->Estimated_Budget;
            }

            DB::table('purchase_order')->insert([
                'Location' => $LSP,
                'Process_By' => $Saya,
                'Purchase_Type' => $gg->Purchase_Type,
                'Purchase_Category' => $gg->Purchase_Category,
                'Purchase_Sub_Category' => $gg->Purchase_Sub_Category,
                'PO_No' => $PONO,
                'PO_Date' => now(),
                'PO_Method' => 'POSTPAID',
                'Supplier' => $gg->V1_ID,
                'Subtotal' => $subtotalx,
                'PPN' => $ppn1,
                'Total' => $totalx,
                'Status' => 'APPROVAL_FINANCE',
                'Remark' => $gg->Note,
                'Date_Trx' => now(),
                'User_Trx' => $Saya,
                'Task_ID' => $gg->Task_ID,
                'Delivery_Fee' => $gg->V1_DC,
                'Management_Fee' => $gg->V1_Fee_Mgt,
                'PPH' => $gg->V1_PPH ?? 0,
                'Management_Fee_Exclude_PPN' => $gg->V1_Fee_VAT ?? 0,
                'Discount' => $gg->V1_Discount,
                'PR_No' => $pr->PR_No,
                'Currency' => $gg->V1_Cur,
            ]);
        }

        return $PONO;
    }
}

