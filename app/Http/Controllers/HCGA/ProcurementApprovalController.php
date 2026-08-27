<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcurementApprovalController extends Controller
{
    public function index()
    {
        return view('humanresources.procurement.approval');
    }

    public function data()
    {
        $Saya = Auth::User()->username;
        $Name = Auth::User()->Name;
        $LSP = Auth::user()->Location;
        $getnik = Auth::user()->Emp_ID;
        $dept = Auth::user()->Department;
        $pos = Auth::user()->Position;

        $getlevelsx = DB::table("organization_structure")->where("NIK", $getnik)->first();
        $lepel = 0;

        if ($getlevelsx->Level1 == 1) $lepel = 1;
        else if ($getlevelsx->Level2 == 1) $lepel = 2;
        else if ($getlevelsx->Level3 == 1) $lepel = 3;
        else if ($getlevelsx->Level4 == 1) $lepel = 4;
        else if ($getlevelsx->Level5 == 1) $lepel = 5;
        else if ($getlevelsx->Level6 == 1) $lepel = 6;
        else if ($getlevelsx->Level7 == 1) $lepel = 7;
        else if ($getlevelsx->Level8 == 1) $lepel = 8;

        if ($lepel > 5) {
            return;
        }

        echo "<rows>";

        $kueri = DB::table('purchase_request')->where("Location", $LSP)->orderBy('ID', "DESC");

        if ($dept != 'HCGA') {
            $kueri->where("Approval_Department", $dept);
            if ($lepel == 1) {
                $kueri->where(function ($q) {
                    $q->where("Status", "APPROVAL_DEPT")->orWhere("Status", "REJECTED_GA");
                });
            } else if ($lepel == 3) {
                $kueri->where(function ($q) {
                    $q->where("Status", "APPROVAL_UPPER")->orWhere("Status", "REJECTED_DEPT");
                });
            }
        } else {
            $kueri->where("Status", "APPROVAL_GA");
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
        $Query = DB::table("master_department");
        if ($dpt != 'ICT' && $dpt != 'BOD' && $dpt != 'HCGA') {
            $Query->where("Initial", $dpt);
        }

        $Department = $Query->get();
        $company = DB::table('supplier')->where("Status", 'NORMAL')->get();

        $pr->details = DB::table('purchase_request_dtl')->where("PR_No", $request->PR_No)->get();
        return view('humanresources.procurement.approval-detail', compact('pr', 'Department', 'company'));
    }

    public function approval(Request $request)
    {
        // Ambil data PR yang sedang di-approve
        $cek = DB::table("purchase_request")->where("ID", $request->ID)->first();
        if (!$cek) {
            echo "error: PR not found";
            return;
        }

        $Saya = Auth::User()->username;
        $LSP = Auth::user()->Location;
        $dept = Auth::user()->Department;
        $Name = Auth::user()->Name;

        $ars = "";
        $ax = "";
        $isi = "";

        foreach ($_POST as $key => $value) {
            if ($key != 'DelPRNewDetail' && $key != 'SubmitPRNewDetail' && $key != 'AddPRNewDetail' && $key != 'Reference' && $key != 'Product_Desc' && $key != 'Specification' && $key != 'Qty' && $key != 'Unit' && $key != 'Description' && $key != 'Remark' && $key != 'Department' && $key != 'PIC' && $key != 'PIC_Phone' && $key != 'PIC_Address' && $key != 'Estimated_Budget' && $key != 'Category' && $key != 'Sub_Category' && $key != 'Supplies_Type' && $key != 'Priority_Status' && substr($key, 0, 2) != 't_') {
                $ars .= "$key = '$value',";
                $ax .= "$key,";
                $isi .= "'$value',";
            }
        }
        $ars = substr($ars, 0, -1);
        $ax = substr($ax, 0, -1);
        $isi = substr($isi, 0, -1);

        // ============================================================
        // DETECT PERUBAHAN DATA PRODUCT OLEH APPROVER
        // ============================================================
        $changesDetected = false;
        $changedFields = [];

        // Gunakan data dari request sebagai data BARU (setelah approver meng-edit)
        // dan data dari $cek sebagai data LAMA
        $newRemark = $request->Remark ?? $cek->Note ?? '';
        $newReference = $request->Reference ?? $cek->Reference ?? '';
        $newCategory = $request->Category ?? $cek->Category ?? '';
        $newSubCategory = $request->Sub_Category ?? $cek->Sub_Category ?? '';
        $newSuppliesType = $request->Supplies_Type ?? $cek->Supplies_Type ?? '';
        $newPIC = $request->PIC ?? $cek->PIC ?? '';
        $newPICPhone = $request->PIC_Phone ?? $cek->PIC_Phone ?? '';
        $newPICAddress = $request->PIC_Address ?? $cek->PIC_Address ?? '';
        $newEstimatedBudget = $request->Estimated_Budget ?? $cek->Estimated_Budget ?? '';
        $newDepartment = $request->Department ?? $cek->Department ?? '';

        // Bandingkan field-field utama
        if (trim($newRemark) !== trim($cek->Note)) {
            $changesDetected = true;
            $changedFields[] = "Note/Remark: " . ($cek->Note ?? '(kosong)') . " → " . $newRemark;
        }
        if (trim($newReference) !== trim($cek->Reference)) {
            $changesDetected = true;
            $changedFields[] = "Reference: " . ($cek->Reference ?? '(kosong)') . " → " . $newReference;
        }
        if ($newCategory !== $cek->Category) {
            $changesDetected = true;
            $changedFields[] = "Category: " . ($cek->Category ?? '(kosong)') . " → " . $newCategory;
        }
        if ($newSubCategory !== $cek->Sub_Category) {
            $changesDetected = true;
            $changedFields[] = "Sub_Category: " . ($cek->Sub_Category ?? '(kosong)') . " → " . $newSubCategory;
        }
        if ($newSuppliesType !== $cek->Supplies_Type) {
            $changesDetected = true;
            $changedFields[] = "Supplies_Type: " . ($cek->Supplies_Type ?? '(kosong)') . " → " . $newSuppliesType;
        }
        if ($newPIC !== $cek->PIC) {
            $changesDetected = true;
            $changedFields[] = "PIC: " . ($cek->PIC ?? '(kosong)') . " → " . $newPIC;
        }
        if ($newPICPhone !== $cek->PIC_Phone) {
            $changesDetected = true;
            $changedFields[] = "PIC_Phone: " . ($cek->PIC_Phone ?? '(kosong)') . " → " . $newPICPhone;
        }
        if ($newPICAddress !== $cek->PIC_Address) {
            $changesDetected = true;
            $changedFields[] = "PIC_Address: " . ($cek->PIC_Address ?? '(kosong)') . " → " . $newPICAddress;
        }
        if ($newEstimatedBudget != $cek->Estimated_Budget) {
            $changesDetected = true;
            $changedFields[] = "Estimated_Budget: " . ($cek->Estimated_Budget ?? 0) . " → " . $newEstimatedBudget;
        }
        if ($newDepartment !== $cek->Department) {
            $changesDetected = true;
            $changedFields[] = "Department: " . ($cek->Department ?? '(kosong)') . " → " . $newDepartment;
        }

        // Cek perubahan di detail product (grid)
        if (!$changesDetected) {
            $dtlOld = DB::table('purchase_request_dtl')->where("PR_No", $cek->PR_No)->get();
            // Data detail baru bisa dikirim melalui POST dengan prefix t_ (nama standar DHTMLX)
            // atau melalui field yang dikirim approver
            // Untuk deteksi sederhana: jika ada field t_* yang tidak match dengan data lama
            foreach ($_POST as $key => $value) {
                if (substr($key, 0, 2) == 't_') {
                    // Field t_ adalah data dari grid DHTMLX
                    $changesDetected = true;
                    $changedFields[] = "Product detail ditambah/diubah oleh approver";
                    break;
                }
            }
        }

        // ============================================================
        // TENTUKAN STATUS BARU
        // ============================================================
        $nik = "";
        $uppername = "";

        if ($request->stat == 2) {
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
            // Untuk reject, notifikasi ke creator
            $creatorUser = DB::table('users')->where('username', $cek->Created_By)->select('Emp_ID', 'Name', 'Email')->first();
            if ($creatorUser) {
                $nik = $creatorUser->Emp_ID;
                $uppername = $creatorUser->Name;
            }
        } else {
            // === APPROVE ===
            if ($changesDetected) {
                // === ADA PERUBAHAN DATA ===
                // Kembalikan ke creator untuk konfirmasi
                $nstatus = "CHANGES_REVIEWED_BY_CREATOR";
                $statNote = "PERUBAHAN DATA: PR dikembalikan ke creator untuk konfirmasi perubahan oleh approver";

                // Temukan creator
                $creatorUser = DB::table('users')->where('username', $cek->Created_By)->select('Emp_ID', 'Name', 'Email')->first();
                if ($creatorUser) {
                    $nik = $creatorUser->Emp_ID;
                    $uppername = $creatorUser->Name;
                }

                // Jika creator ditemukan, log perubahan detail
                if ($creatorUser) {
                    $changeLog = "Approver ($Saya) melakukan perubahan data berikut:\n";
                    $changeLog .= implode("\n", $changedFields);
                    $changeLog .= "\n\nPR dikembalikan ke creator untuk konfirmasi.";

                    DB::table('purchase_request_log')->insert([
                        'PR_No' => $cek->PR_No,
                        'Action_By' => $Saya,
                        'Action_Date' => now(),
                        'Action_Status' => "CHANGES_DETECTED_BY_APPROVER: dikembalikan ke creator",
                        'Last_ID' => $cek->ID,
                        'Category' => $newCategory,
                        'Sub_Category' => $newSubCategory,
                        'Supplies_Type' => $newSuppliesType,
                        'Note' => $changeLog,
                        'Reference' => $newReference,
                    ]);
                }
            } else {
                // === TIDAK ADA PERUBAHAN DATA ===
                // Lanjutkan approval normal
                switch ($cek->Status) {
                    case "CREATED":
                        $nstatus = "PROPOSED";
                        break;
                    case "APPROVAL_UPPER":
                        $nstatus = 'APPROVAL_DEPT';

                        $getlevel = DB::table('level_general')->where("Status_Approval", "LIKE", "%$nstatus%")->first();
                        $getupper = DB::table("organization_structure")->where("Level" . $getlevel->Level, 1)->where("Department", $dept)->first();

                        $nik = $getupper->NIK ?? '';
                        $uppername = $getupper->Name ?? '';
                        break;
                    case "APPROVAL_DEPT":
                        $nstatus = 'APPROVAL_GA';

                        $getlevel = DB::table('level_general')->where("Status_Approval", "LIKE", "%$nstatus%")->first();
                        $getupper = DB::table("organization_structure")->where("Level" . $getlevel->Level, 1)->where("Department", 'HCGA')->first();
                        $nik = $getupper->NIK ?? '';
                        $uppername = $getupper->Name ?? '';
                        break;
                    case "APPROVAL_GA":
                        $nstatus = "APPROVAL_FINANCE";

                        $getlevel = DB::table('level_general')->where("Status_Approval", "LIKE", "%$nstatus%")->first();
                        $getupper = DB::table("organization_structure")->where("Level" . $getlevel->Level, 1)->where("Department", 'FIN')->first();
                        $nik = $getupper->NIK ?? '';
                        $uppername = $getupper->Name ?? '';
                        break;
                    case "APPROVAL_FINANCE":
                        $nstatus = "APPROVED";
                        break;
                    case "PROPOSED":
                        $nstatus = "APPROVAL_UPPER";
                        break;
                    case "CHANGES_REVIEWED_BY_CREATOR":
                        // Creator sudah menyetujui perubahan, lanjutkan ke tahap approval berikutnya
                        $nstatus = 'APPROVAL_UPPER';
                        break;
                    default:
                        $nstatus = 'CREATED';
                        break;
                }
            }
        }

        // Buat data update
        $data = [
            'Note' => $cek->Note . date("Y-m-d H:i:s") . " => " . $request->Remark . " \n",
            'Reference' => $request->Reference ?? $cek->Reference,
            'Status' => $nstatus,
        ];

        // Jika status APPROVED, buat PO
        if ($nstatus == 'APPROVED') {
            $PO_No = $this->createPurchaseSummary($cek);
            $PONO = $this->createPurchaseOrder($cek, $PO_No);
            $data['Processing_Status'] = "APPROVAL_FINANCE";
            $data['Processing_Date'] = now();
        }

        // ============================================================
        // LOG & NOTIFIKASI
        // ============================================================
        try {
            if ($nstatus !== 'CHANGES_REVIEWED_BY_CREATOR') {
                $stat = $nstatus == 'APPROVED' ? $nstatus : "APPROVAL STATUS {$request->Status} => $nstatus";
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

        DB::table('purchase_request')->where("ID", $request->ID)->update($data);

        DB::insert(DB::raw("INSERT INTO purchase_log (Location, Task_ID, Category, Sub_Category, Purchase_Type, Transaction, Status, Remark, Date_Trx, User_Trx) VALUES ('$LSP', '$request->PR_No', '$request->Category', '$request->Sub_Category', '$request->Supplies_Type', 'PURCHASE-REQUEST', '$nstatus', '$request->Note', NOW(), '$Saya')"));

        // ============================================================
        // NOTIFIKASI
        // ============================================================
        $subNotif = "";

        if ($request->stat == 2) {
            // REJECT
            $subjek = "IMPORTANT : PURCHASE REQUEST REJECTED REQUEST PR NO $request->PR_No # , Request By : $Name";
            $subNotif = "PR {$request->PR_No} Rejected, tolong di revisi sesuai catatan";
            $badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>$uppername</b><Br><br>

                        Mohon dilakukan pengecekan lebih lanjut untuk Purchase Request tersebut. Pengecekan dapat dilakukan melalui system ERP pada menu HR -> GA -> New Procurement<br><br>

                        No PR : $request->PR_No<br><br>
                        Reference : $request->Reference<br><br>
                        Jenis Request : $request->Category // $request->Sub_Category // $request->Supplies_Type<br><br>
                        Note : $request->Note<br>

                        <br><br>
                        Anda dapat melakukan pengecekan melalui link berikut ini :<a href='https://" . env('APP_URL') . "/hrd-procurement-index' >KLIK DISINI</a><br><br>

                        Terima kasih<br>
                        PT GREEN ENERGI UTAMA

            </div>";
        } else if ($changesDetected && $nstatus == "CHANGES_REVIEWED_BY_CREATOR") {
            // PERUBAHAN DATA - NOTIFIKASI KE CREATOR
            $subjek = "PERUBAHAN DATA: PURCHASE REQUEST PR NO $request->PR_No # , Request By : $Name";
            $subNotif = "PR {$request->PR_No} mengalami perubahan data. Mohon konfirmasi perubahan.";
            $badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>$uppername</b><Br><br>

                        Ada perubahan data pada Purchase Request Anda oleh approver. Mohon dilakukan pengecekan dan konfirmasi perubahan melalui system ERP.<br><br>

                        No PR : $request->PR_No<br><br>
                        Perubahan yang dilakukan:<br>
                        " . implode("<br>", $changedFields) . "<br><br>
                        Anda dapat mengecek perubahan melalui link berikut ini :<a href='https://" . env('APP_URL') . "/hrd-procurement-detail?PR_No=$request->PR_No' >KLIK DISINI</a><br><br>

                        Terima kasih<br>
                        PT GREEN ENERGI UTAMA

            </div>";

            // Kirim notifikasi ke creator
            if ($nik != '') {
                $us = DB::table('users')->where("Emp_ID", $nik)->select("id")->first();
                (new NotificationService)->makeNotification(
                    "PR PERUBAHAN DATA: {$request->PR_No}",
                    "PR {$request->PR_No} mengalami perubahan data. Silahkan cek dan konfirmasi.",
                    'approval',
                    Auth::user()->id ?? null,
                    $us->id ?? null
                );
            }
        } else {
            // APPROVE NORMAL - NOTIFIKASI KE ATASAN BERIKUTNYA
            $subjek = "IMPORTANT : PURCHASE REQUEST APPROVAL REQUEST PR NO $request->PR_No # , Request By : $Name";
            $subNotif = "PR {$request->PR_No} Need to be Approved";
            $badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>$uppername</b><Br><br>

                        Mohon dilakukan approval untuk Purchase Request tersebut. persetujuan dapat dilakukan melalui system ERP pada menu Procurement -> Request -> Approval<br><br>

                        No PR : $request->PR_No<br><br>
                        Reference : $request->Reference<br><br>
                        Jenis Request : $request->Category // $request->Sub_Category // $request->Supplies_Type<br><br>
                        Note : $request->Note<br>

                        <br><br>
                        Anda dapat melakukan approval melalui link berikut ini :<a href='https://" . env('APP_URL') . "/hrd-procurement-approval-mail?ID=$request->PR_No&linkkey=&saya=$Saya' >KLIK DISINI</a><br><br>

                        Terima kasih<br>
                        PT GREEN ENERGI UTAMA

            </div>";
        }

        // Kirim notifikasi ke recipient yang sesuai
        if ($nik != '') {
            $us = DB::table('users')->where("Emp_ID", $nik)->select("id")->first();
            (new NotificationService)->makeNotification(
                ($changesDetected && $nstatus == "CHANGES_REVIEWED_BY_CREATOR" ? "PR PERUBAHAN DATA" : "PR APPROVAL"),
                $subNotif,
                'approval',
                Auth::user()->id ?? null,
                $us->id ?? null
            );
        }

        // Email log
        DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date) VALUES('".($uppername ? ($getupper->Email ?? $creatorUser->Email) ?? '' : "") ."','$subjek','$badan','UNSENT',NOW(),NOW())"));

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
            'Note' => $pr->Remark ?? '',
            'Priority' => $pr->Priority_Status ?? '',
        ]);

        $prds = DB::table('purchase_request_dtl')->where("PR_No", $pr->PR_No)->get();
        foreach ($prds as $key => $value) {
            $price = $value->Default_Price == 0 || $value->Default_Price == null ? $value->Estimated_Budget : 0;
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
                    'Remark' => addslashes($task->Note),
                    'PR_No' => $task->PR_Related,
                    'Currency' => 'RP',
                ]);
            }

            DB::table('purchase_request')->where("PR_No", $task->PR_Related)->update([
                'PO_No' => $PONO,
                'Processing_Status' => 'PO APPROVED',
                'Processing_Date' => now(),
            ]);
            if ($gg->V1_Discount != 0) {
                $totalx -= $gg->V1_Discount;
            }

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
                $ppn1 = round(($totalx * $gg->V1_VAT) / 100, 0);;
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
                'PPH' => $gg->V1_PPH,
                'Management_Fee_Exclude_PPN' => $gg->V1_Fee_VAT,
                'Discount' => $gg->V1_Discount,
                'PR_No' => $pr->PR_No,
                'Currency' => $gg->V1_Cur,
            ]);
        }

        return $PONO;
    }
}