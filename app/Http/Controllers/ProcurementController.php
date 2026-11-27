<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Services\Approval\ProcurementApprovalService; // <-- Tambahkan baris ini!

class ProcurementController extends Controller
{


// Menampilkan Halaman Master Product List
    public function productList() {
        return view('humanresources.procurement.product-list');
    }

    // Endpoint XML Data Master Produk
    
    // Endpoint XML Data Master Produk
    public function xmlProductList() {
        $products = DB::table('product_code')->orderBy('ID', 'DESC')->get();
        
        $xml = '<?xml version="1.0" encoding="iso-8859-1"?>';
        $xml .= '<rows>';
        
        $nomor = 1;
        foreach ($products as $row) {
            $xml .= '<row id="' . $row->ID . '">';
            $xml .= '<cell>' . $nomor . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Product_Code ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Description ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Specification ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Unit_Name ?? 'PCS') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Category ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->status_Muncul ?? '-') . '</cell>';
            $xml .= '</row>';
            $nomor++;
        }
        
        $xml .= '</rows>';

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    
    // Menyimpan Produk Baru ke Tabel product_code (Dengan Opsi Upload File atau Link Foto)
    // Menyimpan Produk Baru ke Tabel product_code
    public function storeProduct(Request $request) {
    $request->validate([
        'product_code'  => 'required|string|unique:product_code,Product_Code',
        'description'   => 'required|string',
        'specification' => 'nullable|string',
        'uom'           => 'required|string',
        'main_category' => 'required|in:ASSET,NON ASSET',
        'category'      => 'required|in:BARANG,JASA', // <-- Validasi untuk Category
        'status_Muncul' => 'required|in:ACTIVE,NONACTIVE',
        'photo_type'    => 'required|in:upload,link',
        'photo_file'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        'photo_link'    => 'nullable|url',
    ]);

    $fotoPath = null;

    // Opsi 1: Upload File ke Server
    if ($request->photo_type === 'upload' && $request->hasFile('photo_file')) {
        $file = $request->file('photo_file');
        if ($file->isValid()) {
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/product_images');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file->move($destinationPath, $fileName);
            $fotoPath = 'uploads/product_images/' . $fileName;
        }
    } 
    // Opsi 2: Menggunakan Link / URL Gambar
    elseif ($request->photo_type === 'link' && !empty($request->photo_link)) {
        $fotoPath = $request->photo_link;
    }

    // Ambil Location dari session user yang login
    $username = session('username') ?? 'admin';
    $userData = DB::table('users')->where('username', $username)->first();
    $userLocation = $userData ? $userData->Location : 'GEU-HO';

    DB::table('product_code')->insert([
        'Product_Code'  => $request->product_code,
        'Description'   => $request->description,
        'Specification' => $request->specification,
        'Unit_Name'     => $request->uom,
        'Main_Category' => $request->main_category,
        'Category'      => $request->category,      // <-- Disimpan ke kolom Category tabel database
        'status_Muncul' => $request->status_Muncul,
        'Location'      => $userLocation,
        'Foto'          => $fotoPath,
        'Created_Date'  => now(),
        'Created_By'    => $username,
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => 'Master produk berhasil ditambahkan!'
    ]);
}


// Mengambil data produk untuk keperluan Edit
    public function editProduct($id) {
        $product = DB::table('product_code')->where('ID', $id)->first();
        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'Produk tidak ditemukan!'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $product]);
    }

    // Memproses Update Data Produk
    public function updateProduct(Request $request, $id) {
        $request->validate([
            'description'   => 'required|string',
            'specification' => 'nullable|string',
            'uom'           => 'required|string',
            'main_category' => 'required|in:ASSET,NON ASSET',
            'category'      => 'required|in:BARANG,JASA',
            'status_Muncul' => 'required|in:ACTIVE,NONACTIVE',
            'photo_type'    => 'required|in:upload,link',
            'photo_file'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'photo_link'    => 'nullable|url',
        ]);

        $product = DB::table('product_code')->where('ID', $id)->first();
        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'Produk tidak ditemukan!'], 404);
        }

        $fotoPath = $product->Foto; // Default menggunakan foto lama

        // Opsi 1: Upload File baru ke Server
        if ($request->photo_type === 'upload' && $request->hasFile('photo_file')) {
            $file = $request->file('photo_file');
            if ($file->isValid()) {
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('uploads/product_images');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $file->move($destinationPath, $fileName);
                $fotoPath = 'uploads/product_images/' . $fileName;
            }
        } 
        // Opsi 2: Menggunakan Link / URL Gambar baru
        elseif ($request->photo_type === 'link' && !empty($request->photo_link)) {
            $fotoPath = $request->photo_link;
        }

        DB::table('product_code')->where('ID', $id)->update([
            'Description'   => $request->description,
            'Specification' => $request->specification,
            'Unit_Name'     => $request->uom,
            'Main_Category' => $request->main_category,
            'Category'      => $request->category,
            'status_Muncul' => $request->status_Muncul,
            'Foto'          => $fotoPath,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data master produk berhasil diperbarui!'
        ]);
    }


// Menampilkan Dashboard Procurement / Ringkasan Data
    public function dashboardProcurement() {
        // Hitung statistik data
        $totalProduct   = DB::table('product_code')->count();
        $totalSupplier  = DB::table('supplier')->count();
        $totalPR        = DB::table('purchase_request')->count();
        $pendingPR      = DB::table('purchase_request')->where('Status', 'PENDING')->count();
        $approvedPR     = DB::table('purchase_request')->where('Status', 'APPROVED')->count();
        $rejectedPR     = DB::table('purchase_request')->where('Status', 'REJECTED')->count();

        return view('humanresources.procurement.procurement-dashboard', compact(
            'totalProduct', 'totalSupplier', 'totalPR', 'pendingPR', 'approvedPR', 'rejectedPR'
        ));
    }

    // --- KODE YANG SUDAH ADA SEBELUMNYA (RFQ Detail) ---
    // public function createDetail(Request $request) {
    //     $rfqNo = $request->query('rfq');

    //     $rfq = DB::table('rfq as r')
    //             ->leftJoin('supplier as s', 'r.Supplier_ID', '=', 's.Supplier_ID')
    //             ->where('r.RFQ_No', $rfqNo)
    //             ->first();

    //     if (!$rfq) {
    //         return redirect()->back()->with('error', 'Data RFQ tidak ditemukan.');
    //     }

    //     $pr_items = DB::table('purchase_request_dtl')
    //                 ->where('PR_No', $rfq->PR_No)
    //                 ->get();

    //     // return view('procurement.qtn-create-detail', compact('rfq', 'pr_items'));
    //                 // Contoh di ProcurementController.php saat mereturn view create PR:
    //         return view('humanresources.procurement.purchase-request-create', compact('generated_pr_no', 'materials', 'departments', 'userDepartment'));
    // }


  // 1. Menampilkan Form Create PR (Multi Produk + Master Department)
    public function createPR() {
        $lastPr = DB::table('purchase_request')->orderBy('ID', 'DESC')->first();
        $nextId = $lastPr ? ($lastPr->ID + 1) : 1;
        $generated_pr_no = 'PR/GEU-HO/' . date('Ym') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Ambil daftar material dari tabel 'product_code'
        $materials = DB::table('product_code')->select('Product_Code', 'Description')->get();

        // Ambil daftar department dari tabel 'department'
        $departments = DB::table('department')->orderBy('Department', 'ASC')->get(); 

        // Ambil department user yang sedang login
        // Sesuaikan cara pengambilan session/auth di aplikasi Anda (misal: session('username') atau Auth::user())
        $currentUsername = session('username') ?? 'admin'; // Sesuaikan dengan key session login Anda
        $userData = DB::table('users')->where('username', $currentUsername)->first();
        
        // Department user login (misal isinya "ICT" atau "FIN")
        $userDepartment = $userData->Department ?? ''; 

        // return view('procurement.purchase-request-create', compact('generated_pr_no', 'materials', 'departments', 'userDepartment'));
        return view('humanresources.procurement.purchase-request-create', compact('generated_pr_no', 'materials', 'departments', 'userDepartment'));
    }

    // 2. Endpoint AJAX Auto-Populate Detail Berdasarkan 'Product_Code' (Tetap sama)
    public function getMaterialDetail(Request $request) {
        $code = $request->query('code');
        $material = DB::table('product_code')->where('Product_Code', $code)->first();

        if ($material) {
            return response()->json([
                'status' => 'success',
                'nama'   => $material->Description ?? '',
                'spec'   => $material->Specification ?? '-',
                'uom'    => $material->Unit_Name ?? 'PCS',
                'image'  => $material->Foto ?? ''
            ]);
        }

        return response()->json(['status' => 'not_found'], 404);
    }

    public function storePR(Request $request) {
        $request->validate([
            'pr_no'                => 'required|string',
            'pr_date'              => 'required|date',
            'department'           => 'required',
            'supplies_type'        => 'nullable|string',
            'urgency'              => 'nullable|string',
            'procurement_category' => 'nullable|string',
            'hope_date'            => 'nullable|date',
            'cost_center'          => 'nullable|string',
            'product_code'         => 'required|array',
            'product_code.*'       => 'required|string',
            'qty'                  => 'required|array',
            'qty.*'                => 'required|numeric|min:1',
            'price'                => 'nullable|array',
            'price.*'              => 'nullable|numeric|min:0',
            'attachment'           => 'nullable|array',
            'attachment.*'         => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120'
        ]);

        // Hitung total budget secara akurat di sisi server
        $calculatedBudget = 0;
        if ($request->has('product_code')) {
            foreach ($request->product_code as $idx => $code) {
                if (!empty($code)) {
                    $q = floatval($request->qty[$idx] ?? 1);
                    $p = floatval($request->price[$idx] ?? 0);
                    $calculatedBudget += ($q * $p);
                }
            }
        }
        $estimatedBudget = $calculatedBudget > 0 ? $calculatedBudget : floatval($request->estimated_budget ?? 0);

        $username = session('username') ?? (Auth::user()->username ?? 'admin');
        $userObj  = Auth::user() ?? DB::table('users')->where('username', $username)->first();
        $location = $userObj->Location ?? 'GEU-HO';

        $suppliesType = strtoupper($request->supplies_type ?? 'NON-OPS');
        $mainCategory = strtoupper($request->main_category ?? $request->Category ?? 'NON ASSET');
        $subCategory  = strtoupper($request->category ?? $request->Sub_Category ?? 'BARANG');
        $procCategory = $request->procurement_category ?? 'Rutin';
        $urgency      = $request->urgency ?? 'Normal';

        $approvalService = app(ProcurementApprovalService::class);

        // Preflight cek ketersediaan Policy Approval sebelum menyimpan data PR
        if ($userObj) {
            try {
                $approvalService->assertCanSubmit([
                    'Location'             => $location,
                    'Department'           => $request->department,
                    'Estimated_Budget'     => $estimatedBudget,
                    'Supplies_Type'        => $suppliesType,
                    'Category'             => $mainCategory,
                    'Sub_Category'         => $subCategory,
                    'Procurement_Category' => $procCategory,
                    'Urgency'              => $urgency,
                ], $userObj);
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Policy Approval tidak ditemukan / belum aktif: ' . $e->getMessage());
            }
        }

        DB::beginTransaction();
        try {
            // 1. Simpan ke Header purchase_request
            $prId = DB::table('purchase_request')->insertGetId([
                'Location'             => $location,
                'Supplies_Type'        => $suppliesType,
                'Urgency'              => $urgency,
                'PR_No'                => $request->pr_no,
                'PR_Date'              => $request->pr_date,
                'Hope_Date'            => $request->hope_date,
                'Department'           => $request->department,
                'Cost_Center'          => $request->cost_center,
                'Category'             => $mainCategory,
                'Sub_Category'         => $subCategory,
                'Procurement_Category' => $procCategory,
                'PIC'                  => $request->pic_name,
                'PIC_Phone'            => $request->PIC_Phone,
                'PIC_Email'            => $request->pic_email,
                'PIC_Address'          => $request->site_destination,
                'Estimated_Budget'     => $estimatedBudget,
                'Note'                 => $request->note,
                'Status'               => 'PENDING',
                'Created_By'           => $username,
                'Created_Date'         => now(),
            ]);

            // 1.1 Simpan History Note ke pr_notes_history
            if (!empty(trim($request->note))) {
                DB::table('pr_notes_history')->insert([
                    'PR_No'         => $request->pr_no,
                    'Action_Status' => 'CREATED PR',
                    'Note'          => trim($request->note),
                    'Created_By'    => $username,
                    'Created_Date'  => now(),
                ]);
            }

            // 2. Looping simpan rincian multi-produk ke purchase_request_dtl
            if ($request->has('product_code')) {
                foreach ($request->product_code as $index => $code) {
                    if (!empty($code)) {
                        $attachmentPath = null;

                        if ($request->hasFile("attachment.{$index}")) {
                            $file = $request->file('attachment')[$index];
                            if ($file->isValid()) {
                                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                                $destinationPath = public_path('uploads/pr_attachments');

                                if (!file_exists($destinationPath)) {
                                    mkdir($destinationPath, 0777, true);
                                }

                                $file->move($destinationPath, $fileName);
                                $attachmentPath = 'uploads/pr_attachments/' . $fileName;
                            }
                        }

                        $qty   = floatval($request->qty[$index] ?? 1);
                        $price = floatval($request->price[$index] ?? 0);

                        DB::table('purchase_request_dtl')->insert([
                            'PR_No'         => $request->pr_no,
                            'Product_Code'  => $code,
                            'Product_Desc'  => $request->product_desc[$index] ?? '',
                            'Specification' => $request->specification[$index] ?? '',
                            'Qty'           => $qty,
                            'Unit'          => $request->unit[$index] ?? 'PCS',
                            'Default_Price' => $price,
                            'Attachment'    => $attachmentPath,
                        ]);
                    }
                }
            }

            // 3. Submit ke Dynamic Approval Engine (Approval Inbox & Mobile Apps)
            if ($userObj) {
                $prRow = DB::table('purchase_request')->where('ID', $prId)->first();
                $approvalService->submit($prRow, $userObj);

                // 3.1 Kirim notifikasi OneSignal ke approver tahap pertama yang terkait
                try {
                    $freshPr = DB::table('purchase_request')->where('ID', $prId)->first();
                    $reqId   = $freshPr->Approval_Request_ID ?? null;

                    if (!$reqId && !empty($request->pr_no)) {
                        $reqRow = DB::table('approval_request')
                            ->where('Request_No', $request->pr_no)
                            ->orderBy('ID', 'DESC')
                            ->first();
                        $reqId = $reqRow->ID ?? null;
                    }

                    $firstTask = null;
                    if ($reqId) {
                        $firstTask = DB::table('approval_request_task')
                            ->where('Request_ID', $reqId)
                            ->whereIn(DB::raw('UPPER(COALESCE(Status, \'\'))'), ['PENDING', 'WAITING'])
                            ->orderBy('Stage_Order', 'ASC')
                            ->orderBy('Level_Number', 'ASC')
                            ->orderBy('ID', 'ASC')
                            ->first();
                    }

                    $approverTargets = [];
                    if ($firstTask) {
                        $userQuery = DB::table('users');

                        if (!empty($firstTask->Position_ID)) {
                            $userQuery->where('Position_ID', $firstTask->Position_ID);
                        } elseif (!empty($firstTask->Position_Name)) {
                            $userQuery->where(DB::raw('UPPER(Position)'), strtoupper(trim($firstTask->Position_Name)));
                        }

                        $approverTargets = $userQuery->pluck('username')->all();
                    }

                    if (!empty($approverTargets)) {
                        $posLabel   = !empty($firstTask->Position_Name) ? " ({$firstTask->Position_Name})" : '';
                        $nominalStr = 'Rp ' . number_format($estimatedBudget, 0, ',', '.');

                        $this->sendOneSignalNotification(
                            $approverTargets,
                            'PR Baru Perlu Approval',
                            "PR {$request->pr_no} ({$request->department}) sebesar {$nominalStr} menunggu approval Anda{$posLabel}.",
                            [
                                'pr_no' => $request->pr_no,
                                'type'  => 'PR_CREATED',
                                'url'   => url('mobile/procurement/pr/approval'),
                            ]
                        );
                    }
                } catch (\Exception $notifEx) {
                    \Log::warning('Gagal mengirim notifikasi OneSignal saat storePR: ' . $notifEx->getMessage());
                }
            }

            DB::commit();
            return redirect('purchase-request-list')->with('success', 'Purchase Request berhasil disimpan & dikirim ke Approval Inbox!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan PR: ' . $e->getMessage());
        }
    }





    // Menampilkan Detail PR (Mode Read-Only)
    public function detailPR(Request $request) {
        $prNo = $request->query('PR_No');

        $header = DB::table('purchase_request')->where('PR_No', $prNo)->first();
        $details = DB::table('purchase_request_dtl')->where('PR_No', $prNo)->get();
        $noteHistories = DB::table('pr_notes_history')
            ->where('PR_No', $prNo)
            ->orderBy('ID', 'DESC')
            ->get();
        $departments = DB::table('department')->orderBy('Department', 'ASC')->get();

        return view('humanresources.procurement.purchase-request-detail', compact('header', 'details', 'departments', 'noteHistories'));
    }
        

    // Menampilkan Detail PR Status PENDING / RETURNED
    public function detailPRPending(Request $request) {
        $prNo = $request->query('PR_No');

        $header = DB::table('purchase_request')->where('PR_No', $prNo)->first();
        $details = DB::table('purchase_request_dtl')->where('PR_No', $prNo)->get();

        // Ambil riwayat percakapan dari pr_notes_history
        $noteHistories = DB::table('pr_notes_history')
            ->where('PR_No', $prNo)
            ->orderBy('ID', 'DESC')
            ->get();

        // Ambil daftar tahapan & status dari Dynamic Approval Engine (approval_request_task)
        $approvalReq = null;
        $approvalTasks = collect();
        $canUserAct = false;

        $username = session('username') ?? (Auth::user()->username ?? 'admin');
        $userObj  = Auth::user() ?? DB::table('users')->where('username', $username)->first();

        if ($header && !empty($header->Approval_Request_ID)) {
            $approvalReq = DB::table('approval_request')->where('ID', $header->Approval_Request_ID)->first();
            $approvalTasks = DB::table('approval_request_task as t')
                ->leftJoin('master_position as mp', 't.Position_ID', '=', 'mp.ID')
                ->leftJoin('users as u', 't.Acted_By', '=', 'u.id')
                ->select(
                        't.*',
                        'mp.*',
                        'u.Name as Acted_By_Name',
                        'u.username as Acted_By_Username',
                        't.ID as Task_ID',
                        't.Status as Task_Status',
                        't.Notes as Task_Notes'
                    )
                ->where('t.Request_ID', $header->Approval_Request_ID)
                ->orderBy('t.Stage_Order', 'ASC')
                ->orderBy('t.ID', 'ASC')
                ->get();

            if ($userObj) {
                $pendingTasks = app(ProcurementApprovalService::class)->pendingTasksForUser((int) $header->ID, (int) $userObj->id);
                $canUserAct = count($pendingTasks) > 0;
            }
        } else {
            // Fallback untuk PR lama sebelum terhubung ke Approval Engine
            $canUserAct = true;
        }

        // Ambil master data agar dropdown bisa dipilih
        $materials = DB::table('product_code')->select('Product_Code', 'Description')->get();
        $departments = DB::table('department')->orderBy('Department', 'ASC')->get();

        return view('humanresources.procurement.purchase-request-detail-pending', compact(
            'header', 'details', 'materials', 'departments', 'noteHistories',
            'approvalReq', 'approvalTasks', 'canUserAct'
        ));
    }



public function detailPREdit(Request $request)
{
    try {
        $prNo = trim((string) $request->query('PR_No'));
        $header = DB::table('purchase_request')->where('PR_No', $prNo)->first();

        if (!$header) {
            return view('humanresources.procurement.purchase-request-detail-editpr', [
                'header'        => null,
                'details'       => collect(),
                'materials'     => collect(),
                'departments'   => collect(),
                'noteHistories' => collect(),
            ]);
        }

        $currentStatus = strtoupper(trim((string) ($header->Status ?? '')));
        if ($currentStatus !== 'PENDING') {
            return '<div class="alert alert-warning mt-3" style="font-size: 12px;">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                PR <strong>' . e($prNo) . '</strong> berstatus <strong>' . e($currentStatus) . '</strong>. Edit atau pembatalan PR hanya diperbolehkan saat status masih <strong>PENDING</strong>.
            </div>';
        }

        $details = DB::table('purchase_request_dtl')
            ->where('PR_No', $prNo)
            ->orderBy('ID', 'ASC')
            ->get();

        $noteHistories = collect();
        if (Schema::hasTable('pr_notes_history')) {
            $noteHistories = DB::table('pr_notes_history')
                ->where('PR_No', $prNo)
                ->orderBy('ID', 'DESC')
                ->get();
        }

        $materials   = DB::table('product_code')->select('Product_Code', 'Description')->get();
        $departments = DB::table('department')->orderBy('Department', 'ASC')->get();

        return view('humanresources.procurement.purchase-request-detail-editpr', compact(
            'header',
            'details',
            'materials',
            'departments',
            'noteHistories'
        ));
    } catch (\Throwable $e) {
        \Log::error('detailPREdit error: ' . $e->getMessage());
        return response(
            '<div class="alert alert-danger mt-3" style="font-size: 12px;">Gagal memuat form Edit PR: ' . e($e->getMessage()) . '</div>',
            500
        );
    }
}

public function cancelPR(Request $request)
{
    $request->validate([
        'pr_no'  => 'required|string',
        'reason' => 'nullable|string|max:500',
    ]);

    $prNo     = trim((string) $request->input('pr_no'));
    $reason   = trim((string) ($request->input('reason') ?? 'Dibatalkan oleh pemohon'));
    $username = session('username') ?? (Auth::user()->username ?? 'admin');

    $pr = DB::table('purchase_request')->where('PR_No', $prNo)->first();
    if (!$pr) {
        return response()->json([
            'status'  => 'error',
            'message' => "Data Purchase Request {$prNo} tidak ditemukan."
        ], 404);
    }

    $currentStatus = strtoupper(trim((string) ($pr->Status ?? '')));
    if ($currentStatus !== 'PENDING') {
        return response()->json([
            'status'  => 'error',
            'message' => "PR {$prNo} tidak dapat dibatalkan karena statusnya saat ini adalah {$currentStatus} (hanya status PENDING yang dapat dibatalkan)."
        ], 422);
    }

    DB::beginTransaction();
    try {
        $cancelNote  = "[CANCELLED by {$username} (" . date('d-m-Y H:i') . ")] {$reason}";
        $updatedNote = trim((string) ($pr->Note ?? ''));
        $updatedNote = $updatedNote !== '' ? ($updatedNote . ' | ' . $cancelNote) : $cancelNote;

        DB::table('purchase_request')
            ->where('PR_No', $prNo)
            ->update([
                'Status'     => 'CANCELLED',
                'Note'       => $updatedNote,
                'Updated_By' => $username,
            ]);

        if (Schema::hasTable('pr_notes_history')) {
            DB::table('pr_notes_history')->insert([
                'PR_No'         => $prNo,
                'Action_Status' => 'CANCELLED',
                'Note'          => $reason,
                'Created_By'    => $username,
                'Created_Date'  => now(),
            ]);
        }

        if (!empty($pr->Approval_Request_ID) && Schema::hasTable('approval_request')) {
            DB::table('approval_request')
                ->where('ID', $pr->Approval_Request_ID)
                ->update(['Status' => 'CANCELLED']);
        }

        DB::commit();

        return response()->json([
            'status'  => 'success',
            'message' => "Purchase Request {$prNo} berhasil dibatalkan!"
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status'  => 'error',
            'message' => 'Gagal membatalkan PR: ' . $e->getMessage()
        ], 500);
    }
}




    // Proses Approve atau Reject PR
    
    // Proses Approve atau Reject PR melalui Dynamic Approval Engine
    public function approvePR(Request $request) {
        $prNo     = $request->pr_no;
        $action   = strtoupper(trim($request->action)); // 'APPROVED' atau 'REJECTED'
        $note     = trim($request->approval_note ?? '');
        $username = session('username') ?? (Auth::user()->username ?? 'admin');

        $pr = DB::table('purchase_request')->where('PR_No', $prNo)->first();
        if (!$pr) {
            return response()->json(['status' => 'error', 'message' => 'Data PR tidak ditemukan.'], 404);
        }

        $userObj = Auth::user() ?? DB::table('users')->where('username', $username)->first();
        $approvalService = app(ProcurementApprovalService::class);

        try {
            // Jika terhubung ke Approval Engine, eksekusi via ProcurementApprovalService
            if (!empty($pr->Approval_Request_ID) && $userObj) {
                if ($action === 'APPROVED') {
                    $approvalService->approve((int) $pr->ID, $userObj, $note);
                } else {
                    $approvalService->reject((int) $pr->ID, $userObj, $note ?: 'Ditolak oleh approver');
                }

                $updatedPr = DB::table('purchase_request')->where('ID', $pr->ID)->first();
                return response()->json([
                    'status'  => 'success',
                    'message' => "Tahap approval berhasil diproses! Status PR {$prNo} saat ini: {$updatedPr->Status}."
                ]);
            }

            // Fallback legacy untuk PR lama yang belum memiliki Approval_Request_ID
            DB::table('purchase_request')
                ->where('PR_No', $prNo)
                ->update([
                    'Status'              => $action,
                    'Note'                => $note ?: $pr->Note,
                    'Approval_User'       => $username,
                    'Approval_Date'       => now(),
                    'Approval_Department' => session('department') ?? 'ICT',
                ]);

            if (!empty($note)) {
                DB::table('pr_notes_history')->insert([
                    'PR_No'         => $prNo,
                    'Action_Status' => $action,
                    'Note'          => $note,
                    'Created_By'    => $username,
                    'Created_Date'  => now(),
                ]);
            }

            return response()->json([
                'status'  => 'success',
                'message' => "Purchase Request {$prNo} berhasil diubah menjadi {$action}!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
    
    // Proses Update / Edit PR yang Statusnya PENDING
    
    public function updatePR(Request $request) {
        $prNo = $request->pr_no;
        $username = session('username') ?? 'admin';

        $request->validate([
            'pr_date'        => 'required|date',
            'department'     => 'required',
            'product_code'   => 'required|array',
            'product_code.*' => 'required|string',
            'qty'            => 'required|array',
            'qty.*'          => 'required|numeric|min:1',
            'price'          => 'nullable|array',
            'price.*'        => 'nullable|numeric|min:0',
        ]);

        // Hitung ulang total budget di sisi server untuk keamanan data
        $calculatedBudget = 0;
        if ($request->has('product_code')) {
            foreach ($request->product_code as $idx => $code) {
                if (!empty($code)) {
                    $q = floatval($request->qty[$idx] ?? 1);
                    $p = floatval($request->price[$idx] ?? 0);
                    $calculatedBudget += ($q * $p);
                }
            }
        }

        // Gunakan hasil kalkulasi server (atau fallback ke input request)
        $estimatedBudget = $calculatedBudget > 0 ? $calculatedBudget : floatval($request->estimated_budget ?? 0);

        // 1. Update Header purchase_request termasuk Estimated_Budget
        DB::table('purchase_request')->where('PR_No', $prNo)->update([
            'PR_Date'          => $request->pr_date,
            'Department'       => $request->department,
            'Category'         => $request->main_category ?? $request->Category ?? 'ASSET',
            'Sub_Category'     => $request->category ?? $request->Sub_Category ?? 'BARANG',
            'PIC'              => $request->pic_name ?? '',
            'PIC_Phone'        => $request->pic_phone ?? '',
            'PIC_Email'        => $request->pic_email ?? '',
            'PIC_Address'      => $request->site_destination ?? '',
            'Estimated_Budget' => $estimatedBudget,
            'Note'             => $request->note ?? '',
            'Updated_By'       => $username,
        ]);

        // 1.1 Simpan History Note
        if (!empty(trim($request->note))) {
            DB::table('pr_notes_history')->insert([
                'PR_No'         => $prNo,
                'Action_Status' => 'UPDATED PR',
                'Note'          => trim($request->note),
                'Created_By'    => $username,
                'Created_Date'  => now(),
            ]);
        }

        // 2. Hapus detail lama & masukkan kembali data detail baru beserta Price
        DB::table('purchase_request_dtl')->where('PR_No', $prNo)->delete();

        if ($request->has('product_code')) {
            foreach ($request->product_code as $index => $code) {
                if (!empty($code)) {
                    $attachmentPath = $request->old_attachment[$index] ?? null;

                    if ($request->hasFile("attachment.{$index}")) {
                        $file = $request->file('attachment')[$index];
                        if ($file && $file->isValid()) {
                            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $destinationPath = public_path('uploads/pr_attachments');
                            
                            if (!file_exists($destinationPath)) {
                                mkdir($destinationPath, 0777, true);
                            }

                            $file->move($destinationPath, $fileName);
                            $attachmentPath = 'uploads/pr_attachments/' . $fileName;
                        }
                    }

                    $qty   = floatval($request->qty[$index] ?? 1);
                    $price = floatval($request->price[$index] ?? 0);

                    DB::table('purchase_request_dtl')->insert([
                        'PR_No'         => $prNo,
                        'Product_Code'  => $code,
                        'Product_Desc'  => $request->product_desc[$index] ?? '',
                        'Specification' => $request->specification[$index] ?? '',
                        'Qty'           => $qty,
                        'Unit'          => $request->unit[$index] ?? 'PCS',
                        'Default_Price' => $price, // Pastikan kolom 'Price' ada di tabel purchase_request_dtl
                        'Attachment'    => $attachmentPath ?? '',
                    ]);
                }
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Purchase Request {$prNo} berhasil diperbarui! Estimated Budget: Rp " . number_format($estimatedBudget, 0, ',', '.')
        ]);
    }

    
    // Menampilkan Detail PR Status APPROVED
    public function detailPRApproved(Request $request) {
        $prNo = $request->query('PR_No');

        $header = DB::table('purchase_request')->where('PR_No', $prNo)->first();
        if (!$header) {
            return response()->json(['status' => 'error', 'message' => 'Data PR tidak ditemukan'], 404);
        }

        $details = DB::table('purchase_request_dtl')->where('PR_No', $prNo)->get();
        
        $noteHistories = DB::table('pr_notes_history')
                            ->where('PR_No', $prNo)
                            ->orderBy('ID', 'ASC')
                            ->get();
        
        // Ambil data RFQ secara terpisah agar tidak error join
        $listRfqsRaw = DB::table('rfq')->where('PR_No', $prNo)->get();
        $listRfqs = [];
        $existingSupplierIds = [];

        foreach ($listRfqsRaw as $rfq) {
            $supplier = DB::table('supplier')->where('Supplier_ID', $rfq->Supplier_ID)->first();
            $listRfqs[] = (object)[
                'RFQ_No'       => $rfq->RFQ_No,
                'RFQ_Date'     => $rfq->RFQ_Date,
                'Supplier_ID'  => $rfq->Supplier_ID,
                'Company_Name' => $supplier->Company_Name ?? $rfq->Supplier_ID,
                'Status'       => $rfq->Status ?? 'SENT'
            ];
            $existingSupplierIds[] = $rfq->Supplier_ID;
        }

        $materials = DB::table('product_code')->select('Product_Code', 'Description')->get();
        $departments = DB::table('department')->orderBy('Department', 'ASC')->get();
        
        // Ambil supplier yang belum dipilih
        $suppliersQuery = DB::table('supplier')->select('Supplier_ID', 'Company_Name');
        if (!empty($existingSupplierIds)) {
            $suppliersQuery->whereNotIn('Supplier_ID', $existingSupplierIds);
        }
        $suppliers = $suppliersQuery->orderBy('Company_Name', 'ASC')->get();

        return view('humanresources.procurement.purchase-request-detail-approved', compact('header', 'details', 'materials', 'departments', 'noteHistories', 'suppliers', 'listRfqs'));
    }

    // Proses Lanjut ke RFQ (Sesuai Flow Procurement)
    public function convertToRFQ(Request $request) {
    $prNo       = $request->pr_no;
    $supplierId = $request->supplier_id;
    $rfqDate    = $request->rfq_date ?? now()->toDateString();

    if (empty($supplierId)) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Silakan pilih Vendor / Supplier terlebih dahulu!'
        ], 422);
    }

    // Generate Nomor RFQ Unik
    $rfqNo = 'RFQ/GEU/' . date('Ym') . '-' . rand(1000, 9999);
    $username = session('username') ?? 'admin';

    // 1. Insert RFQ baru ke tabel rfq (Multi-vendor)
    DB::table('rfq')->insert([
        'RFQ_No'       => $rfqNo,
        'PR_No'        => $prNo,
        'Supplier_ID'  => $supplierId,
        'RFQ_Date'     => $rfqDate,
        'Status'       => 'SENT',
        'Created_By'   => $username,
    ]);


    $prDetails = DB::table('purchase_request_dtl')
    ->where('PR_No', $prNo)
    ->orderBy('ID', 'ASC')
    ->get();

$sampleDtl = DB::table('rfq_dtl')->first();
$dtlCols = $sampleDtl ? array_keys((array) $sampleDtl) : [];

foreach ($prDetails as $item) {
    $itemArr = (array) $item;
    $prodCode = (string) $this->pickRowValue($itemArr, ['Product_Code', 'product_code'], '');
    $prodDesc = (string) $this->pickRowValue($itemArr, ['Product_Desc', 'product_desc', 'Description'], '');
    $spec     = (string) $this->pickRowValue($itemArr, ['Specification', 'specification', 'Spec'], '');
    $qty      = (float)  $this->pickRowValue($itemArr, ['Qty', 'qty'], 1);
    $unit     = (string) $this->pickRowValue($itemArr, ['Unit', 'unit'], 'PCS');
    $price    = (float)  $this->pickRowValue($itemArr, ['Default_Price', 'Price', 'price', 'Est_Price'], 0);
    $subtotal = $qty * $price;

    $insertDtl = [];
    if (!empty($dtlCols)) {
        foreach ($dtlCols as $col) {
            if (strtolower($col) === 'id') {
                continue;
            }
            $lc = strtolower($col);
            if ($lc === 'rfq_no') {
                $insertDtl[$col] = $rfqNo;
            } elseif ($lc === 'pr_no') {
                $insertDtl[$col] = $prNo;
            } elseif ($lc === 'product_code') {
                $insertDtl[$col] = $prodCode;
            } elseif (in_array($lc, ['product_desc', 'description'], true)) {
                $insertDtl[$col] = $prodDesc;
            } elseif (in_array($lc, ['specification', 'spec'], true)) {
                $insertDtl[$col] = $spec;
            } elseif ($lc === 'qty') {
                $insertDtl[$col] = $qty;
            } elseif ($lc === 'unit') {
                $insertDtl[$col] = $unit;
            } elseif (in_array($lc, ['price', 'est_price', 'default_price'], true)) {
                $insertDtl[$col] = $price;
            } elseif (in_array($lc, ['subtotal', 'total', 'amount'], true)) {
                $insertDtl[$col] = $subtotal;
            }
        }
    } else {
        $insertDtl = [
            'RFQ_No'        => $rfqNo,
            'Product_Code'  => $prodCode,
            'Product_Desc'  => $prodDesc,
            'Specification' => $spec,
            'Qty'           => $qty,
            'Unit'          => $unit,
            'Price'         => $price,
            'Subtotal'      => $subtotal,
        ];
    }

    if (!empty($insertDtl)) {
        DB::table('rfq_dtl')->insert($insertDtl);
    }
}

    // 2. Ambil semua RFQ terkait PR ini untuk digabungkan di header purchase_request
    $allRfqs = DB::table('rfq')->where('PR_No', $prNo)->pluck('RFQ_No')->toArray();
    $combinedRfqNo = implode(', ', $allRfqs);

    // 3. Update tabel purchase_request
    DB::table('purchase_request')
        ->where('PR_No', $prNo)
        ->update([
            'RFQ_No'              => $combinedRfqNo,
            'Processing_Status'   => 'WAITING_RFQ',
        ]);

    return response()->json([
        $status  = 'success',
        'rfq_no'  => $rfqNo,
        'message' => "RFQ berhasil dibuat ({$rfqNo}) untuk Vendor {$supplierId}!"
    ]);
    }



    public function createDetail(Request $request) {
        $rfqNo = $request->query('rfq');

        $rfq = DB::table('rfq as r')
            ->leftJoin('supplier as s', DB::raw('r.Supplier_ID COLLATE utf8mb4_unicode_ci'), '=', DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('purchase_request as pr', DB::raw('r.PR_No COLLATE utf8mb4_unicode_ci'), '=', DB::raw('pr.PR_No COLLATE utf8mb4_unicode_ci'))
            ->select('r.*', 's.Company_Name', 's.Contact_Person', 's.Phone', 's.Email', 'pr.Department', 'pr.PIC_Address as Site_Tujuan', 'pr.Estimated_Budget')
            ->where('r.RFQ_No', $rfqNo)
            ->first();

        if (!$rfq) {
            return '<div class="alert alert-danger m-3">Data RFQ tidak ditemukan.</div>';
        }

        // Ambil detail item dari rfq_dtl
        $rfq_items = DB::table('rfq_dtl')
            ->where('RFQ_No', $rfqNo)
            ->orderBy('ID', 'ASC')
            ->get();

        // Fallback jika data rfq_dtl kosong
        if ($rfq_items->isEmpty()) {
            $prItems = DB::table('purchase_request_dtl')->where('PR_No', $rfq->PR_No)->get();
            foreach ($prItems as $item) {
                DB::table('rfq_dtl')->insert([
                    'RFQ_No'        => $rfq->RFQ_No,
                    'PR_No'         => $rfq->PR_No,
                    'Supplier_ID'   => $rfq->Supplier_ID,
                    'Product_Code'  => $item->Product_Code,
                    'Product_Desc'  => $item->Product_Desc,
                    'Specification' => $item->Specification,
                    'Qty'           => $item->Qty,
                    'Unit'          => $item->Unit,
                    'Est_Price'     => $item->Default_Price ?? 0,
                    'Quoted_Price'  => 0,
                    'Total_Price'   => 0,
                    'Attachment'    => $item->Attachment ?? null,
                    'Created_By'    => session('username') ?? 'admin',
                    'Created_Date'  => now(),
                ]);
            }
            $rfq_items = DB::table('rfq_dtl')->where('RFQ_No', $rfqNo)->orderBy('ID', 'ASC')->get();
        }

        return view('humanresources.procurement.qtn-create-detail', compact('rfq', 'rfq_items'));
    }

public function createRFQForm(Request $request)
{
    $prNo = trim((string) $request->query('PR_No'));
    if ($prNo === '') {
        return '<div class="alert alert-warning mb-0">Silakan pilih Nomor PR terlebih dahulu.</div>';
    }

    $header = DB::table('purchase_request')->where('PR_No', $prNo)->first();
    if (!$header) {
        return '<div class="alert alert-danger mb-0">Data Purchase Request tidak ditemukan.</div>';
    }

    $details = DB::table('purchase_request_dtl')
        ->where('PR_No', $prNo)
        ->orderBy('ID', 'ASC')
        ->get();

    $noteHistories = collect();
    try {
        $noteHistories = DB::table('pr_notes_history')
            ->where('PR_No', $prNo)
            ->orderBy('ID', 'DESC')
            ->get();
    } catch (\Exception $e) {
        $noteHistories = collect();
    }

    // Ambil histori / tahapan approval dari Dynamic Approval Engine secara aman
    $approvalReq = null;
    $approvalTasks = collect();

    try {
        if (!empty($header->Approval_Request_ID)) {
            $approvalReq = DB::table('approval_request')->where('ID', $header->Approval_Request_ID)->first();
        }
        if (!$approvalReq) {
            $approvalReq = DB::table('approval_request')
                ->where('Request_No', $prNo)
                ->orderBy('ID', 'DESC')
                ->first();
        }

        if ($approvalReq) {
            $rawTasks = DB::table('approval_request_task')
                ->where('Request_ID', $approvalReq->ID)
                ->orderBy('Stage_Order', 'ASC')
                ->orderBy('Level_Number', 'ASC')
                ->orderBy('ID', 'ASC')
                ->get();

            $posMap = [];
            try {
                foreach (DB::table('master_position')->get() as $mp) {
                    $posMap[(string) $mp->ID] = $mp->Position_Name ?? ($mp->Position_Code ?? '');
                }
            } catch (\Exception $ex) {
            }

            $userMap = [];
            try {
                foreach (DB::table('users')->get() as $u) {
                    $label = $u->Name ?? ($u->name ?? ($u->username ?? ''));
                    if (isset($u->id)) {
                        $userMap[(string) $u->id] = $label;
                    }
                    if (!empty($u->username)) {
                        $userMap[(string) $u->username] = $label ?: $u->username;
                    }
                }
            } catch (\Exception $ex) {
            }

            foreach ($rawTasks as $t) {
                $posKey = (string) ($t->Position_ID ?? '');
                $actKey = (string) ($t->Acted_By ?? '');
                $approvalTasks->push((object) [
                    'Stage_Order'  => $t->Stage_Order ?? ($t->Level_Number ?? 1),
                    'Position_Name'=> $t->Position_Name ?? ($posMap[$posKey] ?? ($t->Position_Code ?? 'Approver')),
                    'Task_Status'  => $t->Status ?? 'PENDING',
                    'Acted_By_Name'=> $userMap[$actKey] ?? ($actKey !== '' ? $actKey : '-'),
                    'Acted_At'     => $t->Acted_At ?? '-',
                    'Task_Notes'   => $t->Notes ?? '-',
                ]);
            }
        }
    } catch (\Exception $e) {
        \Log::warning('createRFQForm approval history error: ' . $e->getMessage());
    }

    $listRfqsRaw = DB::table('rfq')
        ->where('PR_No', $prNo)
        ->orderBy('ID', 'ASC')
        ->get();

    $listRfqs = [];
    $existingSupplierIds = [];

    foreach ($listRfqsRaw as $rfq) {
        $supplier = DB::table('supplier')->where('Supplier_ID', $rfq->Supplier_ID)->first();
        $listRfqs[] = (object) [
            'RFQ_No'       => $rfq->RFQ_No,
            'RFQ_Date'     => $rfq->RFQ_Date,
            'Supplier_ID'  => $rfq->Supplier_ID,
            'Company_Name' => $supplier->Company_Name ?? $rfq->Supplier_ID,
            'Status'       => $rfq->Status ?? 'SENT',
        ];
        $existingSupplierIds[] = $rfq->Supplier_ID;
    }

    $suppliersQuery = DB::table('supplier')->select('Supplier_ID', 'Company_Name');
    if (!empty($existingSupplierIds)) {
        $suppliersQuery->whereNotIn('Supplier_ID', $existingSupplierIds);
    }
    $suppliers = $suppliersQuery->orderBy('Company_Name', 'ASC')->get();

    return view('humanresources.procurement.purchasing-order-rfq-created', compact(
        'header',
        'details',
        'noteHistories',
        'approvalReq',
        'approvalTasks',
        'suppliers',
        'listRfqs'
    ));
}


public function getApprovedPRForRFQ()
{
    try {
        $rows = DB::table('purchase_request')->get();

        $filtered = [];
        foreach ($rows as $row) {
            $arr = (array) $row;
            $statusVal = '';
            foreach ($arr as $k => $v) {
                if (strtolower($k) === 'status') {
                    $statusVal = strtoupper(trim((string) $v));
                    break;
                }
            }

            if (!in_array($statusVal, ['APPROVED', 'PARTIAL_PO'], true)) {
                continue;
            }

            $getField = function ($keys, $default = '') use ($arr) {
                foreach ($keys as $k) {
                    if (array_key_exists($k, $arr) && $arr[$k] !== null) {
                        return $arr[$k];
                    }
                    foreach ($arr as $ak => $av) {
                        if (strtolower($ak) === strtolower($k) && $av !== null) {
                            return $av;
                        }
                    }
                }
                return $default;
            };

            $filtered[] = [
                'id'               => (int) $getField(['ID', 'id'], 0),
                'PR_No'            => (string) $getField(['PR_No', 'pr_no'], ''),
                'PR_Date'          => (string) $getField(['PR_Date', 'pr_date'], ''),
                'Department'       => (string) $getField(['Department', 'department'], '-'),
                'PIC'              => (string) $getField(['PIC', 'pic', 'Created_By', 'created_by'], '-'),
                'Estimated_Budget' => (float) $getField(['Estimated_Budget', 'estimated_budget'], 0),
                'Status'           => $statusVal,
            ];
        }

        usort($filtered, function ($a, $b) {
            return $b['id'] <=> $a['id'];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $filtered,
        ]);
    } catch (\Exception $e) {
        \Log::error('getApprovedPRForRFQ error: ' . $e->getMessage());
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}


private function pickRowValue(array $arr, array $candidates, $default = null)
{
    foreach ($candidates as $key) {
        if (array_key_exists($key, $arr) && $arr[$key] !== null) {
            return $arr[$key];
        }
        foreach ($arr as $ak => $av) {
            if (strtolower((string) $ak) === strtolower((string) $key) && $av !== null) {
                return $av;
            }
        }
    }
    return $default;
}

public function getRFQEditPanel(Request $request)
{
    $rfqNo = trim((string) $request->query('rfq_no'));
    if ($rfqNo === '') {
        return '<div class="alert alert-warning mb-0">Nomor RFQ tidak ditemukan.</div>';
    }

    try {
        $rfqRow = DB::table('rfq')->where('RFQ_No', $rfqNo)->first();
        if (!$rfqRow) {
            $rfqRow = DB::table('rfq')->where('rfq_no', $rfqNo)->first();
        }
        if (!$rfqRow) {
            return '<div class="alert alert-danger mb-0">Data RFQ <strong>' . e($rfqNo) . '</strong> tidak ditemukan.</div>';
        }

        $rfqArr = (array) $rfqRow;
        $header = (object) [
            'RFQ_No'      => (string) $this->pickRowValue($rfqArr, ['RFQ_No', 'rfq_no'], $rfqNo),
            'PR_No'       => (string) $this->pickRowValue($rfqArr, ['PR_No', 'pr_no'], '-'),
            'Supplier_ID' => (string) $this->pickRowValue($rfqArr, ['Supplier_ID', 'supplier_id'], ''),
            'RFQ_Date'    => (string) $this->pickRowValue($rfqArr, ['RFQ_Date', 'rfq_date'], date('Y-m-d')),
            'Status'      => (string) $this->pickRowValue($rfqArr, ['Status', 'status'], 'SENT'),
            'Note'        => (string) $this->pickRowValue($rfqArr, ['Note', 'note', 'Notes', 'notes', 'Remark', 'remark'], ''),
        ];

        $detailRows = DB::table('rfq_dtl')
            ->where('RFQ_No', $header->RFQ_No)
            ->orderBy('ID', 'ASC')
            ->get();

        // Fallback jika tabel detail menggunakan nama kolom/tabel berbeda
        if ($detailRows->isEmpty()) {
            try {
                $detailRows = DB::table('rfq_dtl')
                    ->where('rfq_no', $header->RFQ_No)
                    ->get();
            } catch (\Exception $ex) {
                $detailRows = collect();
            }
        }



        $details = [];
        foreach ($detailRows as $d) {
            $dArr = (array) $d;
            $details[] = (object) [
                'ID'            => $this->pickRowValue($dArr, ['ID', 'id'], 0),
                'Product_Code'  => (string) $this->pickRowValue($dArr, ['Product_Code', 'product_code'], ''),
                'Product_Desc'  => (string) $this->pickRowValue($dArr, ['Product_Desc', 'product_desc', 'Description', 'description'], ''),
                'Specification' => (string) $this->pickRowValue($dArr, ['Specification', 'specification', 'Spec', 'spec'], ''),
                'Qty'           => (float) $this->pickRowValue($dArr, ['Qty', 'qty'], 1),
                'Unit'          => (string) $this->pickRowValue($dArr, ['Unit', 'unit'], 'PCS'),
                'Price'         => (float) $this->pickRowValue($dArr, ['Est_Price', 'Quoted_Price', 'Price', 'price', 'Default_Price'], 0),
                'Note'          => (string) $this->pickRowValue($dArr, ['Note', 'note', 'Remark', 'remark'], ''),
            ];
        }

        if (empty($details) && !empty($header->PR_No) && $header->PR_No !== '-') {
            $prItems = DB::table('purchase_request_dtl')
                ->where('PR_No', $header->PR_No)
                ->orderBy('ID', 'ASC')
                ->get();

            foreach ($prItems as $d) {
                $dArr = (array) $d;
                $details[] = (object) [
                    'ID'            => $this->pickRowValue($dArr, ['ID', 'id'], 0),
                    'Product_Code'  => (string) $this->pickRowValue($dArr, ['Product_Code', 'product_code'], ''),
                    'Product_Desc'  => (string) $this->pickRowValue($dArr, ['Product_Desc', 'product_desc', 'Description'], ''),
                    'Specification' => (string) $this->pickRowValue($dArr, ['Specification', 'specification', 'Spec'], ''),
                    'Qty'           => (float)  $this->pickRowValue($dArr, ['Qty', 'qty'], 1),
                    'Unit'          => (string) $this->pickRowValue($dArr, ['Unit', 'unit'], 'PCS'),
                    'Price'         => (float)  $this->pickRowValue($dArr, ['Default_Price', 'Est_Price', 'Price', 'price'], 0),
                    'Note'          => '',
                ];
            }
        }

        $productOptions = [];
        try {
            $rawProducts = DB::table('product_code')
                ->orderBy('Product_Code', 'ASC')
                ->get();

            foreach ($rawProducts as $p) {
                $pArr   = (array) $p;
                $status = strtoupper(trim((string) $this->pickRowValue($pArr, ['status_Muncul', 'status_muncul', 'Status'], 'ACTIVE')));
                if ($status !== '' && !in_array($status, ['ACTIVE', 'AKTIF', 'Y', '1'], true)) {
                    continue;
                }

                $pCode = trim((string) $this->pickRowValue($pArr, ['Product_Code', 'product_code'], ''));
                $pName = trim((string) $this->pickRowValue($pArr, ['Description', 'description', 'Product_Desc'], ''));
                $pSpec = trim((string) $this->pickRowValue($pArr, ['Specification', 'specification'], ''));
                $pUnit = trim((string) $this->pickRowValue($pArr, ['Unit_Name', 'unit_name', 'Unit'], 'PCS')) ?: 'PCS';

                if ($pCode !== '' || $pName !== '') {
                    $productOptions[] = [
                        'code'  => $pCode,
                        'name'  => $pName,
                        'spec'  => $pSpec,
                        'unit'  => $pUnit,
                        'price' => 0,
                    ];
                }
            }
        } catch (\Exception $ex) {
            \Log::warning('getRFQEditPanel load product_code error: ' . $ex->getMessage());
        }

        $suppliers = DB::table('supplier')
            ->orderBy('Company_Name', 'ASC')
            ->get();

        return view('humanresources.procurement.purchasing-order-rfq-edit', compact(
            'header',
            'details',
            'suppliers',
            'productOptions'
        ));
    } catch (\Exception $e) {
        \Log::error('getRFQEditPanel error: ' . $e->getMessage());
        return '<div class="alert alert-danger mb-0">Gagal memuat panel edit RFQ: ' . e($e->getMessage()) . '</div>';
    }
}

public function updateRFQData(Request $request)
{
    $rfqNo      = trim((string) $request->input('rfq_no'));
    $supplierId = trim((string) $request->input('supplier_id'));
    $rfqDate    = trim((string) $request->input('rfq_date'));
    $status     = trim((string) $request->input('status', 'SENT'));
    $note       = (string) $request->input('note', '');
    $items      = $request->input('items', []);

    if ($rfqNo === '' || $supplierId === '' || $rfqDate === '') {
        return response()->json([
            'status'  => 'error',
            'message' => 'No. RFQ, Vendor/Supplier, dan Tanggal RFQ wajib diisi.'
        ], 422);
    }

    DB::beginTransaction();
    try {
        $rfqRow = DB::table('rfq')->where('RFQ_No', $rfqNo)->first();
        if (!$rfqRow) {
            $rfqRow = DB::table('rfq')->where('rfq_no', $rfqNo)->first();
        }
        if (!$rfqRow) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => "Data RFQ {$rfqNo} tidak ditemukan."
            ], 404);
        }

        $rfqArr = (array) $rfqRow;
        $prNo   = (string) $this->pickRowValue($rfqArr, ['PR_No', 'pr_no'], '');
        $actor  = auth()->user()->username ?? (auth()->user()->name ?? (session('username') ?? 'system'));
        $nowStr = now()->format('Y-m-d H:i:s');

        $headerUpdate = [];
        foreach ($rfqArr as $col => $val) {
            $lc = strtolower($col);
            if ($lc === 'supplier_id') {
                $headerUpdate[$col] = $supplierId;
            } elseif ($lc === 'rfq_date') {
                $headerUpdate[$col] = $rfqDate;
            } elseif ($lc === 'status') {
                $headerUpdate[$col] = $status;
            } elseif (in_array($lc, ['note', 'notes', 'remark'], true)) {
                $headerUpdate[$col] = $note;
            } elseif (in_array($lc, ['updated_by', 'last_update_by'], true)) {
                $headerUpdate[$col] = $actor;
            } elseif (in_array($lc, ['updated_at', 'updated_date', 'last_update', 'modified_date'], true)) {
                $headerUpdate[$col] = $nowStr;
            }
        }

        $grandTotal = 0;

        // Hapus detail lama lalu insert ulang sesuai struktur rfq_dtl
        $deleted = DB::table('rfq_dtl')->where('RFQ_No', $rfqNo)->delete();
        if ($deleted === 0) {
            DB::table('rfq_dtl')->where('rfq_no', $rfqNo)->delete();
        }

        if (!empty($items) && is_array($items)) {
            foreach ($items as $row) {
                $prodCode = trim((string) ($row['product_code'] ?? ''));
                $prodDesc = trim((string) ($row['product_desc'] ?? ''));
                $spec     = trim((string) ($row['specification'] ?? ''));
                $qty      = (float) ($row['qty'] ?? 0);
                $unit     = trim((string) ($row['unit'] ?? 'PCS')) ?: 'PCS';
                $price    = (float) ($row['price'] ?? 0);

                if ($prodCode === '' && $prodDesc === '') {
                    continue;
                }

                $totalPrice = $qty * $price;
                $grandTotal += $totalPrice;

                DB::table('rfq_dtl')->insert([
                    'RFQ_No'        => $rfqNo,
                    'PR_No'         => $prNo,
                    'Supplier_ID'   => $supplierId,
                    'Product_Code'  => $prodCode,
                    'Product_Desc'  => $prodDesc,
                    'Specification' => $spec,
                    'Qty'           => $qty,
                    'Unit'          => $unit,
                    'Est_Price'     => $price,
                    'Total_Price'   => $totalPrice,
                    'Created_By'    => $actor,
                    'Created_Date'  => $nowStr,
                    'Updated_By'    => $actor,
                    'Updated_Date'  => $nowStr,
                ]);
            }
        }

        // Update Grand Total pada header RFQ jika kolomnya ada
        foreach ($rfqArr as $col => $val) {
            if (in_array(strtolower($col), ['grand_total', 'total_amount', 'total'], true)) {
                $headerUpdate[$col] = $grandTotal;
            }
        }

        if (!empty($headerUpdate)) {
            $updated = DB::table('rfq')->where('RFQ_No', $rfqNo)->update($headerUpdate);
            if ($updated === 0) {
                DB::table('rfq')->where('rfq_no', $rfqNo)->update($headerUpdate);
            }
        }

        DB::commit();

        return response()->json([
            'status'  => 'success',
            'message' => "RFQ {$rfqNo} berhasil diperbarui!"
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('updateRFQData error: ' . $e->getMessage());
        return response()->json([
            'status'  => 'error',
            'message' => 'Gagal menyimpan perubahan RFQ: ' . $e->getMessage()
        ], 500);
    }
}


// 1. Preview RFQ (Menampilkan PDF inline atau view HTML preview sebelum kirim/download)
    public function previewRFQ(Request $request)
    {
        $rfqNo = trim((string) ($request->query('rfq_no') ?? $request->query('rfq')));
        if ($rfqNo === '') {
            return '<div class="alert alert-warning m-3">Nomor RFQ tidak ditemukan.</div>';
        }

        $rfq = DB::table('rfq as r')
            ->leftJoin('supplier as s', DB::raw('r.Supplier_ID COLLATE utf8mb4_unicode_ci'), '=', DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('purchase_request as pr', DB::raw('r.PR_No COLLATE utf8mb4_unicode_ci'), '=', DB::raw('pr.PR_No COLLATE utf8mb4_unicode_ci'))
            ->select(
                'r.*',
                's.Company_Name', 's.Address as Supplier_Address', 's.Contact_Person', 's.Phone', 's.Email',
                'pr.Department', 'pr.PIC', 'pr.PIC_Phone', 'pr.PIC_Email', 'pr.PIC_Address as Site_Tujuan', 'pr.Note as PR_Note'
            )
            ->where('r.RFQ_No', $rfqNo)
            ->first();

        if (!$rfq) {
            return '<div class="alert alert-danger m-3">Data RFQ <strong>' . e($rfqNo) . '</strong> tidak ditemukan.</div>';
        }

        $items = DB::table('rfq_dtl')->where('RFQ_No', $rfqNo)->orderBy('ID', 'ASC')->get();
        if ($items->isEmpty()) {
            $items = DB::table('purchase_request_dtl')->where('PR_No', $rfq->PR_No)->orderBy('ID', 'ASC')->get();
        }

        // Jika dipanggil dengan ?mode=html, tampilkan panel preview interaktif (lengkap dengan info kontak vendor & tombol aksi)
        if ($request->query('mode') === 'html') {
            return view('humanresources.procurement.rfq-preview-panel', compact('rfq', 'items'));
        }

        // Default: langsung stream PDF inline untuk iframe Preview RFQ
        return $this->printRFQ($request->merge(['rfq' => $rfqNo]));
    }

    // 2. Kirim RFQ via WhatsApp otomatis sesuai data master supplier
    public function sendRFQWhatsApp(Request $request)
    {
        $rfqNo = trim((string) $request->input('rfq_no'));
        $rfq = DB::table('rfq as r')
            ->leftJoin('supplier as s', DB::raw('r.Supplier_ID COLLATE utf8mb4_unicode_ci'), '=', DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci'))
            ->select('r.*', 's.Company_Name', 's.Contact_Person', 's.Phone', 's.Email')
            ->where('r.RFQ_No', $rfqNo)
            ->first();

        if (!$rfq) {
            return response()->json(['status' => 'error', 'message' => 'Data RFQ tidak ditemukan.'], 404);
        }

        $rawPhone = preg_replace('/[^0-9]/', '', (string) ($rfq->Phone ?? ''));
        if ($rawPhone === '') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nomor WhatsApp/Telepon vendor (' . ($rfq->Company_Name ?? $rfq->Supplier_ID) . ') belum terdaftar di Master Supplier. Silakan gunakan tombol Download PDF.'
            ], 422);
        }

        if (str_starts_with($rawPhone, '0')) {
            $rawPhone = '62' . substr($rawPhone, 1);
        }

        $items = DB::table('rfq_dtl')->where('RFQ_No', $rfqNo)->orderBy('ID', 'ASC')->get();
        $itemLines = [];
        foreach ($items as $idx => $it) {
            $itemLines[] = ($idx + 1) . ". {$it->Product_Desc} ({$it->Qty} {$it->Unit})";
        }

        $pdfLink = url('rfq-print?rfq=' . urlencode($rfqNo));
        $vendorName = $rfq->Company_Name ?? $rfq->Supplier_ID;
        $picName    = !empty($rfq->Contact_Person) ? "Bpk/Ibu {$rfq->Contact_Person}" : "Tim {$vendorName}";

        $text = "Halo {$picName} ({$vendorName}),\n\n"
              . "Berikut kami kirimkan *Request For Quotation (RFQ)* dari *PT Green Energi Utama*:\n"
              . "• No. RFQ: *{$rfq->RFQ_No}*\n"
              . "• No. PR: {$rfq->PR_No}\n"
              . "• Tanggal: {$rfq->RFQ_Date}\n\n"
              . "*Daftar Kebutuhan:*\n" . implode("\n", $itemLines) . "\n\n"
              . "Dokumen RFQ PDF dapat diunduh melalui tautan berikut:\n{$pdfLink}\n\n"
              . "Mohon dapat mengirimkan penawaran harga terbaik Anda. Terima kasih.";

        // Update status DRAFT menjadi SENT saat dikirim
        if (strtoupper((string) $rfq->Status) === 'DRAFT') {
            DB::table('rfq')->where('RFQ_No', $rfqNo)->update(['Status' => 'SENT']);
        }

        $waUrl = 'https://wa.me/' . $rawPhone . '?text=' . urlencode($text);

        return response()->json([
            'status'  => 'success',
            'wa_url'  => $waUrl,
            'phone'   => $rawPhone,
            'message' => "Menyiapkan pengiriman WhatsApp ke {$vendorName} ({$rawPhone})..."
        ]);
    }

    // 3. Kirim RFQ via Email otomatis sesuai data master supplier (melampirkan PDF RFQ)
    public function sendRFQEmail(Request $request)
    {
        $rfqNo = trim((string) $request->input('rfq_no'));
        $rfq = DB::table('rfq as r')
            ->leftJoin('supplier as s', DB::raw('r.Supplier_ID COLLATE utf8mb4_unicode_ci'), '=', DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('purchase_request as pr', DB::raw('r.PR_No COLLATE utf8mb4_unicode_ci'), '=', DB::raw('pr.PR_No COLLATE utf8mb4_unicode_ci'))
            ->select(
                'r.*',
                's.Company_Name', 's.Address as Supplier_Address', 's.Contact_Person', 's.Phone', 's.Email',
                'pr.Department', 'pr.PIC', 'pr.PIC_Phone', 'pr.PIC_Email', 'pr.PIC_Address as Site_Tujuan', 'pr.Note as PR_Note'
            )
            ->where('r.RFQ_No', $rfqNo)
            ->first();

        if (!$rfq) {
            return response()->json(['status' => 'error', 'message' => 'Data RFQ tidak ditemukan.'], 404);
        }

        $vendorEmail = trim((string) ($rfq->Email ?? ''));
        if ($vendorEmail === '' || !filter_var($vendorEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Alamat Email vendor (' . ($rfq->Company_Name ?? $rfq->Supplier_ID) . ') belum terdaftar / tidak valid di Master Supplier. Silakan gunakan tombol Download PDF.'
            ], 422);
        }

        $items = DB::table('rfq_dtl')->where('RFQ_No', $rfqNo)->orderBy('ID', 'ASC')->get();
        if ($items->isEmpty()) {
            $items = DB::table('purchase_request_dtl')->where('PR_No', $rfq->PR_No)->orderBy('ID', 'ASC')->get();
        }

        try {
            // Generate file PDF RFQ sementara untuk dilampirkan di email
            $mpdfTempDir = storage_path('app/mpdf');
            if (!file_exists($mpdfTempDir)) {
                mkdir($mpdfTempDir, 0775, true);
            }

            $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'tempDir'       => $mpdfTempDir,
                'margin_left'   => 10,
                'margin_right'  => 10,
                'margin_top'    => 38,
                'margin_bottom' => 15,
                'margin_header' => 5,
                'margin_footer' => 5,
            ]);

            $kopPath = public_path('assets/images/surat/header_geu.jpg');
            if (file_exists($kopPath)) {
                $mpdf->SetHTMLHeader('
                    <div style="text-align: center; border-bottom: 1.5px solid #689f38; padding-bottom: 5px;">
                        <img src="' . $kopPath . '" style="width: 100%; max-height: 95px;">
                    </div>
                ');
            }

            $html = view('humanresources.procurement.rfq-print', compact('rfq', 'items'))->render();
            $mpdf->WriteHTML($html);
            $pdfBinary = $mpdf->Output('', 'S');
            $safeFilename = str_replace('/', '-', $rfq->RFQ_No) . '.pdf';

            $vendorName = $rfq->Company_Name ?? $rfq->Supplier_ID;
            $bodyText = "Kepada Yth. {$vendorName},\n\n"
                      . "Terlampir kami kirimkan dokumen Request For Quotation (RFQ) dengan nomor {$rfq->RFQ_No} (Ref PR: {$rfq->PR_No}).\n"
                      . "Mohon dapat mengirimkan penawaran harga terbaik beserta estimasi waktu pengiriman.\n\n"
                      . "Hormat kami,\nProcurement Team - PT Green Energi Utama";

            \Mail::raw($bodyText, function ($message) use ($vendorEmail, $vendorName, $rfq, $pdfBinary, $safeFilename) {
                $message->to($vendorEmail, $vendorName)
                    ->subject("Request For Quotation (RFQ) - {$rfq->RFQ_No}")
                    ->attachData($pdfBinary, $safeFilename, ['mime' => 'application/pdf']);
            });

            if (strtoupper((string) $rfq->Status) === 'DRAFT') {
                DB::table('rfq')->where('RFQ_No', $rfqNo)->update(['Status' => 'SENT']);
            }

            return response()->json([
                'status'  => 'success',
                'message' => "Dokumen RFQ {$rfqNo} berhasil dikirim via Email ke {$vendorName} ({$vendorEmail})!"
            ]);
        } catch (\Exception $e) {
            \Log::error('sendRFQEmail error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengirim email RFQ: ' . $e->getMessage()
            ], 500);
        }
    }


    // 2. Menyimpan Penawaran Harga Vendor (Quotation) ke rfq & rfq_dtl
    public function storeQuotation(Request $request) {
        $request->validate([
            'rfq_no'            => 'required|string',
            'quotation_no'      => 'required|string',
            'valid_until'       => 'nullable|date',
            'payment_terms'     => 'nullable|string',
            'tax_percent'       => 'nullable|numeric|min:0',
            'shipping_cost'     => 'nullable|numeric|min:0',
            'dtl_id'            => 'required|array',
            'quoted_price'      => 'required|array',
            'discount_percent'  => 'nullable|array',
            'vendor_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120'
        ]);

        $rfqNo    = $request->rfq_no;
        $username = session('username') ?? 'admin';

        $rfqHeader = DB::table('rfq')->where('RFQ_No', $rfqNo)->first();
        if (!$rfqHeader) {
            return response()->json(['status' => 'error', 'message' => 'Data RFQ tidak ditemukan!'], 404);
        }

        $attachmentPath = $rfqHeader->Vendor_Attachment;
        if ($request->hasFile('vendor_attachment')) {
            $file = $request->file('vendor_attachment');
            if ($file->isValid()) {
                $fileName = time() . '_QTN_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('uploads/vendor_quotations');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file->move($destinationPath, $fileName);
                $attachmentPath = 'uploads/vendor_quotations/' . $fileName;
            }
        }

        DB::transaction(function () use ($request, $rfqNo, $rfqHeader, $attachmentPath, $username) {
            $subtotalAmount = 0;

            foreach ($request->dtl_id as $idx => $dtlId) {
                $qty         = floatval($request->qty[$idx] ?? 1);
                $quotedPrice = floatval($request->quoted_price[$idx] ?? 0);
                $discPercent = floatval($request->discount_percent[$idx] ?? 0);
                $leadTime    = $request->lead_time_days[$idx] ?? null;
                $vendorNote  = $request->vendor_note[$idx] ?? null;

                $gross     = $qty * $quotedPrice;
                $discValue = $gross * ($discPercent / 100);
                $lineTotal = $gross - $discValue;

                $subtotalAmount += $lineTotal;

                DB::table('rfq_dtl')->where('ID', $dtlId)->update([
                    'Quoted_Price'     => $quotedPrice,
                    'Discount_Percent' => $discPercent,
                    'Total_Price'      => $lineTotal,
                    'Lead_Time_Days'   => $leadTime,
                    'Vendor_Note'      => $vendorNote,
                    'Updated_By'       => $username,
                    'Updated_Date'     => now(),
                ]);
            }

            $taxPercent   = floatval($request->tax_percent ?? 11);
            $taxAmount    = $subtotalAmount * ($taxPercent / 100);
            $shippingCost = floatval($request->shipping_cost ?? 0);
            $grandTotal   = $subtotalAmount + $taxAmount + $shippingCost;

            DB::table('rfq')->where('RFQ_No', $rfqNo)->update([
                'Quotation_No'      => $request->quotation_no,
                'Valid_Until'       => $request->valid_until,
                'Payment_Terms'     => $request->payment_terms,
                'Subtotal_Amount'   => $subtotalAmount,
                'Tax_Percent'       => $taxPercent,
                'Tax_Amount'        => $taxAmount,
                'Shipping_Cost'     => $shippingCost,
                'Grand_Total'       => $grandTotal,
                'Vendor_Attachment' => $attachmentPath,
                'Remarks'           => $request->remarks,
                'Status'            => 'RESPONDED',
                'Updated_By'        => $username,
            ]);

            DB::table('purchase_request')
                ->where('PR_No', $rfqHeader->PR_No)
                ->update([
                    'Processing_Status' => 'QUOTATION_RECEIVED',
                ]);
        });

        return response()->json([
            'status'  => 'success',
            'message' => "Penawaran Vendor untuk {$rfqNo} berhasil disimpan!"
        ]);
    }

    // Menampilkan Halaman Matriks Perbandingan Penawaran (Vendor Comparison Matrix)
    // public function vendorComparisonMatrix(Request $request) {
    //     $prNo = $request->query('pr_no');

    //     // 1. Ambil Header PR
    //     $prHeader = DB::table('purchase_request')->where('PR_No', $prNo)->first();
    //     if (!$prHeader) {
    //         return '<div class="alert alert-danger m-3">Data Purchase Request tidak ditemukan.</div>';
    //     }

    //     // 2. Ambil daftar RFQ & Vendor yang merespons PR ini
    //     $rfqVendors = DB::table('rfq as r')
    //         ->leftJoin('supplier as s', 'r.Supplier_ID', '=', 's.Supplier_ID')
    //         ->select('r.*', 's.Company_Name', 's.Contact_Person', 's.Phone')
    //         ->where('r.PR_No', $prNo)
    //         ->get();

    //     // 3. Ambil rincian item kebutuhan dari PR asli
    //     $prItems = DB::table('purchase_request_dtl')->where('PR_No', $prNo)->get();

    //     // 4. Ambil rincian penawaran harga dari rfq_dtl dikelompokkan per Supplier_ID
    //     $rfqDetailsRaw = DB::table('rfq_dtl')->where('PR_No', $prNo)->get();
    //     $rfqDetails = [];
    //     foreach ($rfqDetailsRaw as $detail) {
    //         $rfqDetails[$detail->Supplier_ID][$detail->Product_Code] = $detail;
    //     }

    //     return view('humanresources.procurement.vendor-comparison-matrix', compact('prHeader', 'rfqVendors', 'prItems', 'rfqDetails'));
    // }

    public function vendorComparisonMatrix(Request $request) {
        $prNo = $request->query('pr_no');

        // Jika pr_no dikirim sebagai RFQ_No, kita ambil PR_No-nya terlebih dahulu
        if (str_starts_with($prNo, 'RFQ/')) {
            $rfqHeader = DB::table('rfq')->where('RFQ_No', $prNo)->first();
            if ($rfqHeader) {
                $prNo = $rfqHeader->PR_No;
            }
        }

        // 1. Ambil Header PR
        $prHeader = DB::table('purchase_request')->where('PR_No', $prNo)->first();
        if (! $prHeader) {
            return '<div class="alert alert-danger m-3">Data Purchase Request tidak ditemukan untuk PR: ' . htmlspecialchars($prNo) . '</div>';
        }

        // 2. Ambil daftar Vendor UNIK yang merespons PR ini (GROUP BY Supplier_ID)
        $rfqVendors = DB::table('rfq as r')
            ->leftJoin('supplier as s', 'r.Supplier_ID', '=', 's.Supplier_ID')
            ->select('r.*', 's.Company_Name', 's.Contact_Person', 's.Phone')
            ->where('r.PR_No', $prNo)
            ->groupBy('r.Supplier_ID') // Mencegah duplikat kolom vendor yang sama
            ->get();

        // 3. Ambil rincian item kebutuhan dari PR asli
        $prItems = DB::table('purchase_request_dtl')->where('PR_No', $prNo)->get();

        // 4. Ambil rincian penawaran harga dari rfq_dtl dikelompokkan per Supplier_ID & Product_Code
        $rfqDetailsRaw = DB::table('rfq_dtl')->where('PR_No', $prNo)->get();
        $rfqDetails = [];
        foreach ($rfqDetailsRaw as $detail) {
            $rfqDetails[$detail->Supplier_ID][$detail->Product_Code] = $detail;
        }

        // return view('humanresources.procurement.vendor-comparison-matrix', compact('prHeader', 'rfqVendors', 'prItems', 'rfqDetails'));

        $versi = $request->query('v');
        if ($versi == '3') {
            $viewName = 'humanresources.procurement.vendor-comparison-matrix3';
        } elseif ($versi == '2') {
            $viewName = 'humanresources.procurement.vendor-comparison-matrix2';
        } else {
            $viewName = 'humanresources.procurement.vendor-comparison-matrix';
        }

        return view($viewName, compact('prHeader', 'rfqVendors', 'prItems', 'rfqDetails'));
    }

    // Cetak Dokumen RFQ Resmi untuk Dikirim ke Vendor
    // public function printRFQ(Request $request) {
    //     $rfqNo = $request->query('rfq');

    //     $rfq = DB::table('rfq as r')
    //         ->leftJoin('supplier as s', 'r.Supplier_ID', '=', 's.Supplier_ID')
    //         ->leftJoin('purchase_request as pr', 'r.PR_No', '=', 'pr.PR_No')
    //         ->select(
    //             'r.*', 
    //             's.Company_Name', 's.Address as Supplier_Address', 's.Contact_Person', 's.Phone', 's.Email',
    //             'pr.Department', 'pr.PIC', 'pr.PIC_Phone', 'pr.PIC_Email', 'pr.PIC_Address as Site_Tujuan', 'pr.Note as PR_Note'
    //         )
    //         ->where('r.RFQ_No', $rfqNo)
    //         ->first();

    //     if (!$rfq) {
    //         return abort(404, 'Dokumen RFQ tidak ditemukan.');
    //     }

    //     // Ambil item dari rfq_dtl (jika kosong, ambil dari purchase_request_dtl)
    //     $items = DB::table('rfq_dtl')->where('RFQ_No', $rfqNo)->orderBy('ID', 'ASC')->get();
    //     if ($items->isEmpty()) {
    //         $items = DB::table('purchase_request_dtl')->where('PR_No', $rfq->PR_No)->get();
    //     }

    //     return view('humanresources.procurement.rfq-print', compact('rfq', 'items'));
    // }

    public function printRFQ(Request $request) {
        $rfqNo = $request->query('rfq');

        $rfq = DB::table('rfq as r')
            ->leftJoin('supplier as s', 'r.Supplier_ID', '=', 's.Supplier_ID')
            ->leftJoin('purchase_request as pr', 'r.PR_No', '=', 'pr.PR_No')
            ->select(
                'r.*', 
                's.Company_Name', 's.Address as Supplier_Address', 's.Contact_Person', 's.Phone', 's.Email',
                'pr.Department', 'pr.PIC', 'pr.PIC_Phone', 'pr.PIC_Email', 'pr.PIC_Address as Site_Tujuan', 'pr.Note as PR_Note'
            )
            ->where('r.RFQ_No', $rfqNo)
            ->first();

        if (!$rfq) {
            return abort(404, 'Dokumen RFQ tidak ditemukan.');
        }

        // Ambil item dari rfq_dtl (jika kosong, ambil dari purchase_request_dtl)
        $items = DB::table('rfq_dtl')->where('RFQ_No', $rfqNo)->orderBy('ID', 'ASC')->get();
        if ($items->isEmpty()) {
            $items = DB::table('purchase_request_dtl')->where('PR_No', $rfq->PR_No)->get();
        }

        // Konfigurasi mPDF (Samakan margin top dengan printPR agar kop surat tidak menimpa isi)
        // Pastikan folder temporary mPDF tersedia di dalam storage Laravel
        $mpdfTempDir = storage_path('app/mpdf');
        if (!file_exists($mpdfTempDir)) {
            mkdir($mpdfTempDir, 0775, true);
        }

        // Konfigurasi mPDF dengan tempDir yang writable
        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'tempDir'       => $mpdfTempDir, // <-- Tambahkan baris ini!
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 38,
            'margin_bottom' => 15,
            'margin_header' => 5,
            'margin_footer' => 5,
        ]);

        // Set Kop Surat (Sesuaikan nama file gambar dengan yang ada di printPR Anda)
       // Arahkan langsung ke lokasi file header_geu.jpg di folder public/assets/images/surat/
        $kopPath = public_path('assets/images/surat/header_geu.jpg');
        if (file_exists($kopPath)) {
            $mpdf->SetHTMLHeader('
                <div style="text-align: center; border-bottom: 1.5px solid #689f38; padding-bottom: 5px;">
                    <img src="' . $kopPath . '" style="width: 100%; max-height: 95px;">
                </div>
            ');
        }

        $html = view('humanresources.procurement.rfq-print', compact('rfq', 'items'))->render();
        $mpdf->WriteHTML($html);

        // Output langsung tampil di browser (I = Inline)
        $fileName = str_replace('/', '-', $rfq->RFQ_No) . '.pdf';
        return response($mpdf->Output($fileName, 'I'), 200)
            ->header('Content-Type', 'application/pdf');
    }

    public function generatePO(Request $request)
    {
        $prNo       = $request->input('pr_no');
        $rfqNo      = $request->input('rfq_no');
        $supplierId = $request->input('supplier_id');

        DB::beginTransaction();
        try {
            // 1. Ambil data PR, RFQ vendor pemenang, dan data master Supplier
            $pr = DB::table('purchase_request')->where('PR_No', $prNo)->first();

            $rfqQuery = DB::table('rfq')->where('PR_No', $prNo)->where('Supplier_ID', $supplierId);
            if (!empty($rfqNo)) {
                $rfqQuery->where('RFQ_No', $rfqNo);
            }
            $rfq = $rfqQuery->first();

            if (!$pr || !$rfq) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data PR atau Penawaran Vendor tidak ditemukan.'
                ], 404);
            }

            $supplier = DB::table('supplier')->where('Supplier_ID', $supplierId)->first();

            // Cek jika PO untuk PR & Supplier ini sudah pernah diterbitkan (menggunakan kolom 'Supplier')
            $existingPo = DB::table('purchase_order')
                ->where('PR_No', $prNo)
                ->where('Supplier', $supplierId)
                ->first();

            if ($existingPo) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Purchase Order untuk PR & Vendor ini sudah pernah dibuat (' . $existingPo->PO_No . ').'
                ], 400);
            }

            // 2. Generate Nomor PO Otomatis (Format: PO/GEU/YYYYMM-XXXX)
            $prefix = 'PO/GEU/' . date('Ym') . '-';
            $lastPo = DB::table('purchase_order')
                ->where('PO_No', 'like', $prefix . '%')
                ->orderBy('ID', 'desc')
                ->first();

            $nextNum = $lastPo ? ((int) substr($lastPo->PO_No, -4)) + 1 : 1;
            $poNo    = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            // 3. Insert Header ke tabel purchase_order (sesuai struktur tabel asli)
            $remarkInfo = 'Ref RFQ: ' . $rfq->RFQ_No;
            if (!empty($rfq->Quotation_No)) {
                $remarkInfo .= ' | Ref Quotation: ' . $rfq->Quotation_No;
            }
            if (!empty($pr->Note)) {
                $remarkInfo .= ' | ' . $pr->Note;
            }

            DB::table('purchase_order')->insert([
                'Location'              => $pr->Location ?? 'HO',
                'Process_By'            => auth()->user()->Username ?? auth()->user()->name ?? 'admin',
                'Purchase_Type'         => $pr->Purchase_Type ?? null,
                'Purchase_Category'     => $pr->Category ?? null,
                'Purchase_Sub_Category' => $pr->Sub_Category ?? null,
                'PR_No'                 => $pr->PR_No,
                'PO_No'                 => $poNo,
                'PO_Date'               => date('Y-m-d'),
                'PO_Dest'               => $pr->Location ?? null,
                'Department'            => $pr->Department,
                'PIC_Address'           => $pr->PIC_Address ?? null,
                'PIC_Phone'             => $pr->PIC_Phone ?? null,
                'PIC'                   => $pr->PIC ?? null,
                'Supplier'              => $supplierId,
                'Supplier_PIC'          => $supplier->Contact_Person ?? null,
                'Term_Payment'          => $rfq->Payment_Terms ?? 'NET 30',
                'Pay_Term'              => $rfq->Payment_Terms ?? 'NET 30',
                'Subtotal'              => $rfq->Subtotal_Amount ?? 0,
                'Discount'              => 0,
                'PPN'                   => $rfq->Tax_Amount ?? 0,
                'PPH'                   => 0,
                'Delivery_Fee'          => $rfq->Shipping_Cost ?? 0,
                'Total'                 => $rfq->Grand_Total ?? 0,
                'Total_Paid'            => 0,
                'Status'                => 'OPEN',
                'Status_Receive'        => 'OPEN',
                'Currency'              => 'IDR',
                'Remark'                => $remarkInfo,
                'Date_Trx'              => now(),
                'User_Trx'              => auth()->user()->Username ?? auth()->user()->name ?? 'admin',
            ]);

            // 4. Insert Detail Item ke tabel purchase_order_dtl (sesuai struktur tabel asli)
            $rfqItems = DB::table('rfq_dtl')->where('RFQ_No', $rfq->RFQ_No)->get();
            foreach ($rfqItems as $item) {
                $unitPrice = $item->Quoted_Price ?? $item->Price ?? 0;
                $totalLine = $item->Total_Price ?? ($item->Qty * $unitPrice);

                DB::table('purchase_order_dtl')->insert([
                    'Location'      => $pr->Location ?? 'HO',
                    'PR_No'         => $pr->PR_No,
                    'PO_No'         => $poNo,
                    'Category'      => $pr->Category ?? null,
                    'Sub_Category'  => $pr->Sub_Category ?? null,
                    'Purchase_Type' => $pr->Purchase_Type ?? null,
                    'Product_Code'  => $item->Product_Code,
                    'Product_Desc'  => $item->Product_Desc,
                    'Specification' => $item->Specification ?? null,
                    'Qty'           => $item->Qty,
                    'Unit'          => $item->Unit,
                    'Price'         => $unitPrice,
                    'Discount'      => 0,
                    'Subtotal'      => $totalLine,
                    'PPN'           => 0,
                    'PPN_pct'       => 0,
                    'Total'         => $totalLine,
                    'Qty_Receive'   => 0,
                    'Qty_Remain'    => $item->Qty,
                    'Currency'      => 'IDR',
                ]);
            }

            // 5. Update Status RFQ Pemenang & Vendor Lainnya
            DB::table('rfq')->where('RFQ_No', $rfq->RFQ_No)->update(['Status' => 'AWARDED']);
            DB::table('rfq')
                ->where('PR_No', $prNo)
                ->where('RFQ_No', '!=', $rfq->RFQ_No)
                ->update(['Status' => 'CLOSED']);

            // 6. Update Status PR menjadi PO_CREATED
            DB::table('purchase_request')->where('PR_No', $prNo)->update(['Status' => 'PO_CREATED']);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Berhasil! Purchase Order ' . $poNo . ' telah diterbitkan.',
                'po_no'   => $poNo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat PO: ' . $e->getMessage()
            ], 500);
        }
    }

    // 1. Tampilkan Halaman Utama Outstanding PO
    public function outstandingPOIndex()
    {
        return view('humanresources.procurement.po-outstanding-list');
    }

    // 2. Endpoint XML DHTMLX Grid untuk Daftar PO Berstatus OPEN / PARTIAL
   public function xmlOutstandingPO(Request $request)
{
    $defaultStart = date('Y-m-01');
    $defaultEnd   = date('Y-m-d');

    $startDate = trim((string) $request->query('start_date', $defaultStart));
    $endDate   = trim((string) $request->query('end_date', $defaultEnd));

    if ($startDate === '') {
        $startDate = $defaultStart;
    }
    if ($endDate === '') {
        $endDate = $defaultEnd;
    }

    $pos = DB::table('purchase_order as po')
        ->leftJoin(
            'supplier as s',
            DB::raw('po.Supplier COLLATE utf8mb4_unicode_ci'),
            '=',
            DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci')
        )
        ->leftJoin(
            'purchase_request as pr',
            DB::raw('po.PR_No COLLATE utf8mb4_unicode_ci'),
            '=',
            DB::raw('pr.PR_No COLLATE utf8mb4_unicode_ci')
        )
        ->leftJoin(
            DB::raw('(SELECT PO_No, AVG(Price) as Avg_Unit_Price FROM purchase_order_dtl GROUP BY PO_No) as pod'),
            DB::raw('po.PO_No COLLATE utf8mb4_unicode_ci'),
            '=',
            DB::raw('pod.PO_No COLLATE utf8mb4_unicode_ci')
        )

        ->leftJoin(
            DB::raw('(SELECT PO_No, MAX(Incoming_Date) as Last_Incoming_Date FROM good_receive GROUP BY PO_No) as gr'),
            DB::raw('po.PO_No COLLATE utf8mb4_unicode_ci'),
            '=',
            DB::raw('gr.PO_No COLLATE utf8mb4_unicode_ci')
        )
        ->select(
            'po.ID',
            'po.PO_No',
            'po.PO_Date',
            'po.PR_No',
            'pr.PR_Date',
            'po.Supplier',
            's.Company_Name',
            'po.Department',
            'po.Term_Payment',
            'po.Total',
            'po.Status',
            'po.Status_Receive',
            'po.Date_Trx',
            'po.User_Trx',
            DB::raw('COALESCE(pod.Avg_Unit_Price, 0) as Avg_Unit_Price')
        )
        ->whereIn('po.Status', ['OPEN', 'APPROVED', 'DELIVERY', 'PARTIAL', 'CLOSED', 'RECEIVED'])
        ->whereDate('po.PO_Date', '>=', $startDate)
        ->whereDate('po.PO_Date', '<=', $endDate)
        ->orderBy('po.ID', 'desc')
        ->get();

    // Histori harga rata-rata per Supplier + Product_Code dari PO sebelumnya
    $histPrices = DB::table('purchase_order_dtl as d')
        ->join(
            'purchase_order as h',
            DB::raw('d.PO_No COLLATE utf8mb4_unicode_ci'),
            '=',
            DB::raw('h.PO_No COLLATE utf8mb4_unicode_ci')
        )
        ->select(
            'h.Supplier',
            'd.Product_Code',
            DB::raw('AVG(d.Price) as Hist_Price'),
            DB::raw('COUNT(*) as Trx_Count')
        )
        ->where('h.Status', '!=', 'CANCELLED')
        ->groupBy('h.Supplier', 'd.Product_Code')
        ->get()
        ->keyBy(function ($r) {
            return strtoupper(trim((string) $r->Supplier)) . '|' . strtoupper(trim((string) $r->Product_Code));
        });

    $poItemsMap = DB::table('purchase_order_dtl')
        ->select('PO_No', 'Product_Code', 'Price')
        ->whereIn('PO_No', $pos->pluck('PO_No')->all())
        ->get()
        ->groupBy('PO_No');

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<rows>';

    $no = 1;
    foreach ($pos as $row) {
        $vendorDisplay = $row->Company_Name
            ? $row->Supplier . ' - ' . $row->Company_Name
            : $row->Supplier;

        $stPo  = strtoupper(trim((string) ($row->Status ?? 'OPEN')));
        $stRec = strtoupper(trim((string) ($row->Status_Receive ?? 'OPEN')));
        $isDone = in_array($stPo, ['RECEIVED', 'DONE', 'CLOSED'], true) || in_array($stRec, ['RECEIVED', 'DONE'], true);
        $prosesLabel = $isDone ? 'DONE' : 'ON PROSES';


        // Lead time dari PO_Date ke tanggal penerimaan (Good Receive) / sekarang
        $poLeadtimeText = '-';
        if (!empty($row->PO_Date)) {
            try {
                $poStartDt = \Carbon\Carbon::parse($row->PO_Date)->startOfDay();
                $hasReceivedDt = !empty($row->Last_Incoming_Date);

                if ($hasReceivedDt) {
                    $poEndDt = \Carbon\Carbon::parse($row->Last_Incoming_Date)->startOfDay();
                    $poDays = max(0, $poStartDt->diffInDays($poEndDt));
                    $poLeadtimeText = $poDays . ' hr (Received)';
                } elseif ($isDone) {
                    $poEndDt = \Carbon\Carbon::parse($row->Date_Trx ?? now())->startOfDay();
                    $poDays = max(0, $poStartDt->diffInDays($poEndDt));
                    $poLeadtimeText = $poDays . ' hr (Done)';
                } else {
                    $poDays = max(0, $poStartDt->diffInDays(now()->startOfDay()));
                    $poLeadtimeText = $poDays . ' hr (PO->Now)';
                }
            } catch (\Exception $ex) {
                $poLeadtimeText = '-';
            }
        }

        // Lead time dari PR_Date ke tanggal selesai / sekarang
        $leadtimeText = '-';
        if (!empty($row->PR_Date)) {
            try {
                $startDt = \Carbon\Carbon::parse($row->PR_Date)->startOfDay();
                $endDt   = $isDone
                    ? \Carbon\Carbon::parse($row->Date_Trx ?? now())->startOfDay()
                    : now()->startOfDay();
                $days = max(0, $startDt->diffInDays($endDt));
                $leadtimeText = $days . ' hr (' . ($isDone ? 'Complete' : 'PR->Now') . ')';
            } catch (\Exception $ex) {
                $leadtimeText = '-';
            }
        }

        // Cek perbandingan harga terhadap histori vendor & produk
        $priceTrend = 'NORMAL';
        $itemsForPo = $poItemsMap->get($row->PO_No, collect());
        foreach ($itemsForPo as $it) {
            $key = strtoupper(trim((string) $row->Supplier)) . '|' . strtoupper(trim((string) $it->Product_Code));
            $hist = $histPrices->get($key);
            if ($hist && (int) $hist->Trx_Count > 1 && floatval($hist->Hist_Price) > 0) {
                $curPrice = floatval($it->Price ?? 0);
                $avgHist  = floatval($hist->Hist_Price);
                if ($curPrice > $avgHist) {
                    $priceTrend = 'UP';
                    break;
                } elseif ($curPrice < $avgHist && $priceTrend !== 'UP') {
                    $priceTrend = 'DOWN';
                }
            }
        }

        $avgFormatted = number_format($row->Avg_Unit_Price ?? 0, 0, ',', '.');
        if ($priceTrend === 'UP') {
            $histBadge = 'Naik vs Hist (Avg ' . $avgFormatted . ')';
            $rowStyle  = ' style="background-color:#fff8e1;"';
        } elseif ($priceTrend === 'DOWN') {
            $histBadge = 'Turun vs Hist (Avg ' . $avgFormatted . ')';
            $rowStyle  = ' style="background-color:#e8f5e9;"';
        } else {
            $histBadge = 'Avg ' . $avgFormatted;
            $rowStyle  = '';
        }

        $xml .= '<row id="' . htmlspecialchars($row->PO_No) . '"' . $rowStyle . '>';
        // $xml .= '<cell>' . $no++ . '</cell>';
        $xml .= '<cell>' . ($no++) . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->PO_No) . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->PO_Date ?? '-') . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->PR_No ?? '-') . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($vendorDisplay ?? '-') . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->Department ?? '-') . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->Term_Payment ?? '-') . '</cell>';
        $xml .= '<cell>' . number_format($row->Total ?? 0, 0, ',', '.') . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($histBadge) . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($leadtimeText) . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($poLeadtimeText) . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($prosesLabel) . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->Status ?? 'OPEN') . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->Status_Receive ?? 'OPEN') . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->User_Trx ?? '-') . '</cell>';
        $xml .= '</row>';
    }

    $xml .= '</rows>';

    return response($xml, 200)->header('Content-Type', 'text/xml');
}

// 3. Detail Item PO saat Baris Grid Di-Double Click
public function detailPO(Request $request)
{
    $poNo = $request->query('PO_No');

    $poHeader = DB::table('purchase_order as po')
        ->leftJoin(
            'supplier as s',
            DB::raw('po.Supplier COLLATE utf8mb4_unicode_ci'),
            '=',
            DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci')
        )
        ->leftJoin(
            'purchase_request as pr',
            DB::raw('po.PR_No COLLATE utf8mb4_unicode_ci'),
            '=',
            DB::raw('pr.PR_No COLLATE utf8mb4_unicode_ci')
        )
        ->select(
            'po.*',
            'pr.PR_Date',
            's.Company_Name',
            's.Address as Supplier_Address',
            's.Phone as Supplier_Phone'
        )
        ->where('po.PO_No', $poNo)
        ->first();

    if (!$poHeader) {
        return '<div class="alert alert-danger">Data Purchase Order tidak ditemukan.</div>';
    }

    $poItems = DB::table('purchase_order_dtl')
        ->where('PO_No', $poNo)
        ->orderBy('ID', 'ASC')
        ->get();

    return view('humanresources.procurement.po-detail', compact('poHeader', 'poItems'));
}

    // 4. Cetak PDF Purchase Order (mPDF + Kop Surat Base64)
    public function printPO(Request $request)
    {
        $poNo = $request->query('PO_No');

        $poHeader = DB::table('purchase_order as po')
            ->leftJoin(
                'supplier as s',
                DB::raw('po.Supplier COLLATE utf8mb4_unicode_ci'),
                '=',
                DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci')
            )
            ->leftJoin(
                'purchase_request as pr',
                DB::raw('po.PR_No COLLATE utf8mb4_unicode_ci'),
                '=',
                DB::raw('pr.PR_No COLLATE utf8mb4_unicode_ci')
            )
            ->select(
                'po.*',
                's.Company_Name',
                's.Address as Supplier_Address',
                's.Phone as Supplier_Phone',
                's.Email as Supplier_Email',
                'pr.Urgency as Urgency'
            )
            ->where('po.PO_No', $poNo)
            ->first();

        if (!$poHeader) {
            abort(404, 'Data Purchase Order tidak ditemukan.');
        }

        $poItems = DB::table('purchase_order_dtl')
            ->where('PO_No', $poNo)
            ->orderBy('ID', 'ASC')
            ->get();

        $mpdfTempDir = storage_path('app/mpdf');
        if (!file_exists($mpdfTempDir)) {
            mkdir($mpdfTempDir, 0775, true);
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'tempDir'       => $mpdfTempDir,
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 42,
            'margin_bottom' => 15,
            'margin_header' => 5,
            'margin_footer' => 5,
        ]);

        $kopPath = public_path('assets/images/surat/header_geu.jpg');
        if (file_exists($kopPath)) {
            $ext = pathinfo($kopPath, PATHINFO_EXTENSION);
            $base64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($kopPath));

            $mpdf->SetHTMLHeader('
                <div style="text-align: center; border-bottom: 1.5px solid #689f38; padding-bottom: 5px;">
                    <img src="' . $base64 . '" style="width: 100%; max-height: 95px;">
                </div>
            ');
        }

        $html = view('humanresources.procurement.po-print-pdf', compact('poHeader', 'poItems'))->render();
        $mpdf->WriteHTML($html);

        $safeFilename = str_replace('/', '-', $poHeader->PO_No) . '.pdf';
        return response($mpdf->Output($safeFilename, 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $safeFilename . '"');
    }
    // Generate Split PO (1 PR menjadi Banyak PO berdasarkan item yang diceklis per vendor)
    public function generateSplitPO(Request $request)
    {
        $prNo         = $request->input('pr_no');
        $rfqNo        = $request->input('rfq_no');
        $supplierId   = $request->input('supplier_id');
        $productCodes = $request->input('product_codes', []); // Array Product_Code yang dicentang

        if (empty($productCodes) || !is_array($productCodes)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Silakan centang minimal 1 item produk untuk vendor ini!'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $pr = DB::table('purchase_request')->where('PR_No', $prNo)->first();
            $rfqQuery = DB::table('rfq')->where('PR_No', $prNo)->where('Supplier_ID', $supplierId);
            if (!empty($rfqNo)) {
                $rfqQuery->where('RFQ_No', $rfqNo);
            }
            $rfq = $rfqQuery->first();

            if (!$pr || !$rfq) {
                return response()->json(['status' => 'error', 'message' => 'Data PR atau RFQ Vendor tidak ditemukan.'], 404);
            }

            // Cek apakah ada item yang dicentang ternyata sudah dibuatkan PO AKTIF (abaikan PO yang CANCELLED)
            $alreadyOrdered = DB::table('purchase_order_dtl as pod')
                ->join('purchase_order as po', 'pod.PO_No', '=', 'po.PO_No')
                ->where('pod.PR_No', $prNo)
                ->where('po.Status', '!=', 'CANCELLED')
                ->whereIn('pod.Product_Code', $productCodes)
                ->pluck('pod.Product_Code')
                ->toArray();

            if (!empty($alreadyOrdered)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Item berikut sudah pernah diterbitkan PO aktifnya: ' . implode(', ', $alreadyOrdered)
                ], 400);
            }

            $supplier = DB::table('supplier')->where('Supplier_ID', $supplierId)->first();

            // Ambil detail penawaran hanya untuk item yang dicentang
            $selectedItems = DB::table('rfq_dtl')
                ->where('RFQ_No', $rfq->RFQ_No)
                ->whereIn('Product_Code', $productCodes)
                ->get();

            if ($selectedItems->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'Rincian harga item tidak ditemukan.'], 404);
            }

            // Hitung Subtotal, PPN, dan Grand Total khusus item yang dicentang
            $subtotal = 0;
            foreach ($selectedItems as $item) {
                $unitPrice = $item->Quoted_Price ?? $item->Price ?? 0;
                $lineTotal = ($item->Total_Price > 0) ? $item->Total_Price : ($item->Qty * $unitPrice);
                $subtotal += $lineTotal;
            }

            $taxPercent  = floatval($rfq->Tax_Percent ?? 11);
            $ppnAmount   = $subtotal * ($taxPercent / 100);
            // Ongkir diambil penuh hanya jika belum pernah dibebankan pada PO vendor ini di PR yang sama
            $hasPreviousPoSameVendor = DB::table('purchase_order')
                ->where('PR_No', $prNo)
                ->where('Supplier', $supplierId)
                ->exists();
            $deliveryFee = $hasPreviousPoSameVendor ? 0 : floatval($rfq->Shipping_Cost ?? 0);
            $grandTotal  = $subtotal + $ppnAmount + $deliveryFee;

            // Generate Nomor PO Otomatis (Format: PO/GEU/YYYYMM-XXXX)
            $prefix = 'PO/GEU/' . date('Ym') . '-';
            $lastPo = DB::table('purchase_order')
                ->where('PO_No', 'like', $prefix . '%')
                ->orderBy('ID', 'desc')
                ->first();
            $nextNum = $lastPo ? ((int) substr($lastPo->PO_No, -4)) + 1 : 1;
            $poNo    = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $username = session('username') ?? (auth()->user()->Username ?? auth()->user()->name ?? 'admin');

            $remarkInfo = 'Split PO | Ref RFQ: ' . $rfq->RFQ_No;
            if (!empty($rfq->Quotation_No)) {
                $remarkInfo .= ' | Ref QTN: ' . $rfq->Quotation_No;
            }

            // 1. Insert Header PO
            DB::table('purchase_order')->insert([
                'Location'              => $pr->Location ?? 'HO',
                'Process_By'            => $username,
                'Purchase_Type'         => $pr->Purchase_Type ?? null,
                'Purchase_Category'     => $pr->Category ?? null,
                'Purchase_Sub_Category' => $pr->Sub_Category ?? null,
                'PR_No'                 => $pr->PR_No,
                'PO_No'                 => $poNo,
                'PO_Date'               => date('Y-m-d'),
                'PO_Dest'               => $pr->Location ?? null,
                'Department'            => $pr->Department,
                'PIC_Address'           => $pr->PIC_Address ?? null,
                'PIC_Phone'             => $pr->PIC_Phone ?? null,
                'PIC'                   => $pr->PIC ?? null,
                'Supplier'              => $supplierId,
                'Supplier_PIC'          => $supplier->Contact_Person ?? null,
                'Term_Payment'          => $rfq->Payment_Terms ?? 'NET 30',
                'Pay_Term'              => $rfq->Payment_Terms ?? 'NET 30',
                'Subtotal'              => $subtotal,
                'Discount'              => 0,
                'PPN'                   => $ppnAmount,
                'PPH'                   => 0,
                'Delivery_Fee'          => $deliveryFee,
                'Total'                 => $grandTotal,
                'Total_Paid'            => 0,
                'Status'                => 'OPEN',
                'Status_Receive'        => 'OPEN',
                'Currency'              => 'IDR',
                'Remark'                => $remarkInfo,
                'Date_Trx'              => now(),
                'User_Trx'              => $username,
            ]);

            // 2. Insert Detail PO khusus item yang dicentang
            foreach ($selectedItems as $item) {
                $unitPrice = $item->Quoted_Price ?? $item->Price ?? 0;
                $lineTotal = ($item->Total_Price > 0) ? $item->Total_Price : ($item->Qty * $unitPrice);

                DB::table('purchase_order_dtl')->insert([
                    'Location'      => $pr->Location ?? 'HO',
                    'PR_No'         => $pr->PR_No,
                    'PO_No'         => $poNo,
                    'Category'      => $pr->Category ?? null,
                    'Sub_Category'  => $pr->Sub_Category ?? null,
                    'Purchase_Type' => $pr->Purchase_Type ?? null,
                    'Product_Code'  => $item->Product_Code,
                    'Product_Desc'  => $item->Product_Desc,
                    'Specification' => $item->Specification ?? null,
                    'Qty'           => $item->Qty,
                    'Unit'          => $item->Unit,
                    'Price'         => $unitPrice,
                    'Discount'      => 0,
                    'Subtotal'      => $lineTotal,
                    'PPN'           => 0,
                    'PPN_pct'       => 0,
                    'Total'         => $lineTotal,
                    'Qty_Receive'   => 0,
                    'Qty_Remain'    => $item->Qty,
                    'Currency'      => 'IDR',
                ]);
            }

            // 3. Tandai RFQ vendor ini sebagai AWARDED (Pemenang parsial/penuh)
            DB::table('rfq')->where('RFQ_No', $rfq->RFQ_No)->update(['Status' => 'AWARDED']);

            // 4. Cek apakah masih ada sisa item PR yang belum dibuatkan PO
            $totalPrItems = DB::table('purchase_request_dtl')->where('PR_No', $prNo)->count();
            $totalOrderedItems = DB::table('purchase_order_dtl')->where('PR_No', $prNo)->distinct('Product_Code')->count('Product_Code');

            $newPrStatus = ($totalOrderedItems >= $totalPrItems) ? 'PO_CREATED' : 'PARTIAL_PO';
            DB::table('purchase_request')->where('PR_No', $prNo)->update(['Status' => $newPrStatus]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Split PO {$poNo} berhasil diterbitkan untuk {$supplier->Company_Name} (" . count($productCodes) . " item)!",
                'po_no'   => $poNo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat Split PO: ' . $e->getMessage()
            ], 500);
        }
    }


    // Cancel / Batalkan PO dan kembalikan item agar bisa dipilih ulang di Matrix v3
    public function cancelPO(Request $request)
    {
        $poNo   = $request->input('po_no');
        $reason = trim($request->input('cancel_reason', 'Dibatalkan oleh user'));

        DB::beginTransaction();
        try {
            $po = DB::table('purchase_order')->where('PO_No', $poNo)->first();
            if (!$po) {
                return response()->json(['status' => 'error', 'message' => 'Data PO tidak ditemukan.'], 404);
            }

            if (strtoupper($po->Status) === 'CANCELLED') {
                return response()->json(['status' => 'error', 'message' => 'PO ini sudah berstatus CANCELLED.'], 400);
            }

            // 1. Cek apakah sudah ada barang yang diterima (Goods Received)
            $hasReceived = DB::table('purchase_order_dtl')
                ->where('PO_No', $poNo)
                ->where('Qty_Receive', '>', 0)
                ->exists();

            if ($hasReceived) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'PO tidak dapat dibatalkan karena sudah ada proses penerimaan barang (Goods Received)!'
                ], 400);
            }

            $username = session('username') ?? (auth()->user()->Username ?? auth()->user()->name ?? 'admin');
            $cancelNote = " [CANCELLED by {$username} (" . date('d-m-Y H:i') . "): {$reason}]";

            // 2. Ubah status PO menjadi CANCELLED (riwayat nomor PO tetap tersimpan untuk audit)
            DB::table('purchase_order')->where('PO_No', $poNo)->update([
                'Status'         => 'CANCELLED',
                'Status_Receive' => 'CANCELLED',
                'Remark'         => ($po->Remark ?? '') . $cancelNote,
            ]);

            // 3. Cek sisa PO aktif (selain CANCELLED) untuk PR dan Supplier yang sama
            $prNo = $po->PR_No;
            $hasOtherActivePoSameVendor = DB::table('purchase_order')
                ->where('PR_No', $prNo)
                ->where('Supplier', $po->Supplier)
                ->where('Status', '!=', 'CANCELLED')
                ->exists();

            // Jika vendor ini tidak punya PO aktif lain pada PR ini, kembalikan status RFQ-nya ke RESPONDED
            if (!$hasOtherActivePoSameVendor) {
                DB::table('rfq')
                    ->where('PR_No', $prNo)
                    ->where('Supplier_ID', $po->Supplier)
                    ->update(['Status' => 'RESPONDED']);
            }

            // 4. Hitung ulang sisa item yang masih terikat PO aktif untuk memperbarui status PR
            $totalPrItems = DB::table('purchase_request_dtl')->where('PR_No', $prNo)->count();
            $activeOrderedItems = DB::table('purchase_order_dtl as pod')
                ->join('purchase_order as po', 'pod.PO_No', '=', 'po.PO_No')
                ->where('pod.PR_No', $prNo)
                ->where('po.Status', '!=', 'CANCELLED')
                ->distinct('pod.Product_Code')
                ->count('pod.Product_Code');

            if ($activeOrderedItems === 0) {
                $newPrStatus = 'APPROVED';
            } elseif ($activeOrderedItems < $totalPrItems) {
                $newPrStatus = 'PARTIAL_PO';
            } else {
                $newPrStatus = 'PO_CREATED';
            }

            DB::table('purchase_request')->where('PR_No', $prNo)->update(['Status' => $newPrStatus]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "PO {$poNo} berhasil dibatalkan! Item-item tersebut kini dapat dipilih ulang di Matrix."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membatalkan PO: ' . $e->getMessage()
            ], 500);
        }
    }


    // 1. Proses APPROVED PO
    public function approvePO(Request $request)
    {
        $poNo = $request->input('po_no');
        $note = trim($request->input('note', ''));
        $username = session('username') ?? (auth()->user()->Username ?? auth()->user()->name ?? 'admin');

        $po = DB::table('purchase_order')->where('PO_No', $poNo)->first();
        if (!$po) {
            return response()->json(['status' => 'error', 'message' => 'Data PO tidak ditemukan.'], 404);
        }

        $remark = $po->Remark ?? '';
        if (!empty($note)) {
            $remark .= " | Approved by {$username}: {$note}";
        }

        DB::table('purchase_order')->where('PO_No', $poNo)->update([
            'Status'   => 'APPROVED',
            'Remark'   => $remark,
            'User_Trx' => $username,
            'Date_Trx' => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Purchase Order {$poNo} berhasil di-APPROVED!"
        ]);
    }

    // 2. Proses DELIVERY PO (Barang dalam pengiriman vendor)
    public function deliveryPO(Request $request)
    {
        $poNo     = $request->input('po_no');
        $suratJln = trim($request->input('delivery_note', ''));
        $username = session('username') ?? (auth()->user()->Username ?? auth()->user()->name ?? 'admin');

        $po = DB::table('purchase_order')->where('PO_No', $poNo)->first();
        if (!$po) {
            return response()->json(['status' => 'error', 'message' => 'Data PO tidak ditemukan.'], 404);
        }

        $remark = $po->Remark ?? '';
        $infoSj = !empty($suratJln) ? " (No. SJ/Resi: {$suratJln})" : '';
        $remark .= " | Delivery by {$username} (" . date('d-m-Y') . "){$infoSj}";

        DB::table('purchase_order')->where('PO_No', $poNo)->update([
            'Status'         => 'DELIVERY',
            'Status_Receive' => 'ON_DELIVERY',
            'Remark'         => $remark,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Status {$poNo} berhasil diubah menjadi DELIVERY!"
        ]);
    }

   // 3. Proses RECEIVED PO (Penerimaan Barang Parsial / Penuh + Catat ke good_receive & good_receive_dtl)
    public function receivePO(Request $request)
    {
        $request->validate([
            'receive_note'  => 'nullable|string|max:1000',
            'receive_proof' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $poNo         = $request->input('po_no');
        $receiveQty   = $request->input('receive_qty', []); // [dtl_id => qty_masuk]
        $sjNo         = trim($request->input('surat_jalan', ''));
        $receiveNote  = trim($request->input('receive_note', ''));
        $username     = session('username') ?? (auth()->user()->Username ?? auth()->user()->name ?? 'admin');

        DB::beginTransaction();
        try {
            $po = DB::table('purchase_order')->where('PO_No', $poNo)->first();
            if (!$po) {
                return response()->json(['status' => 'error', 'message' => 'Data PO tidak ditemukan.'], 404);
            }

            // 1. Kumpulkan & validasi item yang akan diterima dulu sebelum insert header GR
            $itemsToReceive = [];
            $totalReceivedThisTime = 0;

            foreach ($receiveQty as $dtlId => $qtyIn) {
                $qtyIn = floatval($qtyIn);
                if ($qtyIn <= 0) continue;

                $item = DB::table('purchase_order_dtl')->where('ID', $dtlId)->where('PO_No', $poNo)->first();
                if (!$item) continue;

                $currentReceive = floatval($item->Qty_Receive ?? 0);
                $orderQty       = floatval($item->Qty ?? 0);
                $maxAllowed     = max(0, $orderQty - $currentReceive);

                if ($qtyIn > $maxAllowed) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => 'error',
                        'message' => "Qty terima untuk {$item->Product_Desc} melebihi sisa order ({$maxAllowed})!"
                    ], 422);
                }

                $itemsToReceive[] = [
                    'item'           => $item,
                    'qtyIn'          => $qtyIn,
                    'currentReceive' => $currentReceive,
                    'orderQty'       => $orderQty,
                ];
                $totalReceivedThisTime += $qtyIn;
            }

            if ($totalReceivedThisTime <= 0 || empty($itemsToReceive)) {
                DB::rollBack();
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Masukkan minimal 1 item dengan Qty Diterima lebih dari 0!'
                ], 422);
            }

            
            // Upload bukti gambar penerima barang jika ada (Max 5 MB)
            $proofPath = null;
            if ($request->hasFile('receive_proof') && $request->file('receive_proof')->isValid()) {
                $file = $request->file('receive_proof');
                $fileName = time() . '_GR_' . uniqid() . '.' . strtolower($file->getClientOriginalExtension());
                $destinationPath = public_path('uploads/goods_received');

                if (!file_exists($destinationPath)) {
                    if (!mkdir($destinationPath, 0777, true) && !is_dir($destinationPath)) {
                        throw new \RuntimeException('Gagal membuat folder uploads/goods_received di server.');
                    }
                    @chmod($destinationPath, 0777);
                }

                if (!$file->move($destinationPath, $fileName)) {
                    throw new \RuntimeException('Gagal mengunggah file bukti penerima barang ke server.');
                }

                $proofPath = 'uploads/goods_received/' . $fileName;
            }

            // 2. Generate Nomor GR Otomatis (Format: GR/GEU/YYYYMM-XXXX)
            $prefix = 'GR/GEU/' . date('Ym') . '-';
            $lastGr = DB::table('good_receive')
                ->where('GR_No', 'like', $prefix . '%')
                ->orderBy('ID', 'desc')
                ->first();

            $nextGrNum    = $lastGr ? ((int) substr($lastGr->GR_No, -4)) + 1 : 1;
            $grNo         = $prefix . str_pad($nextGrNum, 4, '0', STR_PAD_LEFT);
            $incomingDate = date('Y-m-d');
            $nowTimestamp = now();
            $location     = $po->Location ?? 'HO';

            $grRemarkParts = [];
            if ($sjNo !== '') {
                $grRemarkParts[] = "SJ: {$sjNo}";
            } else {
                $grRemarkParts[] = "Penerimaan barang PO {$po->PO_No}";
            }
            if ($receiveNote !== '') {
                $grRemarkParts[] = "Note: {$receiveNote}";
            }
            if ($proofPath !== null) {
                $grRemarkParts[] = "Bukti: {$proofPath}";
            }

            // 3. Insert Header ke tabel good_receive
            DB::table('good_receive')->insert([
                'GR_No'                 => $grNo,
                'PR_No'                 => $po->PR_No,
                'PO_No'                 => $po->PO_No,
                'PO_No_KSPS'            => null,
                'Incoming_Date'         => $incomingDate,
                'Purchase_Category'     => $po->Purchase_Category,
                'Purchase_Sub_Category' => $po->Purchase_Sub_Category,
                'Purchase_Type'         => $po->Purchase_Type,
                'Reference'             => $sjNo ?: null,
                'Remark'                => implode(' | ', $grRemarkParts),
                'Location'              => $location,
                'Status'                => 'OPEN',
                'Date_Created'          => $nowTimestamp,
                'Created_By'            => $username,
                'Apply_To'              => 'PO',
            ]);

            // 4. Update purchase_order_dtl & insert detail ke good_receive_dtl
            foreach ($itemsToReceive as $row) {
                $item       = $row['item'];
                $qtyIn      = $row['qtyIn'];
                $orderQty   = $row['orderQty'];
                $newReceive = $row['currentReceive'] + $qtyIn;
                $newRemain  = max(0, $orderQty - $newReceive);

                DB::table('purchase_order_dtl')->where('ID', $item->ID)->update([
                    'Qty_Receive' => $newReceive,
                    'Qty_Remain'  => $newRemain,
                ]);

                $unitPrice    = floatval($item->Price ?? 0);
                $valuePo      = floatval($item->Total ?? ($orderQty * $unitPrice));
                $valueReceive = $qtyIn * $unitPrice;

                DB::table('good_receive_dtl')->insert([
                    'Category'      => $item->Category ?? $po->Category,
                    'GR_No'         => $grNo,
                    'PO_No'         => $po->PO_No,
                    'Product_Code'  => $item->Product_Code,
                    'Barcode'       => null,
                    'SN'            => null,
                    'Qty_PO'        => $orderQty,
                    'Qty_Receive'   => $qtyIn,
                    'Value_PO'      => $valuePo,
                    'Value_Receive' => $valueReceive,
                    'Location'      => $location,
                    'Date_Created'  => $nowTimestamp,
                    'Created_By'    => $username,
                    'Incoming_Date' => $incomingDate,
                ]);
            }

            // 5. Cek total sisa seluruh item di PO ini untuk menentukan status PARTIAL / RECEIVED
            $remainingTotal = DB::table('purchase_order_dtl')
                ->where('PO_No', $poNo)
                ->sum('Qty_Remain');

            $newStatus        = ($remainingTotal <= 0) ? 'RECEIVED' : 'PARTIAL';
            $newStatusReceive = ($remainingTotal <= 0) ? 'RECEIVED' : 'PARTIAL';

            $remark = $po->Remark ?? '';
            $extraInfo = [];
            if ($sjNo !== '') {
                $extraInfo[] = "SJ: {$sjNo}";
            }
            if ($receiveNote !== '') {
                $extraInfo[] = "Note: {$receiveNote}";
            }
            if ($proofPath !== null) {
                $extraInfo[] = "Bukti: {$proofPath}";
            }
            $extraStr = !empty($extraInfo) ? ' [' . implode(' | ', $extraInfo) . ']' : '';
            $remark .= " | GR: {$grNo} ({$newStatus}) by {$username} (" . date('d-m-Y H:i') . "){$extraStr}";

            // Update status header good_receive
            DB::table('good_receive')->where('GR_No', $grNo)->update([
                'Status' => $newStatus,
            ]);

            // Update status header purchase_order
            DB::table('purchase_order')->where('PO_No', $poNo)->update([
                'Status'         => $newStatus,
                'Status_Receive' => $newStatusReceive,
                'Remark'         => $remark,
            ]);

            DB::commit();

            return response()->json([
                'status'    => 'success',
                'gr_no'     => $grNo,
                'po_status' => $newStatus,
                'message'   => ($remainingTotal <= 0)
                    ? "Seluruh barang untuk {$poNo} telah diterima ({$grNo} — STATUS: RECEIVED)!"
                    : "Penerimaan parsial untuk {$poNo} berhasil disimpan ({$grNo} — STATUS: PARTIAL)!"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses penerimaan barang: ' . $e->getMessage()
            ], 500);
        }
    }

    // 1. Tampilkan Halaman Utama Goods Received List
    public function goodsReceivedIndex()
    {
        return view('humanresources.procurement.goods-received-list');
    }

    // 2. Endpoint XML DHTMLX Grid untuk Daftar Goods Received (good_receive)
    public function xmlGoodsReceivedList()
    {
        $grList = DB::table('good_receive as gr')
            ->leftJoin(
                'purchase_order as po',
                DB::raw('gr.PO_No COLLATE utf8mb4_unicode_ci'),
                '=',
                DB::raw('po.PO_No COLLATE utf8mb4_unicode_ci')
            )
            ->leftJoin(
                'supplier as s',
                DB::raw('po.Supplier COLLATE utf8mb4_unicode_ci'),
                '=',
                DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci')
            )
            ->leftJoin(
                DB::raw('(SELECT GR_No, SUM(Qty_Receive) as Total_Qty_Receive, SUM(Value_Receive) as Total_Value_Receive FROM good_receive_dtl GROUP BY GR_No) as grd'),
                DB::raw('gr.GR_No COLLATE utf8mb4_unicode_ci'),
                '=',
                DB::raw('grd.GR_No COLLATE utf8mb4_unicode_ci')
            )
            ->select(
                'gr.ID',
                'gr.GR_No',
                'gr.PO_No',
                'gr.PR_No',
                'gr.Incoming_Date',
                'gr.Reference',
                'gr.Location',
                'gr.Status',
                'gr.Created_By',
                'gr.Date_Created',
                'po.Supplier',
                's.Company_Name',
                DB::raw('COALESCE(grd.Total_Qty_Receive, 0) as Total_Qty_Receive'),
                DB::raw('COALESCE(grd.Total_Value_Receive, 0) as Total_Value_Receive')
            )
            ->orderBy('gr.ID', 'desc')
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rows>';

        $no = 1;
        foreach ($grList as $row) {
            $vendorDisplay = $row->Company_Name
                ? ($row->Supplier . ' - ' . $row->Company_Name)
                : ($row->Supplier ?? '-');

            $xml .= '<row id="' . htmlspecialchars($row->GR_No) . '">';
            $xml .= '<cell>' . $no++ . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->GR_No) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Incoming_Date ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->PO_No ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->PR_No ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($vendorDisplay) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Reference ?? '-') . '</cell>';
            $xml .= '<cell>' . number_format($row->Total_Qty_Receive ?? 0, 0, ',', '.') . '</cell>';
            $xml .= '<cell>' . number_format($row->Total_Value_Receive ?? 0, 0, ',', '.') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Status ?? 'OPEN') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Created_By ?? '-') . '</cell>';
            $xml .= '</row>';
        }

        $xml .= '</rows>';

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    // 3. Detail Item per GR_No saat Baris Grid Diklik / Di-Double Click
    public function detailGoodsReceived(Request $request)
    {
        $grNo = $request->query('GR_No');

        $grHeader = DB::table('good_receive as gr')
            ->leftJoin(
                'purchase_order as po',
                DB::raw('gr.PO_No COLLATE utf8mb4_unicode_ci'),
                '=',
                DB::raw('po.PO_No COLLATE utf8mb4_unicode_ci')
            )
            ->leftJoin(
                'supplier as s',
                DB::raw('po.Supplier COLLATE utf8mb4_unicode_ci'),
                '=',
                DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci')
            )
            ->select(
                'gr.*',
                'po.Supplier',
                'po.Department',
                's.Company_Name'
            )
            ->where('gr.GR_No', $grNo)
            ->first();

        if (!$grHeader) {
            return '<div class="alert alert-danger mt-2">Data Goods Received tidak ditemukan.</div>';
        }

        $grItems = DB::table('good_receive_dtl as grd')
            ->leftJoin(
                'product_code as pc',
                DB::raw('grd.Product_Code COLLATE utf8mb4_unicode_ci'),
                '=',
                DB::raw('pc.Product_Code COLLATE utf8mb4_unicode_ci')
            )
            ->select(
                'grd.*',
                'pc.Description as Product_Desc',
                'pc.Unit_Name as Unit'
            )
            ->where('grd.GR_No', $grNo)
            ->orderBy('grd.ID', 'ASC')
            ->get();

        return view('humanresources.procurement.goods-received-detail', compact('grHeader', 'grItems'));
    }


  public function mobileCreatePR()
{
    $lastPr = DB::table('purchase_request')->orderBy('ID', 'DESC')->first();
    $nextId = $lastPr ? ($lastPr->ID + 1) : 1;
    $generated_pr_no = 'PR/GEU-HO/' . date('Ym') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

    $materials = DB::table('product_code')
        ->select('Product_Code', 'Description', 'Specification', 'Unit_Name')
        ->orderBy('Description', 'ASC')
        ->get();

    $departments = DB::table('department')
        ->orderBy('Department', 'ASC')
        ->get();

    $currentUsername = session('username') ?? (auth()->user()->username ?? 'admin');
    $userData = DB::table('users')->where('username', $currentUsername)->first();
    $userDepartment = $userData->Department ?? '';

    return view('humanresources.procurement.mobile-pr-create', compact(
        'generated_pr_no',
        'materials',
        'departments',
        'userDepartment'
    ));
}


public function mobileEditPRList()
{
    $prs = DB::table('purchase_request')
        ->orderBy('ID', 'DESC')
        ->limit(100)
        ->get();

    return view('humanresources.procurement.mobile-pr-edit-list', compact('prs'));
}

public function mobileEditPR($id)
{
    $pr = DB::table('purchase_request')->where('ID', $id)->first();
    if (!$pr) {
        abort(404);
    }

    $prNo = $pr->PR_No ?? ($pr->pr_no ?? '');
    $items = DB::table('purchase_request_dtl')
        ->where('PR_No', $prNo)
        ->get();

    $materials = DB::table('product_code')
        ->select('Product_Code', 'Description', 'Specification', 'Unit_Name')
        ->orderBy('Description', 'ASC')
        ->get();

    $departments = DB::table('department')
        ->orderBy('Department', 'ASC')
        ->get();

    $user = auth()->user();

    return view('humanresources.procurement.mobile-pr-edit', compact(
        'pr',
        'items',
        'materials',
        'departments',
        'user'
    ));
}

public function mobileApprovalPR()
{
    $username = session('username') ?? (Auth::user()->username ?? 'admin');
    $userObj  = Auth::user() ?? DB::table('users')->where('username', $username)->first();

    $prApprovals    = collect();
    $stageHistories = [];
    $debugStageErr  = null;

    try {
        // 1. Ambil daftar PR yang berstatus PENDING / RETURNED / APPROVAL_UPPER
        $prs = DB::table('purchase_request')
            ->whereIn(DB::raw('UPPER(COALESCE(Status, \'\'))'), ['PENDING', 'RETURNED', 'APPROVAL_UPPER'])
            ->orderBy('ID', 'DESC')
            ->limit(100)
            ->get();

        // Kumpulkan Approval_Request_ID & PR_No untuk ambil approval_request secara batch
        $reqIdsByPrId = [];
        $reqIds       = [];
        $prNos        = [];

        foreach ($prs as $p) {
            $prNo = trim((string) ($p->PR_No ?? ''));
            if ($prNo !== '') {
                $prNos[] = $prNo;
            }
            if (!empty($p->Approval_Request_ID)) {
                $reqIdsByPrId[(int) $p->ID] = (int) $p->Approval_Request_ID;
                $reqIds[] = (int) $p->Approval_Request_ID;
            }
        }

        // Ambil approval_request berdasarkan ID maupun Request_No
        $requestsById   = [];
        $requestsByPrNo = [];

        if (!empty($reqIds) || !empty($prNos)) {
            $reqRows = DB::table('approval_request')
                ->where(function ($q) use ($reqIds, $prNos) {
                    if (!empty($reqIds)) {
                        $q->whereIn('ID', array_unique($reqIds));
                    }
                    if (!empty($prNos)) {
                        $q->orWhereIn('Request_No', array_unique($prNos));
                    }
                })
                ->orderBy('ID', 'DESC')
                ->get();

            foreach ($reqRows as $req) {
                $requestsById[(int) $req->ID] = $req;
                $rNo = trim((string) ($req->Request_No ?? ''));
                if ($rNo !== '' && !isset($requestsByPrNo[$rNo])) {
                    $requestsByPrNo[$rNo] = $req;
                }
            }
        }

        // Petakan setiap PR ke approval_request-nya
        $mappedReqIds = [];
        $prToReqId    = [];

        foreach ($prs as $p) {
            $prNo  = trim((string) ($p->PR_No ?? ''));
            $reqId = !empty($p->Approval_Request_ID) ? (int) $p->Approval_Request_ID : 0;

            if ($reqId > 0 && isset($requestsById[$reqId])) {
                $prToReqId[(int) $p->ID] = $reqId;
                $mappedReqIds[] = $reqId;
            } elseif ($prNo !== '' && isset($requestsByPrNo[$prNo])) {
                $foundReqId = (int) $requestsByPrNo[$prNo]->ID;
                $prToReqId[(int) $p->ID] = $foundReqId;
                $mappedReqIds[] = $foundReqId;
            }
        }

        // 2. Ambil seluruh task dari approval_request_task untuk Request_ID yang terhubung
        $tasksByReqId = [];
        $actorIds     = [];

        if (!empty($mappedReqIds)) {
            $allTasks = DB::table('approval_request_task')
                ->whereIn('Request_ID', array_unique($mappedReqIds))
                ->orderBy('Stage_Order', 'ASC')
                ->orderBy('Level_Number', 'ASC')
                ->orderBy('ID', 'ASC')
                ->get();

            foreach ($allTasks as $t) {
                $tasksByReqId[(int) $t->Request_ID][] = $t;
                if (!empty($t->Acted_By) && is_numeric($t->Acted_By)) {
                    $actorIds[] = (int) $t->Acted_By;
                }
            }
        }

        // Ambil mapping nama user jika Acted_By berisi ID numerik
        $usersById = [];
        if (!empty($actorIds)) {
            $uRows = DB::table('users')->whereIn('id', array_unique($actorIds))->get();
            foreach ($uRows as $u) {
                $usersById[(int) $u->id] = $u->Name ?? ($u->name ?? ($u->username ?? ('User #' . $u->id)));
            }
        }

        // Helper mengekstrak kode/nama policy dari approval_request (Policy_Snapshot / Context)
        $extractPolicyLabel = function ($req, $prRow) {
            if ($req) {
                $snapRaw = $req->Policy_Snapshot ?? '';
                if (is_string($snapRaw) && trim($snapRaw) !== '') {
                    $snap = json_decode($snapRaw, true);
                    if (is_array($snap)) {
                        foreach ([
                            'Policy_Code', 'policy_code',
                            'Policy_Name', 'policy_name',
                            'Code', 'code',
                            'Name', 'name',
                            'Title', 'title',
                        ] as $k) {
                            if (!empty($snap[$k]) && is_string($snap[$k])) {
                                return trim($snap[$k]);
                            }
                        }
                        if (!empty($snap['Policy']) && is_array($snap['Policy'])) {
                            foreach (['Policy_Code', 'Policy_Name', 'Code', 'Name'] as $pk) {
                                if (!empty($snap['Policy'][$pk]) && is_string($snap['Policy'][$pk])) {
                                    return trim($snap['Policy'][$pk]);
                                }
                            }
                        }
                    }
                }

                $ctxRaw = $req->Context ?? '';
                if (is_string($ctxRaw) && trim($ctxRaw) !== '') {
                    $ctx = json_decode($ctxRaw, true);
                    if (is_array($ctx)) {
                        foreach (['Policy_Code', 'policy_code', 'Policy_Name', 'policy_name', 'Policy', 'policy'] as $ck) {
                            if (!empty($ctx[$ck]) && is_string($ctx[$ck])) {
                                return trim($ctx[$ck]);
                            }
                        }
                    }
                }
            }

            return trim((string) ($prRow->Procurement_Category ?? '-')) ?: '-';
        };

        // 3. Susun $prApprovals (posisi/stage aktif) dan $stageHistories (semua stage)
        foreach ($prs as $p) {
            $prNo  = trim((string) ($p->PR_No ?? ''));
            $reqId = $prToReqId[(int) $p->ID] ?? null;
            $req   = $reqId ? ($requestsById[$reqId] ?? null) : null;
            $tasks = $reqId ? ($tasksByReqId[$reqId] ?? []) : [];

            // Cari task PENDING aktif pertama (sesuai urutan Stage_Order, Level_Number, ID)
            $activeTask = null;
            foreach ($tasks as $t) {
                if (strtoupper(trim((string) ($t->Status ?? ''))) === 'PENDING') {
                    $activeTask = $t;
                    break;
                }
            }

            if ($activeTask) {
                $posName    = trim((string) ($activeTask->Position_Name ?? '')) ?: (trim((string) ($activeTask->Position_Code ?? '')) ?: 'Approver');
                $stageOrder = $activeTask->Stage_Order ?? ($activeTask->Level_Number ?? 1);
                $taskStatus = strtoupper(trim((string) ($activeTask->Status ?? 'PENDING')));
                $taskId     = $activeTask->ID;
            } elseif ($req) {
                $posName    = 'Approval PR';
                $stageOrder = $req->Current_Stage ?? ($req->Current_Level ?? 1);
                $taskStatus = strtoupper(trim((string) ($req->Status ?? ($p->Status ?? 'PENDING'))));
                $taskId     = null;
            } else {
                $posName    = 'Approval PR';
                $stageOrder = 1;
                $taskStatus = strtoupper(trim((string) ($p->Status ?? 'PENDING')));
                $taskId     = null;
            }

            $prApprovals->push((object) [
                'task_id'          => $taskId,
                'request_id'       => $reqId,
                'pr_id'            => $p->ID,
                'pr_no'            => $prNo,
                'requester'        => $p->PIC ?: ($p->Created_By ?: '-'),
                'department'       => $p->Department ?: '-',
                'position'         => $posName,
                'stage_order'      => $stageOrder,
                'policy'           => $extractPolicyLabel($req, $p),
                'supplies_type'    => $p->Supplies_Type ?: 'NON-OPS',
                'category'         => $p->Category ?: 'NON ASSET',
                'sub_category'     => $p->Sub_Category ?: 'BARANG',
                'estimated_budget' => floatval($p->Estimated_Budget ?? 0),
                'note'             => $p->Note ?? '',
                'status'           => $taskStatus,
                'date_label'       => !empty($p->PR_Date)
                    ? \Carbon\Carbon::parse($p->PR_Date)->format('d M Y')
                    : date('d M Y'),
            ]);

            // Isi riwayat seluruh stage untuk PR ini
            if ($prNo !== '' && !empty($tasks)) {
                $stageHistories[$prNo] = [];
                foreach ($tasks as $t) {
                    $stPos = trim((string) ($t->Position_Name ?? '')) ?: (trim((string) ($t->Position_Code ?? '')) ?: 'Approver');
                    $rawActor = trim((string) ($t->Acted_By ?? ''));
                    $actorStr = '';
                    if ($rawActor !== '' && $rawActor !== '0') {
                        $actorStr = is_numeric($rawActor)
                            ? ($usersById[(int) $rawActor] ?? ('User #' . $rawActor))
                            : $rawActor;
                    }

                    $actedStr = '';
                    if (!empty($t->Acted_At)) {
                        try {
                            $actedStr = \Carbon\Carbon::parse($t->Acted_At)->format('d M Y H:i');
                        } catch (\Exception $ex) {
                            $actedStr = (string) $t->Acted_At;
                        }
                    }

                    $stageHistories[$prNo][] = (object) [
                        'stage'    => $t->Stage_Order ?? ($t->Level_Number ?? 1),
                        'position' => $stPos,
                        'status'   => strtoupper(trim((string) ($t->Status ?? ($t->Action ?? 'PENDING')))),
                        'actor'    => $actorStr,
                        'notes'    => trim((string) ($t->Notes ?? '')),
                        'acted_at' => $actedStr,
                    ];
                }
            }
        }
    } catch (\Exception $e) {
        $debugStageErr = $e->getMessage();
        \Log::warning('mobileApprovalPR error: ' . $e->getMessage());
    }

    return view('humanresources.procurement.mobile-pr-approval', compact('prApprovals', 'userObj', 'stageHistories', 'debugStageErr'));
}


public function mobileTrackingPR(Request $request)
{
    $username = session('username') ?? (Auth::user()->username ?? 'admin');
    $userObj  = Auth::user() ?? DB::table('users')->where('username', $username)->first();

    $prs = DB::table('purchase_request')
        ->orderBy('ID', 'DESC')
        ->limit(150)
        ->get();

    $prNos  = [];
    $reqIds = [];

    foreach ($prs as $p) {
        $prNo = trim((string) ($p->PR_No ?? ''));
        if ($prNo !== '') {
            $prNos[] = $prNo;
        }
        if (!empty($p->Approval_Request_ID)) {
            $reqIds[] = (int) $p->Approval_Request_ID;
        }
    }

    $prNos  = array_values(array_unique($prNos));
    $reqIds = array_values(array_unique($reqIds));

    // 1. Ambil approval_request (lewat ID maupun Request_No)
    $requestsById   = [];
    $requestsByPrNo = [];

    if (!empty($reqIds) || !empty($prNos)) {
        $reqRows = DB::table('approval_request')
            ->where(function ($q) use ($reqIds, $prNos) {
                if (!empty($reqIds)) {
                    $q->whereIn('ID', $reqIds);
                }
                if (!empty($prNos)) {
                    $q->orWhereIn('Request_No', $prNos);
                }
            })
            ->orderBy('ID', 'DESC')
            ->get();

        foreach ($reqRows as $req) {
            $requestsById[(int) $req->ID] = $req;
            $rNo = trim((string) ($req->Request_No ?? ''));
            if ($rNo !== '' && !isset($requestsByPrNo[$rNo])) {
                $requestsByPrNo[$rNo] = $req;
            }
        }
    }

    $mappedReqIds = [];
    $prToReqId    = [];

    foreach ($prs as $p) {
        $prNo  = trim((string) ($p->PR_No ?? ''));
        $reqId = !empty($p->Approval_Request_ID) ? (int) $p->Approval_Request_ID : 0;

        if ($reqId > 0 && isset($requestsById[$reqId])) {
            $prToReqId[(int) $p->ID] = $reqId;
            $mappedReqIds[] = $reqId;
        } elseif ($prNo !== '' && isset($requestsByPrNo[$prNo])) {
            $foundReqId = (int) $requestsByPrNo[$prNo]->ID;
            $prToReqId[(int) $p->ID] = $foundReqId;
            $mappedReqIds[] = $foundReqId;
        }
    }

    // 2. Ambil seluruh task approval_request_task
    $tasksByReqId = [];
    $actorIds     = [];

    if (!empty($mappedReqIds)) {
        $allTasks = DB::table('approval_request_task')
            ->whereIn('Request_ID', array_values(array_unique($mappedReqIds)))
            ->orderBy('Stage_Order', 'ASC')
            ->orderBy('Level_Number', 'ASC')
            ->orderBy('ID', 'ASC')
            ->get();

        foreach ($allTasks as $t) {
            $tasksByReqId[(int) $t->Request_ID][] = $t;
            if (!empty($t->Acted_By) && is_numeric($t->Acted_By)) {
                $actorIds[] = (int) $t->Acted_By;
            }
        }
    }

    $usersById = [];
    if (!empty($actorIds)) {
        $uRows = DB::table('users')->whereIn('id', array_values(array_unique($actorIds)))->get();
        foreach ($uRows as $u) {
            $usersById[(int) $u->id] = $u->Name ?? ($u->name ?? ($u->username ?? ('User #' . $u->id)));
        }
    }

    // 3. Ambil rincian item PR, RFQ, PO, GR, dan Notes History secara batch
    $itemsByPr = [];
    $rfqsByPr  = [];
    $posByPr   = [];
    $grsByPr   = [];
    $notesByPr = [];

    if (!empty($prNos)) {
        foreach (DB::table('purchase_request_dtl')->whereIn('PR_No', $prNos)->orderBy('ID', 'ASC')->get() as $it) {
            $itemsByPr[trim((string) $it->PR_No)][] = $it;
        }

        foreach (DB::table('rfq as r')
            ->leftJoin('supplier as s', DB::raw('r.Supplier_ID COLLATE utf8mb4_unicode_ci'), '=', DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci'))
            ->select('r.PR_No', 'r.RFQ_No', 'r.Supplier_ID', 'r.Status', 'r.Grand_Total', 's.Company_Name')
            ->whereIn('r.PR_No', $prNos)
            ->orderBy('r.ID', 'ASC')
            ->get() as $rfq) {
            $rfqsByPr[trim((string) $rfq->PR_No)][] = $rfq;
        }

        foreach (DB::table('purchase_order as po')
            ->leftJoin('supplier as s', DB::raw('po.Supplier COLLATE utf8mb4_unicode_ci'), '=', DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci'))
            ->select('po.PR_No', 'po.PO_No', 'po.PO_Date', 'po.Supplier', 'po.Total', 'po.Status', 'po.Status_Receive', 's.Company_Name')
            ->whereIn('po.PR_No', $prNos)
            ->orderBy('po.ID', 'ASC')
            ->get() as $po) {
            $posByPr[trim((string) $po->PR_No)][] = $po;
        }

        foreach (DB::table('good_receive')->whereIn('PR_No', $prNos)->orderBy('ID', 'ASC')->get() as $gr) {
            $grsByPr[trim((string) $gr->PR_No)][] = $gr;
        }

        foreach (DB::table('pr_notes_history')->whereIn('PR_No', $prNos)->orderBy('ID', 'DESC')->get() as $nh) {
            $notesByPr[trim((string) $nh->PR_No)][] = $nh;
        }
    }

    // 4. Susun data tracking per PR
    $trackingList = collect();

    foreach ($prs as $p) {
        $prNo      = trim((string) ($p->PR_No ?? ''));
        $rawStatus = strtoupper(trim((string) ($p->Status ?? 'PENDING')));
        $reqId     = $prToReqId[(int) $p->ID] ?? null;
        $req       = $reqId ? ($requestsById[$reqId] ?? null) : null;
        $tasks     = $reqId ? ($tasksByReqId[$reqId] ?? []) : [];

        // Ekstrak Policy Code dari Policy_Snapshot
        $policyCode = trim((string) ($p->Procurement_Category ?? '-')) ?: '-';
        if ($req && !empty($req->Policy_Snapshot)) {
            $snap = json_decode((string) $req->Policy_Snapshot, true);
            if (is_array($snap)) {
                foreach (['Policy_Code', 'policy_code', 'Policy_Name', 'policy_name', 'Code', 'Name'] as $k) {
                    if (!empty($snap[$k]) && is_string($snap[$k])) {
                        $policyCode = trim($snap[$k]);
                        break;
                    }
                }
            }
        }

        // Susun stage approval & cari stage aktif
        $stages     = [];
        $activePos  = '-';
        $activeStg  = null;

        foreach ($tasks as $t) {
            $stStatus = strtoupper(trim((string) ($t->Status ?? ($t->Action ?? 'PENDING'))));
            $stPos    = trim((string) ($t->Position_Name ?? '')) ?: (trim((string) ($t->Position_Code ?? '')) ?: 'Approver');
            $stNum    = $t->Stage_Order ?? ($t->Level_Number ?? 1);

            if ($activeStg === null && in_array($stStatus, ['PENDING', 'WAITING'], true)) {
                $activePos = $stPos;
                $activeStg = $stNum;
            }

            $rawActor = trim((string) ($t->Acted_By ?? ''));
            $actorStr = '';
            if ($rawActor !== '' && $rawActor !== '0') {
                $actorStr = is_numeric($rawActor)
                    ? ($usersById[(int) $rawActor] ?? ('User #' . $rawActor))
                    : $rawActor;
            }

            $actedStr = '';
            if (!empty($t->Acted_At)) {
                try {
                    $actedStr = \Carbon\Carbon::parse($t->Acted_At)->format('d M Y H:i');
                } catch (\Exception $ex) {
                    $actedStr = (string) $t->Acted_At;
                }
            }

            $stages[] = (object) [
                'stage'    => $stNum,
                'position' => $stPos,
                'status'   => $stStatus,
                'actor'    => $actorStr,
                'notes'    => trim((string) ($t->Notes ?? '')),
                'acted_at' => $actedStr,
            ];
        }

        $rfqList  = $rfqsByPr[$prNo] ?? [];
        $poList   = $posByPr[$prNo] ?? [];
        $grList   = $grsByPr[$prNo] ?? [];
        $itemList = $itemsByPr[$prNo] ?? [];
        $noteList = $notesByPr[$prNo] ?? [];

        // Tentukan kategori filter & progress persentase (4 fase: PR -> Approval -> RFQ/PO -> GR)
        if (in_array($rawStatus, ['REJECTED', 'CANCEL', 'CANCELLED'], true)) {
            $filterGroup = 'REJECTED';
            $progressPct = 100;
            $stageLabel  = 'Ditolak / Dibatalkan';
        } elseif (!empty($grList) || in_array($rawStatus, ['RECEIVED', 'CLOSED'], true)) {
            $filterGroup = 'PO_GR';
            $progressPct = 100;
            $stageLabel  = 'Goods Received (' . count($grList) . ' GR)';
        } elseif (!empty($poList) || in_array($rawStatus, ['PO_CREATED', 'PARTIAL_PO'], true)) {
            $filterGroup = 'PO_GR';
            $progressPct = 75;
            $stageLabel  = 'PO Terbit (' . count($poList) . ' PO)';
        } elseif ($rawStatus === 'APPROVED') {
            $filterGroup = 'APPROVED';
            $progressPct = !empty($rfqList) ? 60 : 50;
            $stageLabel  = !empty($rfqList) ? 'Proses RFQ (' . count($rfqList) . ' Vendor)' : 'PR Disetujui (Siap RFQ/PO)';
        } else {
            $filterGroup = 'PENDING';
            $progressPct = 25;
            $stageLabel  = $activeStg !== null
                ? "Menunggu {$activePos} (Stage {$activeStg})"
                : 'Menunggu Approval';
        }

        $trackingList->push((object) [
            'pr_id'            => $p->ID,
            'pr_no'            => $prNo,
            'pr_date_label'    => !empty($p->PR_Date) ? \Carbon\Carbon::parse($p->PR_Date)->format('d M Y') : '-',
            'hope_date_label'  => !empty($p->Hope_Date) ? \Carbon\Carbon::parse($p->Hope_Date)->format('d M Y') : '-',
            'requester'        => $p->PIC ?: ($p->Created_By ?: '-'),
            'department'       => $p->Department ?: '-',
            'supplies_type'    => $p->Supplies_Type ?: 'NON-OPS',
            'category'         => $p->Category ?: 'NON ASSET',
            'sub_category'     => $p->Sub_Category ?: 'BARANG',
            'policy'           => $policyCode,
            'estimated_budget' => floatval($p->Estimated_Budget ?? 0),
            'note'             => trim((string) ($p->Note ?? '')),
            'status'           => $rawStatus,
            'filter_group'     => $filterGroup,
            'progress_pct'     => $progressPct,
            'stage_label'      => $stageLabel,
            'stages'           => $stages,
            'items'            => $itemList,
            'rfqs'             => $rfqList,
            'pos'              => $poList,
            'grs'              => $grList,
            'notes_history'    => $noteList,
        ]);
    }

    return view('humanresources.procurement.mobile-pr-tracking', compact('trackingList', 'userObj'));
}

private function sendOneSignalNotification(array $targetExternalIds, $title, $message, array $extraData = [])
{
    $appId  = config('services.onesignal.app_id');
    $apiKey = config('services.onesignal.rest_api_key');

    $recipients = array_values(array_unique(array_filter(array_map(function ($val) {
        return trim((string) $val);
    }, $targetExternalIds))));

    if (empty($appId) || empty($apiKey) || empty($recipients)) {
        return;
    }

    $payload = [
        'app_id' => $appId,
        'headings' => [
            'en' => $title,
            'id' => $title,
        ],
        'contents' => [
            'en' => $message,
            'id' => $message,
        ],
        // Pastikan di Kodular / OneSignal memakai External User ID / Alias yang sama dengan nilai di $recipients
        'include_external_user_ids' => $recipients,
        'data' => $extraData,
    ];

    $ch = curl_init('https://onesignal.com/api/v1/notifications');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic ' . $apiKey,
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        \Log::warning('OneSignal PR Created error: ' . $curlErr);
    } elseif ($response) {
        \Log::info('OneSignal PR Created response: ' . $response);
    }
}

        // 1. Tampilkan Halaman Utama Purchase Order List
public function purchaseOrderListIndex()
{
    return view('humanresources.procurement.purchase-order-list');
}

// 2. Endpoint XML DHTMLX Grid untuk Seluruh Daftar Purchase Order
public function xmlPurchaseOrderList()
{
    $pos = DB::table('purchase_order as po')
        ->leftJoin(
            'supplier as s',
            DB::raw('po.Supplier COLLATE utf8mb4_unicode_ci'),
            '=',
            DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci')
        )
        ->select(
            'po.ID',
            'po.PO_No',
            'po.PO_Date',
            'po.PR_No',
            'po.Supplier',
            's.Company_Name',
            'po.Department',
            'po.Term_Payment',
            'po.Total',
            'po.Status',
            'po.Status_Receive',
            'po.User_Trx'
        )
        ->orderBy('po.ID', 'desc')
        ->get();

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<rows>';

    $no = 1;
    foreach ($pos as $row) {
        $vendorDisplay = $row->Company_Name
            ? $row->Supplier . ' - ' . $row->Company_Name
            : ($row->Supplier ?? '-');

        $xml .= '<row id="' . htmlspecialchars($row->PO_No) . '">';
        $xml .= '<cell>' . $no++ . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->PO_No) . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->PO_Date ?? '-') . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->PR_No ?? '-') . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($vendorDisplay) . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->Department ?? '-') . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->Term_Payment ?? '-') . '</cell>';
        $xml .= '<cell>' . number_format($row->Total ?? 0, 0, ',', '.') . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->Status ?? 'OPEN') . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->Status_Receive ?? 'OPEN') . '</cell>';
        $xml .= '<cell>' . htmlspecialchars($row->User_Trx ?? '-') . '</cell>';
        $xml .= '</row>';
    }

    $xml .= '</rows>';

    return response($xml, 200)->header('Content-Type', 'text/xml');
}

// 3. Form Edit PO (Disamakan dengan form Create PO Emergency + fitur tambah/hapus item)
public function editPOForm(Request $request)
{
    $poNo = trim((string) $request->query('PO_No'));
    $poHeader = DB::table('purchase_order')->where('PO_No', $poNo)->first();

    if (!$poHeader) {
        return '<div class="alert alert-danger mb-0">Data Purchase Order tidak ditemukan.</div>';
    }

    $poItems = DB::table('purchase_order_dtl')
        ->where('PO_No', $poNo)
        ->orderBy('ID', 'ASC')
        ->get();

    $suppliers = DB::table('supplier')
        ->orderBy('Company_Name', 'ASC')
        ->get();

    $departments = DB::table('department')
        ->orderBy('Department', 'ASC')
        ->get();

    $materials = DB::table('product_code')
        ->select('Product_Code', 'Description', 'Specification', 'Unit_Name')
        ->orderBy('Description', 'ASC')
        ->get();

    $username = session('username') ?? (auth()->user()->Username ?? auth()->user()->name ?? 'admin');

    return view('humanresources.procurement.po-edit-form', compact(
        'poHeader',
        'poItems',
        'suppliers',
        'departments',
        'materials',
        'username'
    ));
}

// 4. Simpan Perubahan Edit PO
public function updatePO(Request $request)
{
    $poNo       = trim((string) $request->input('po_no'));
    $poDate     = trim((string) $request->input('po_date', date('Y-m-d')));
    $supplierId = trim((string) $request->input('supplier_id'));
    $department = trim((string) $request->input('department', ''));
    $termPay    = trim((string) $request->input('term_payment', 'NET 30'));
    $category   = trim((string) $request->input('purchase_category', 'NON ASSET'));
    $subCat     = trim((string) $request->input('purchase_sub_category', 'BARANG'));
    $suppliesType = strtoupper(trim((string) $request->input('supplies_type', 'NON OPS')));
    if (!in_array($suppliesType, ['OPS', 'NON OPS'], true)) {
        $suppliesType = 'NON OPS';
    }

    $pic        = trim((string) $request->input('pic', ''));
    $siteDest   = trim((string) $request->input('site_destination', ''));
    $picPhone   = trim((string) $request->input('pic_phone', ''));
    $picEmail   = trim((string) $request->input('pic_email', ''));
    $remark     = (string) $request->input('remark', '');

    $discount   = floatval($request->input('discount', 0));
    $ppn        = floatval($request->input('ppn', 0));
    $pph        = floatval($request->input('pph', 0));
    $delivery   = floatval($request->input('delivery_fee', 0));
    $items      = $request->input('items', []);
    $username   = session('username') ?? (auth()->user()->Username ?? auth()->user()->name ?? 'admin');

    DB::beginTransaction();
    try {
        $po = DB::table('purchase_order')->where('PO_No', $poNo)->first();
        if (!$po) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Data PO tidak ditemukan.'], 404);
        }

        $supplier = DB::table('supplier')->where('Supplier_ID', $supplierId)->first();

        // Hapus detail PO lama lalu insert ulang sesuai item tersisa
        DB::table('purchase_order_dtl')->where('PO_No', $poNo)->delete();

        $subtotal = 0;
        if (is_array($items) && !empty($items)) {
            foreach ($items as $row) {
                $desc  = trim((string) ($row['product_desc'] ?? ''));
                $qty   = floatval($row['qty'] ?? 0);
                $price = floatval($row['price'] ?? 0);
                if ($desc === '' || $qty <= 0) continue;

                $lineSub = $qty * $price;
                $subtotal += $lineSub;

                DB::table('purchase_order_dtl')->insert([
                    'Location'      => $siteDest ?: ($po->Location ?? 'HO'),
                    'PR_No'         => $po->PR_No,
                    'PO_No'         => $poNo,
                    'Category'      => $category ?: $po->Purchase_Category,
                    'Sub_Category'  => $subCat ?: $po->Purchase_Sub_Category,
                    'Purchase_Type' => $po->Purchase_Type,
                    'Product_Code'  => trim((string) ($row['product_code'] ?? '')) ?: 'EMERGENCY-ITEM',
                    'Product_Desc'  => $desc,
                    'Specification' => trim((string) ($row['specification'] ?? '')) ?: null,
                    'Qty'           => $qty,
                    'Unit'          => trim((string) ($row['unit'] ?? 'PCS')) ?: 'PCS',
                    'Price'         => $price,
                    'Discount'      => 0,
                    'Subtotal'      => $lineSub,
                    'PPN'           => 0,
                    'PPN_pct'       => 0,
                    'Total'         => $lineSub,
                    'Qty_Receive'   => 0,
                    'Qty_Remain'    => $qty,
                    'Currency'      => $po->Currency ?? 'IDR',
                ]);
            }
        }

        $grandTotal = max(0, $subtotal - $discount + $ppn - $pph + $delivery);

        $headerUpdate = [
            'PO_Date'               => $poDate ?: $po->PO_Date,
            'PO_Dest'               => $siteDest ?: $po->PO_Dest,
            'Department'            => $department !== '' ? $department : $po->Department,
            'Purchase_Category'     => $category ?: $po->Purchase_Category,
            'Purchase_Sub_Category' => $subCat ?: $po->Purchase_Sub_Category,
            'PIC'                   => $pic !== '' ? $pic : $po->PIC,
            'PIC_Address'           => $siteDest ?: $po->PIC_Address,
            'PIC_Phone'             => $picPhone !== '' ? $picPhone : null,
            'PIC_Email'             => $picEmail !== '' ? $picEmail : null,
            'Supplier'              => $supplierId ?: $po->Supplier,
            'Supplier_PIC'          => $supplier->Contact_Person ?? $po->Supplier_PIC,
            'Term_Payment'          => $termPay,
            'Pay_Term'              => $termPay,
            'Subtotal'              => $subtotal,
            'Discount'              => $discount,
            'PPN'                   => $ppn,
            'PPH'                   => $pph,
            'Delivery_Fee'          => $delivery,
            'Total'                 => $grandTotal,
            'Remark'                => $remark,
            'User_Trx'              => $username,
            'Date_Trx'              => now(),
        ];

        if (\Schema::hasColumn('purchase_order', 'Supplies_Type')) {
            $headerUpdate['Supplies_Type'] = $suppliesType;
        }

        DB::table('purchase_order')->where('PO_No', $poNo)->update($headerUpdate);

        DB::commit();

        return response()->json([
            'status'  => 'success',
            'message' => "Purchase Order {$poNo} berhasil diperbarui!"
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status'  => 'error',
            'message' => 'Gagal menyimpan perubahan PO: ' . $e->getMessage()
        ], 500);
    }
}

// 5. Kirim PO Otomatis via WhatsApp sesuai data master vendor
public function sendPOWhatsApp(Request $request)
{
    $poNo = trim((string) $request->input('po_no'));
    $po = DB::table('purchase_order as po')
        ->leftJoin(
            'supplier as s',
            DB::raw('po.Supplier COLLATE utf8mb4_unicode_ci'),
            '=',
            DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci')
        )
        ->select('po.*', 's.Company_Name', 's.Contact_Person', 's.Phone')
        ->where('po.PO_No', $poNo)
        ->first();

    if (!$po) {
        return response()->json(['status' => 'error', 'message' => 'Data PO tidak ditemukan.'], 404);
    }

    $rawPhone = preg_replace('/[^0-9]/', '', (string) ($po->Phone ?? ''));
    if ($rawPhone === '') {
        return response()->json([
            'status'  => 'error',
            'message' => 'Nomor telepon/WhatsApp vendor (' . ($po->Company_Name ?? $po->Supplier) . ') belum terdaftar di Master Supplier.'
        ], 422);
    }

    if (str_starts_with($rawPhone, '0')) {
        $rawPhone = '62' . substr($rawPhone, 1);
    }

    $items = DB::table('purchase_order_dtl')->where('PO_No', $poNo)->orderBy('ID', 'ASC')->get();
    $lines = [];
    foreach ($items as $i => $it) {
        $lines[] = ($i + 1) . ". {$it->Product_Desc} ({$it->Qty} {$it->Unit}) @ Rp " . number_format($it->Price ?? 0, 0, ',', '.');
    }

    $vendorName = $po->Company_Name ?? $po->Supplier;
    $picName    = !empty($po->Contact_Person) ? "Bpk/Ibu {$po->Contact_Person}" : "Tim {$vendorName}";
    $pdfLink    = url('po-print?PO_No=' . urlencode($poNo));
    $totalStr   = 'Rp ' . number_format($po->Total ?? 0, 0, ',', '.');

    $text = "Halo {$picName} ({$vendorName}),\n\n"
          . "Berikut kami kirimkan *Purchase Order (PO)*:\n"
          . "• No. PO: *{$po->PO_No}*\n"
          . "• Tanggal PO: {$po->PO_Date}\n"
          . "• Termin (TOP): " . ($po->Term_Payment ?? '-') . "\n"
          . "• Grand Total: *{$totalStr}*\n\n"
          . "*Rincian Item:*\n" . implode("\n", $lines) . "\n\n"
          . "Dokumen PO PDF:\n{$pdfLink}";

    return response()->json([
        'status'  => 'success',
        'wa_url'  => 'https://wa.me/' . $rawPhone . '?text=' . urlencode($text),
        'message' => "Menyiapkan WhatsApp ke {$vendorName} ({$rawPhone})..."
    ]);
}

// 6. Kirim PO Otomatis via Email sesuai data master vendor + lampiran PDF
public function sendPOEmail(Request $request)
{
    $poNo = trim((string) $request->input('po_no'));
    $poHeader = DB::table('purchase_order as po')
        ->leftJoin(
            'supplier as s',
            DB::raw('po.Supplier COLLATE utf8mb4_unicode_ci'),
            '=',
            DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci')
        )
        ->select('po.*', 's.Company_Name', 's.Address as Supplier_Address', 's.Phone as Supplier_Phone', 's.Email as Supplier_Email')
        ->where('po.PO_No', $poNo)
        ->first();

    if (!$poHeader) {
        return response()->json(['status' => 'error', 'message' => 'Data PO tidak ditemukan.'], 404);
    }

    $vendorEmail = trim((string) ($poHeader->Supplier_Email ?? ''));
    if ($vendorEmail === '' || !filter_var($vendorEmail, FILTER_VALIDATE_EMAIL)) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Email vendor (' . ($poHeader->Company_Name ?? $poHeader->Supplier) . ') belum terdaftar atau tidak valid di Master Supplier.'
        ], 422);
    }

    $poItems = DB::table('purchase_order_dtl')->where('PO_No', $poNo)->orderBy('ID', 'ASC')->get();

    try {
        $mpdfTempDir = storage_path('app/mpdf');
        if (!file_exists($mpdfTempDir)) {
            mkdir($mpdfTempDir, 0775, true);
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'tempDir'       => $mpdfTempDir,
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 42,
            'margin_bottom' => 15,
            'margin_header' => 5,
            'margin_footer' => 5,
        ]);

        $kopPath = public_path('assets/images/surat/header_geu.jpg');
        if (file_exists($kopPath)) {
            $ext = pathinfo($kopPath, PATHINFO_EXTENSION);
            $base64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($kopPath));
            $mpdf->SetHTMLHeader('
                <div style="text-align: center; border-bottom: 1.5px solid #689f38; padding-bottom: 5px;">
                    <img src="' . $base64 . '" style="width: 100%; max-height: 95px;">
                </div>
            ');
        }

        $html = view('humanresources.procurement.po-print-pdf', compact('poHeader', 'poItems'))->render();
        $mpdf->WriteHTML($html);
        $pdfBinary = $mpdf->Output('', 'S');
        $safeFilename = str_replace('/', '-', $poHeader->PO_No) . '.pdf';

        $vendorName = $poHeader->Company_Name ?? $poHeader->Supplier;
        $bodyText = "Kepada Yth. {$vendorName},\n\n"
                  . "Terlampir kami kirimkan dokumen Purchase Order nomor {$poHeader->PO_No} (Ref PR: " . ($poHeader->PR_No ?? '-') . ").\n"
                  . "Grand Total: Rp " . number_format($poHeader->Total ?? 0, 0, ',', '.') . "\n\n"
                  . "Terima kasih,\nProcurement Team";

        \Mail::raw($bodyText, function ($message) use ($vendorEmail, $vendorName, $poHeader, $pdfBinary, $safeFilename) {
            $message->to($vendorEmail, $vendorName)
                ->subject("Purchase Order - {$poHeader->PO_No}")
                ->attachData($pdfBinary, $safeFilename, ['mime' => 'application/pdf']);
        });

        return response()->json([
            'status'  => 'success',
            'message' => "Purchase Order {$poNo} berhasil dikirim via Email ke {$vendorName} ({$vendorEmail})!"
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Gagal mengirim Email PO: ' . $e->getMessage()
        ], 500);
    }
}

        // 1. Tampilkan Form Create PO Emergency (Bypass PR/RFQ/QTN)
public function createEmergencyPOForm()
{
    $prefix = 'PO/GEU/' . date('Ym') . '-';
    $lastPo = DB::table('purchase_order')
        ->where('PO_No', 'like', $prefix . '%')
        ->orderBy('ID', 'desc')
        ->first();

    $nextNum = $lastPo ? ((int) substr($lastPo->PO_No, -4)) + 1 : 1;
    $generated_po_no = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

    $suppliers = DB::table('supplier')->orderBy('Company_Name', 'ASC')->get();
    $departments = DB::table('department')->orderBy('Department', 'ASC')->get();
    $materials = DB::table('product_code')
        ->select('Product_Code', 'Description', 'Specification', 'Unit_Name')
        ->orderBy('Description', 'ASC')
        ->get();

    $username = session('username') ?? (auth()->user()->Username ?? auth()->user()->name ?? 'admin');
    $userData = DB::table('users')->where('username', $username)->first();
    $userDepartment = $userData->Department ?? '';

    return view('humanresources.procurement.po-emergency-form', compact(
        'generated_po_no',
        'suppliers',
        'departments',
        'materials',
        'username',
        'userDepartment'
    ));
}

// 2. Simpan PO Emergency ke purchase_order & purchase_order_dtl
public function storeEmergencyPO(Request $request)
{
    $request->validate([
        'po_date'          => 'required|date',
        'supplier_id'      => 'required|string',
        'department'       => 'required|string',
        'emergency_reason' => 'required|string',
        'items'            => 'required|array|min:1',
    ]);

    $username   = session('username') ?? (auth()->user()->Username ?? auth()->user()->name ?? 'admin');
    $userData   = DB::table('users')->where('username', $username)->first();
    $location   = $userData->Location ?? 'HO';
    $supplierId = trim((string) $request->input('supplier_id'));
    $supplier   = DB::table('supplier')->where('Supplier_ID', $supplierId)->first();

    DB::beginTransaction();
    try {
        // Generate ulang nomor PO di dalam transaksi agar aman dari bentrok
        $prefix = 'PO/GEU/' . date('Ym') . '-';
        $lastPo = DB::table('purchase_order')
            ->where('PO_No', 'like', $prefix . '%')
            ->orderBy('ID', 'desc')
            ->lockForUpdate()
            ->first();

        $nextNum = $lastPo ? ((int) substr($lastPo->PO_No, -4)) + 1 : 1;
        $poNo    = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $subtotal = 0;
        $validItems = [];

        foreach ($request->input('items', []) as $row) {
            $desc  = trim((string) ($row['product_desc'] ?? ''));
            $qty   = floatval($row['qty'] ?? 0);
            $price = floatval($row['price'] ?? 0);

            if ($desc === '' || $qty <= 0) {
                continue;
            }

            $lineTotal = $qty * $price;
            $subtotal += $lineTotal;

            $validItems[] = [
                'product_code'  => trim((string) ($row['product_code'] ?? '')) ?: 'EMERGENCY-ITEM',
                'product_desc'  => $desc,
                'specification' => trim((string) ($row['specification'] ?? '')),
                'qty'           => $qty,
                'unit'          => trim((string) ($row['unit'] ?? 'PCS')) ?: 'PCS',
                'price'         => $price,
                'total'         => $lineTotal,
            ];
        }

        if (empty($validItems)) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Minimal harus mengisi 1 item dengan deskripsi dan qty valid.'
            ], 422);
        }

        $discount    = floatval($request->input('discount', 0));
        $ppn         = floatval($request->input('ppn', 0));
        $deliveryFee = floatval($request->input('delivery_fee', 0));
        $grandTotal  = max(0, $subtotal - $discount + $ppn + $deliveryFee);

        $siteDest = trim((string) $request->input('site_destination', $location));
        $picPhone = trim((string) $request->input('pic_phone', ''));
        $picEmail = trim((string) $request->input('pic_email', ''));

        // Hitung otomatis Lead Time Emergency (<= 1 hari kerja sejak PO_Date)
        $poDateCarbon = \Carbon\Carbon::parse($request->input('po_date'))->startOfDay();
        $dueDateCarbon = $poDateCarbon->copy()->addDay();
        while ($dueDateCarbon->isWeekend()) {
            $dueDateCarbon->addDay();
        }
        $leadTimeDays = max(1, $poDateCarbon->diffInDaysFiltered(function ($d) {
            return !$d->isWeekend();
        }, $dueDateCarbon));
        $leadTimeInfo = "Lead Time Emergency: <= {$leadTimeDays} hari kerja (Target: {$dueDateCarbon->format('d/m/Y')})";

        $termPayment = trim((string) $request->input('term_payment', 'NET 30'));
        $category    = $request->input('purchase_category', 'NON ASSET');
        $subCategory = $request->input('purchase_sub_category', 'BARANG');
        $reason      = trim((string) $request->input('emergency_reason'));
        $suppliesType = strtoupper(trim((string) $request->input('supplies_type', 'NON OPS')));
            if (!in_array($suppliesType, ['OPS', 'NON OPS'], true)) {
                $suppliesType = 'NON OPS';
            }

        // Insert Header PO (PR_No ditandai EMERGENCY / BYPASS-PR)
        $poHeaderData = [
    'Location'              => $location,
    'Process_By'            => $username,
    'Purchase_Type'         => 'EMERGENCY',
    'Purchase_Category'     => $category,
    'Purchase_Sub_Category' => $subCategory,
    'PR_No'                 => 'EMERGENCY',
    'PO_No'                 => $poNo,
    'PO_Date'               => $request->input('po_date'),
    'PO_Dest'               => $siteDest,
    'Department'            => $request->input('department'),
    'PIC_Address'           => $siteDest,
    'PIC_Phone'             => $picPhone ?: null,
    'PIC_Email'             => $picEmail ?: null,
    'PIC'                   => trim((string) $request->input('pic', $username)),
    'Supplier'              => $supplierId,
    'Supplier_PIC'          => $supplier->Contact_Person ?? null,
    'Term_Payment'          => $termPayment,
    'Pay_Term'              => $termPayment,
    'Subtotal'              => $subtotal,
    'Discount'              => $discount,
    'PPN'                   => $ppn,
    'PPH'                   => 0,
    'Delivery_Fee'          => $deliveryFee,
    'Total'                 => $grandTotal,
    'Total_Paid'            => 0,
    'Status'                => 'OPEN',
    'Status_Receive'        => 'OPEN',
    'Currency'              => 'IDR',
    'Remark'                => "[PO EMERGENCY - {$suppliesType} - BYPASS PR/RFQ/QTN] {$reason} | {$leadTimeInfo}",
    'Date_Trx'              => now(),
    'User_Trx'              => $username,
];

if (\Schema::hasColumn('purchase_order', 'Supplies_Type')) {
    $poHeaderData['Supplies_Type'] = $suppliesType;
}

DB::table('purchase_order')->insert($poHeaderData);

        // Insert Detail Item ke purchase_order_dtl
        foreach ($validItems as $it) {
            DB::table('purchase_order_dtl')->insert([
                'Location'      => $location,
                'PR_No'         => 'EMERGENCY',
                'PO_No'         => $poNo,
                'Category'      => $category,
                'Sub_Category'  => $subCategory,
                'Purchase_Type' => 'EMERGENCY',
                'Product_Code'  => $it['product_code'],
                'Product_Desc'  => $it['product_desc'],
                'Specification' => $it['specification'] ?: null,
                'Qty'           => $it['qty'],
                'Unit'          => $it['unit'],
                'Price'         => $it['price'],
                'Discount'      => 0,
                'Subtotal'      => $it['total'],
                'PPN'           => 0,
                'PPN_pct'       => 0,
                'Total'         => $it['total'],
                'Qty_Receive'   => 0,
                'Qty_Remain'    => $it['qty'],
                'Currency'      => 'IDR',
            ]);
        }

        DB::commit();

        return response()->json([
            'status'  => 'success',
            'po_no'   => $poNo,
            'message' => "PO Emergency {$poNo} berhasil diterbitkan!"
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status'  => 'error',
            'message' => 'Gagal menyimpan PO Emergency: ' . $e->getMessage()
        ], 500);
    }
}

public function mobileGoodReceivedPO(Request $request)
{
    $username = session('username') ?? (Auth::user()->username ?? 'admin');
    $userObj  = Auth::user() ?? DB::table('users')->where('username', $username)->first();

    $pos = DB::table('purchase_order as po')
        ->leftJoin(
            'supplier as s',
            DB::raw('po.Supplier COLLATE utf8mb4_unicode_ci'),
            '=',
            DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci')
        )
        ->select(
            'po.ID',
            'po.PO_No',
            'po.PO_Date',
            'po.PR_No',
            'po.Supplier',
            's.Company_Name',
            'po.Department',
            'po.PIC',
            'po.PIC_Address',
            'po.Total',
            'po.Status',
            'po.Status_Receive',
            'po.Remark'
        )
        ->whereIn(DB::raw('UPPER(COALESCE(po.Status, \'\'))'), ['OPEN', 'APPROVED', 'DELIVERY', 'PARTIAL', 'RECEIVED'])
        ->orderBy('po.ID', 'DESC')
        ->limit(100)
        ->get();

    $poNos = $pos->pluck('PO_No')->filter()->unique()->values()->all();

    $detailsByPo = [];
    $grsByPo     = [];

    if (!empty($poNos)) {
        foreach (DB::table('purchase_order_dtl')->whereIn('PO_No', $poNos)->orderBy('ID', 'ASC')->get() as $dtl) {
            $orderQty = floatval($dtl->Qty ?? 0);
            $recQty   = floatval($dtl->Qty_Receive ?? 0);
            $remain   = isset($dtl->Qty_Remain) && $dtl->Qty_Remain !== null
                ? max(0, floatval($dtl->Qty_Remain))
                : max(0, $orderQty - $recQty);

            $detailsByPo[trim((string) $dtl->PO_No)][] = (object) [
                'ID'           => $dtl->ID,
                'Product_Code' => $dtl->Product_Code ?? '-',
                'Product_Desc' => $dtl->Product_Desc ?? '-',
                'Qty'          => $orderQty,
                'Qty_Receive'  => $recQty,
                'Qty_Remain'   => $remain,
                'Unit'         => $dtl->Unit ?? 'PCS',
            ];
        }

        foreach (DB::table('good_receive')->whereIn('PO_No', $poNos)->orderBy('ID', 'DESC')->get() as $gr) {
            $grsByPo[trim((string) $gr->PO_No)][] = $gr;
        }
    }
$poReceiveList = collect();
    foreach ($pos as $p) {
        $poNo   = trim((string) ($p->PO_No ?? ''));
        $items  = $detailsByPo[$poNo] ?? [];
        $grList = $grsByPo[$poNo] ?? [];

        $totalRemainQty = 0;
        foreach ($items as $it) {
            $totalRemainQty += floatval($it->Qty_Remain ?? 0);
        }

        $stPo = strtoupper(trim((string) ($p->Status ?? 'OPEN')));
        $canReceive = ($totalRemainQty > 0) && !in_array($stPo, ['CANCELLED', 'CLOSED'], true);

        $poReceiveList->push((object) [
            'id'             => $p->ID,
            'po_no'          => $poNo,
            'pr_no'          => $p->PR_No ?: '-',
            'po_date_label'  => !empty($p->PO_Date) ? \Carbon\Carbon::parse($p->PO_Date)->format('d M Y') : '-',
            'vendor'         => $p->Company_Name ? ($p->Supplier . ' - ' . $p->Company_Name) : ($p->Supplier ?: '-'),
            'department'     => $p->Department ?: '-',
            'destination'    => $p->PIC_Address ?: ($p->PIC ?: '-'),
            'total'          => floatval($p->Total ?? 0),
            'status'         => $stPo,
            'status_receive' => strtoupper(trim((string) ($p->Status_Receive ?? 'OPEN'))),
            'total_remain'   => $totalRemainQty,
            'can_receive'    => $canReceive,
            'items'          => $items,
            'gr_history'     => $grList,
        ]);
    }

    return view('humanresources.procurement.mobile-po-good-received', compact('poReceiveList', 'userObj'));
}
    /**
 * Menampilkan halaman Approval Purchase Order Berjenjang
 */
public function approvalPOInbox(Request $request)
{
    $user = Auth::user();
    $username = $user->username ?? $user->Username ?? session('username');

    // 1. Ambil Policy berdasarkan Request_Type dan Status = 'ACTIVE'
    $policies = DB::table('approval_policy')
        ->whereIn('Request_Type', ['PURCHASE_ORDER', 'PO', 'PROCUREMENT'])
        ->where('Status', 'ACTIVE')
        ->orderBy('Policy_Code')
        ->get()
        ->map(function ($policy) {
            $policy->steps = DB::table('approval_policy_step')
                ->where('Policy_ID', $policy->ID)
                ->orderBy('Stage_Order')
                ->orderBy('Level_Number')
                ->get();
            return $policy;
        });

    // 2. Ambil Task Approval PO sesuai kolom asli approval_request & purchase_order
    $tasks = DB::table('approval_request_task as t')
        ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
        ->leftJoin('purchase_order as po', 'po.PO_No', '=', 'r.Request_No')
        ->leftJoin('approval_policy as p', 'p.ID', '=', 'r.Policy_ID')
        ->leftJoin('users as u', 'u.id', '=', 'r.Requester_Id')
        ->whereIn('r.Request_Type', ['PURCHASE_ORDER', 'PO'])
        ->select(
            't.ID as task_id',
            't.Request_ID as request_id',
            't.Stage_Order as stage',
            't.Level_Number as level',
            't.Approval_Mode as mode',
            't.Status as task_status',
            't.Position_Code as position_code',
            't.Position_Name as position_name',
            'r.Request_No as po_no',
            'r.Request_Type as type',
            DB::raw("COALESCE(u.Name, u.username, r.Requester_Id) as requester"),
            'r.Location as location',
            DB::raw('COALESCE(r.Submitted_At, r.Created_At) as submitted_at'),
            'r.Revision_No as revision_no',
            'r.Status as request_status',
            'p.Policy_Code as policy_code',
            'p.Policy_Name as policy_name',
            'po.PR_No as pr_no',
            'po.Supplier as supplier',
            DB::raw('COALESCE(po.Department, r.Department) as department'),
            DB::raw('COALESCE(po.Total, r.Amount, 0) as total_amount')
        )
        ->orderByRaw("FIELD(t.Status, 'PENDING', 'WAITING', 'APPROVED', 'REJECTED')")
        ->orderByDesc('r.ID')
        ->get();

    // 3. Hitung total stage per request
    $stageCounts = DB::table('approval_request_task')
        ->select('Request_ID', DB::raw('MAX(Stage_Order) as max_stage'))
        ->groupBy('Request_ID')
        ->pluck('max_stage', 'Request_ID');

    return view('humanresources.procurement.approval-purchase-order', compact(
        'policies',
        'tasks',
        'stageCounts',
        'username'
    ));
}

/**
 * Mengambil detail lengkap Task Approval PO + Item PO + Jenjang Stage (AJAX)
 */
public function getApprovalPODetail($taskId)
{
    $task = DB::table('approval_request_task as t')
        ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
        ->leftJoin('purchase_order as po', 'po.PO_No', '=', 'r.Request_No')
        ->leftJoin('approval_policy as p', 'p.ID', '=', 'r.Policy_ID')
        ->leftJoin('users as u', 'u.id', '=', 'r.Requester_Id')
        ->where('t.ID', $taskId)
        ->select(
            't.*',
            'r.Request_No as po_no',
            'r.Request_Type as module_type',
            DB::raw("COALESCE(u.Name, u.username, r.Requester_Id) as requester"),
            'r.Department as req_department',
            'r.Location as location',
            'r.Company_ID as company',
            'r.Amount as req_amount',
            'r.Revision_No as revision_no',
            'r.Status as doc_status',
            'r.Description as description',
            DB::raw('COALESCE(r.Submitted_At, r.Created_At) as submitted_at'),
            'p.Policy_Code as policy_code',
            'p.Policy_Name as policy_name',
            'po.ID as po_id',
            'po.PR_No as pr_no',
            'po.Supplier as supplier',
            'po.Department as po_department',
            'po.Term_Payment as payment_term',
            'po.Total as po_total',
            'po.Status as po_status'
        )
        ->first();

    if (!$task) {
        return response()->json(['success' => false, 'message' => 'Task approval PO tidak ditemukan.'], 404);
    }

    $items = DB::table('purchase_order_dtl')
        ->where('PO_No', $task->po_no)
        ->get();

    $candidates = DB::table('users')
        ->when(!empty($task->Position_ID), function ($q) use ($task) {
            $q->where('Position_ID', $task->Position_ID);
        }, function ($q) use ($task) {
            $q->where('Position', $task->Position_Name);
        })
        ->select('Name as name', 'username', 'Department as dept')
        ->get();

    $allStages = DB::table('approval_request_task')
        ->where('Request_ID', $task->Request_ID)
        ->orderBy('Stage_Order')
        ->orderBy('Level_Number')
        ->get();

    return response()->json([
        'success'    => true,
        'task'       => $task,
        'items'      => $items,
        'candidates' => $candidates,
        'stages'     => $allStages,
    ]);
}

/**
 * Proses Approve / Reject / Force Approve Berjenjang
 */
public function processPOApprovalAction(Request $request)
{
    $request->validate([
        'task_id' => 'required|integer',
        'action'  => 'required|in:APPROVE,REJECT,FORCE_APPROVE',
        'remarks' => 'nullable|string|max:500',
    ]);

    $user = Auth::user();
    $username = $user->username ?? $user->Username ?? session('username') ?? 'SYSTEM';
    $notes = $request->remarks;

    DB::beginTransaction();
    try {
        $task = DB::table('approval_request_task')->where('ID', $request->task_id)->lockForUpdate()->first();
        if (!$task) {
            throw new \RuntimeException('Task approval tidak ditemukan.');
        }

        if ($task->Status !== 'PENDING') {
            throw new \RuntimeException('Task ini sudah diproses atau belum memasuki gilirannya (Status: ' . $task->Status . ').');
        }

        $approvalReq = DB::table('approval_request')->where('ID', $task->Request_ID)->lockForUpdate()->first();
        $poNo = $approvalReq->Request_No ?? null;
        $now = now();

        if ($request->action === 'REJECT') {
            DB::table('approval_request_task')->where('ID', $task->ID)->update([
                'Status'     => 'REJECTED',
                'Action'     => 'REJECT',
                'Acted_By'   => $username,
                'Acted_At'   => $now,
                'Notes'      => $notes,
                'Updated_At' => $now,
            ]);

            // Batalkan stage berikutnya yang masih PENDING / WAITING
            DB::table('approval_request_task')
                ->where('Request_ID', $task->Request_ID)
                ->whereIn('Status', ['PENDING', 'WAITING'])
                ->update([
                    'Status'     => 'CANCELLED',
                    'Updated_At' => $now,
                ]);

            DB::table('approval_request')->where('ID', $task->Request_ID)->update([
                'Status'     => 'REJECTED',
                'Updated_At' => $now,
            ]);

            if (!empty($poNo)) {
                DB::table('purchase_order')->where('PO_No', $poNo)->update([
                    'Status' => 'REJECTED',
                ]);
            }
        } elseif ($request->action === 'FORCE_APPROVE') {
            // Setujui task aktif dan seluruh sisa stage
            DB::table('approval_request_task')
                ->where('Request_ID', $task->Request_ID)
                ->whereIn('Status', ['PENDING', 'WAITING'])
                ->update([
                    'Status'     => 'APPROVED',
                    'Action'     => 'FORCE_APPROVE',
                    'Acted_By'   => $username . ' (FORCE)',
                    'Acted_At'   => $now,
                    'Notes'      => $notes ?? 'Force Approved',
                    'Updated_At' => $now,
                ]);

            DB::table('approval_request')->where('ID', $task->Request_ID)->update([
                'Status'     => 'APPROVED',
                'Updated_At' => $now,
            ]);

            if (!empty($poNo)) {
                DB::table('purchase_order')->where('PO_No', $poNo)->update([
                    'Status' => 'APPROVED',
                ]);
            }
        } else {
            // APPROVE normal bertahap
            DB::table('approval_request_task')->where('ID', $task->ID)->update([
                'Status'     => 'APPROVED',
                'Action'     => 'APPROVE',
                'Acted_By'   => $username,
                'Acted_At'   => $now,
                'Notes'      => $notes,
                'Updated_At' => $now,
            ]);

            // Cek apakah masih ada task PENDING di stage yang sama (jika mode ALL)
            $remainingSameStage = DB::table('approval_request_task')
                ->where('Request_ID', $task->Request_ID)
                ->where('Stage_Order', $task->Stage_Order)
                ->where('Status', 'PENDING')
                ->count();

            if ($remainingSameStage === 0) {
                // Cari stage berikutnya yang masih WAITING
                $nextStage = DB::table('approval_request_task')
                    ->where('Request_ID', $task->Request_ID)
                    ->where('Stage_Order', '>', $task->Stage_Order)
                    ->where('Status', 'WAITING')
                    ->min('Stage_Order');

                if ($nextStage) {
                    // Aktifkan stage berikutnya menjadi PENDING
                    DB::table('approval_request_task')
                        ->where('Request_ID', $task->Request_ID)
                        ->where('Stage_Order', $nextStage)
                        ->update([
                            'Status'     => 'PENDING',
                            'Updated_At' => $now,
                        ]);
                } else {
                    // Semua stage selesai -> dokumen resmi APPROVED
                    DB::table('approval_request')->where('ID', $task->Request_ID)->update([
                        'Status'     => 'APPROVED',
                        'Updated_At' => $now,
                    ]);

                    if (!empty($poNo)) {
                        DB::table('purchase_order')->where('PO_No', $poNo)->update([
                            'Status' => 'APPROVED',
                        ]);
                    }
                }
            }
        }

        DB::commit();
        return response()->json([
            'success' => true,
            'message' => 'Aksi ' . $request->action . ' untuk ' . ($poNo ?? 'dokumen') . ' berhasil diproses.',
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Gagal memproses approval: ' . $e->getMessage(),
        ], 422);
    }
}


private function createPOApprovalRequest(string $poNo): void
{
    $po = DB::table('purchase_order')->where('PO_No', $poNo)->first();
    if (!$po) {
        return;
    }

    // Hindari duplikasi jika sudah ada request approval untuk PO ini
    $existing = DB::table('approval_request')
        ->where('Request_No', $poNo)
        ->whereIn('Request_Type', ['PURCHASE_ORDER', 'PO'])
        ->first();
    if ($existing) {
        return;
    }

    $totalAmount = (float) ($po->Total ?? 0);
    $now = now();
    $userObj = Auth::user();
    $requesterId = $userObj->id ?? null;

    // Cari policy khusus PO yang sesuai nominal (Min_Amount .. Max_Amount)
    $policy = DB::table('approval_policy')
        ->whereIn('Request_Type', ['PURCHASE_ORDER', 'PO'])
        ->where('Status', 'ACTIVE')
        ->where(function ($q) use ($totalAmount) {
            $q->whereNull('Min_Amount')->orWhere('Min_Amount', '<=', $totalAmount);
        })
        ->where(function ($q) use ($totalAmount) {
            $q->whereNull('Max_Amount')->orWhere('Max_Amount', '>=', $totalAmount);
        })
        ->orderByDesc('Min_Amount')
        ->first();

    $steps = collect();
    if ($policy) {
        $steps = DB::table('approval_policy_step')
            ->where('Policy_ID', $policy->ID)
            ->orderBy('Stage_Order')
            ->orderBy('Level_Number')
            ->orderBy('ID')
            ->get();
    }

    $firstStage = $steps->first()->Stage_Order ?? 1;
    $firstLevel = $steps->first()->Level_Number ?? 1;

    $reqId = DB::table('approval_request')->insertGetId([
        'Status'       => 'PENDING',
        'Requester_Id' => $requesterId,
        'Description'  => 'Approval Purchase Order ' . $poNo . ' (Ref PR: ' . ($po->PR_No ?? '-') . ')',
        'Request_Type' => 'PURCHASE_ORDER',
        'Current_Stage'=> $firstStage,
        'Current_Level'=> $firstLevel,
        'Submitted_At' => $now,
        'Created_At'   => $now,
        'Updated_At'   => $now,
        'Request_No'   => $poNo,
        'Policy_ID'    => $policy->ID ?? null,
        'Location'     => $po->Location ?? null,
        'Department'   => $po->Department ?? null,
        'Amount'       => $totalAmount,
    ]);

    if ($steps->isNotEmpty()) {
        foreach ($steps as $st) {
            $isActiveStage = ((int) $st->Stage_Order === (int) $firstStage);
            DB::table('approval_request_task')->insert([
                'Request_ID'    => $reqId,
                'Stage_Order'   => $st->Stage_Order,
                'Level_Number'  => $st->Level_Number,
                'Position_ID'   => $st->Position_ID ?? null,
                'Position_Code' => $st->Position_Code ?? null,
                'Position_Name' => $st->Label ?? ($st->Position_Code ?? 'Approver'),
                'Approval_Mode' => $st->Approval_Mode ?? 'ALL',
                'Status'        => $isActiveStage ? 'PENDING' : 'WAITING',
                'Created_At'    => $now,
            ]);
        }
    } else {
        // Fallback 1 stage jika belum ada step policy PO di database
        DB::table('approval_request_task')->insert([
            'Request_ID'    => $reqId,
            'Stage_Order'   => 1,
            'Level_Number'  => 1,
            'Position_Name' => 'Manager / Approver PO',
            'Approval_Mode' => 'ALL',
            'Status'        => 'PENDING',
            'Created_At'    => $now,
        ]);
    }
}

/**
 * Menampilkan halaman Approval Purchase Request (PR) Berjenjang
 */
public function approvalPRInbox(Request $request)
{
    $user = Auth::user();
    $username = $user->username ?? $user->Username ?? session('username');

    // 1. Ambil daftar Policy PR aktif beserta step jenjangnya
    $policies = DB::table('approval_policy')
        ->where(function ($q) {
            $q->whereIn('Request_Type', ['PURCHASE_REQUEST', 'PR', 'PROCUREMENT'])
              ->orWhereRaw("Policy_Code COLLATE utf8mb4_unicode_ci LIKE 'PR\\_%' COLLATE utf8mb4_unicode_ci");
        })
        ->where('Status', 'ACTIVE')
        ->orderBy('Policy_Code')
        ->get()
        ->map(function ($policy) {
            $policy->steps = DB::table('approval_policy_step')
                ->where('Policy_ID', $policy->ID)
                ->orderBy('Stage_Order')
                ->orderBy('Level_Number')
                ->orderBy('ID')
                ->get();
            return $policy;
        });

    // 2. Ambil PR yang statusnya masih PENDING (diurutkan dari yang paling baru dibuat)
    $pendingPRs = DB::table('purchase_request')
        ->whereRaw("UPPER(COALESCE(Status, '')) = 'PENDING'")
        ->orderByDesc('Created_Date')
        ->orderByDesc('ID')
        ->get();

    $prNos = $pendingPRs->pluck('PR_No')->filter()->unique()->values()->all();

    // Ambil task approval aktif/pending terkait PR tersebut jika ada
    $tasksByPrNo = !empty($prNos)
        ? DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->whereIn('r.Request_No', $prNos)
            ->orderBy('t.Stage_Order')
            ->orderBy('t.Level_Number')
            ->orderBy('t.ID')
            ->select(
                't.ID as task_id',
                't.Request_ID as request_id',
                't.Stage_Order as stage',
                't.Level_Number as level',
                't.Approval_Mode as mode',
                't.Status as task_status',
                't.Position_ID as position_id',
                't.Position_Code as position_code',
                't.Position_Name as position_name',
                'r.Request_No as pr_no',
                'r.Policy_ID as policy_id'
            )
            ->get()
            ->groupBy('pr_no')
        : collect();

    $policyIds = $tasksByPrNo->flatten(1)->pluck('policy_id')->filter()->unique()->values()->all();
    $policiesById = !empty($policyIds)
        ? DB::table('approval_policy')->whereIn('ID', $policyIds)->get()->keyBy('ID')
        : collect();

    $tasks = $pendingPRs->map(function ($pr, $index) use ($tasksByPrNo, $policiesById) {
        $prTasks = $tasksByPrNo->get($pr->PR_No, collect());
        $activeTask = $prTasks->firstWhere('task_status', 'PENDING') ?? $prTasks->first();
        $pol = ($activeTask && !empty($activeTask->policy_id))
            ? $policiesById->get($activeTask->policy_id)
            : null;

        return (object) [
            'task_id'         => $activeTask->task_id ?? ($pr->ID ?? ($index + 1)),
            'request_id'      => $activeTask->request_id ?? ($pr->Approval_Request_ID ?? $pr->ID),
            'pr_id'           => $pr->ID,
            'pr_no'           => $pr->PR_No,
            'po_no'           => $pr->PR_No, // kompatibel dengan Blade lama jika memanggil $t->po_no
            'supplier'        => $pr->Supplier_Name ?: ($pr->PIC ?: '-'),
            'requester'       => $pr->PIC ?: ($pr->User_Request_Name ?: ($pr->Request_By ?: ($pr->Created_By ?: '-'))),
            'department'      => $pr->Department ?: ($pr->Department_Request ?: '-'),
            'location'        => $pr->Location ?: '-',
            'urgency'         => $pr->Urgency ?: '-',
            'hope_date'       => $pr->Hope_Date,
            'pr_date'         => $pr->PR_Date,
            'submitted_at'    => $pr->Created_Date,
            'total_amount'    => (float) ($pr->Estimated_Budget ?? 0),
            'estimated_budget'=> (float) ($pr->Estimated_Budget ?? 0),
            'pr_status'       => $pr->Status ?: 'PENDING',
            'task_status'     => $activeTask->task_status ?? ($pr->Status ?: 'PENDING'),
            'stage'           => $activeTask->stage ?? 1,
            'level'           => $activeTask->level ?? 1,
            'mode'            => $activeTask->mode ?? 'ALL',
            'position_id'     => $activeTask->position_id ?? null,
            'position_code'   => $activeTask->position_code ?? 'PR',
            'position_name'   => $activeTask->position_name ?? 'PR Approval',
            'policy_code'     => $pol->Policy_Code ?? 'PR-STD',
            'policy_name'     => $pol->Policy_Name ?? 'Purchase Request Approval',
        ];
    });

    // 3. Hitung total stage per request untuk tampilan "Stage 1/3"
    $requestIds = $tasks->pluck('request_id')->filter()->unique()->values()->all();
    $stageCounts = !empty($requestIds)
        ? DB::table('approval_request_task')
            ->whereIn('Request_ID', $requestIds)
            ->select('Request_ID', DB::raw('MAX(Stage_Order) as max_stage'))
            ->groupBy('Request_ID')
            ->pluck('max_stage', 'Request_ID')
        : collect();

    return view('humanresources.procurement.approval-purchase-request', compact(
        'policies',
        'tasks',
        'stageCounts',
        'username'
    ));
}

public function getApprovalPRDetail(Request $request, $id = null)
{
    try {
        $taskId = $id ?? $request->input('task_id');

        if (empty($taskId)) {
            return response()->json([
                'success' => false,
                'message' => 'Task ID wajib diisi.'
            ], 422);
        }

        $row = DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->where('t.ID', $taskId)
            ->select(
                't.*',
                'r.Request_No as pr_no',
                'r.Request_Type as module_type',
                'r.Requester_Id as requester_id',
                'r.Department as req_department',
                'r.Location as location',
                'r.Company_ID as company',
                'r.Amount as req_amount',
                'r.Revision_No as revision_no',
                'r.Status as doc_status',
                'r.Description as description',
                'r.Policy_Snapshot as policy_snapshot',
                'r.Submitted_At',
                'r.Created_At',
                'r.Policy_ID as policy_id'
            )
            ->first();

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Task approval PR tidak ditemukan.'
            ], 404);
        }

        $pr = !empty($row->pr_no)
            ? DB::table('purchase_request')
                ->whereRaw("TRIM(PR_No) COLLATE utf8mb4_unicode_ci = ?", [trim((string) $row->pr_no)])
                ->first()
            : null;

        $pol = !empty($row->policy_id)
            ? DB::table('approval_policy')->where('ID', $row->policy_id)->first()
            : null;

        $usr = null;
        if (!empty($row->requester_id)) {
            $usr = is_numeric($row->requester_id)
                ? DB::table('users')->where('id', $row->requester_id)->first()
                : DB::table('users')
                    ->whereRaw("username COLLATE utf8mb4_unicode_ci = ?", [(string) $row->requester_id])
                    ->first();
        }

        $prArr  = $pr ? (array) $pr : [];
        $usrArr = $usr ? (array) $usr : [];

        $requesterName = ($usrArr['Name'] ?? $usrArr['name'] ?? $usrArr['username'] ?? null)
            ?? ($prArr['PIC'] ?? $prArr['Created_By'] ?? $row->requester_id ?? '-');

        $prNoClean = trim((string) ($prArr['PR_No'] ?? $row->pr_no ?? ''));

        $task = (object) array_merge((array) $row, [
            'pr_no'         => $prNoClean ?: '-',
            'po_no'         => $prNoClean ?: '-',
            'supplier'      => $prArr['Supplier_Name'] ?? ($prArr['PIC'] ?? '-'),
            'requester'     => $requesterName,
            'department'    => $prArr['Department'] ?? ($row->req_department ?? '-'),
            'payment_term'  => $prArr['Urgency'] ?? '-',
            'budget_amount' => isset($prArr['Estimated_Budget']) ? (float) $prArr['Estimated_Budget'] : (float) ($row->req_amount ?? 0),
            'pr_status'     => $prArr['Status'] ?? ($row->doc_status ?? 'PENDING'),
            'policy_code'   => $pol->Policy_Code ?? 'PR-STD',
            'policy_name'   => $pol->Policy_Name ?? 'Purchase Request Approval',
            'submitted_at'  => $prArr['Created_Date'] ?? ($row->Submitted_At ?: $row->Created_At),
            'description'   => $prArr['Note'] ?? ($row->description ?? '-'),
        ]);

        // Ambil item PR + data lengkap master produk dari tabel `product_code`
        $items = collect();
        if ($prNoClean !== '') {
            $items = DB::table('purchase_request_dtl as d')
                ->leftJoin(
                    'product_code as pc',
                    DB::raw('TRIM(d.Product_Code) COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('TRIM(pc.Product_Code) COLLATE utf8mb4_unicode_ci')
                )
                ->whereRaw("TRIM(d.PR_No) COLLATE utf8mb4_unicode_ci = ?", [$prNoClean])
                ->orderBy('d.ID', 'ASC')
                ->select(
                    'd.*',
                    'pc.ID as Master_Product_ID',
                    'pc.Main_Category',
                    'pc.Category',
                    'pc.Supplies_Type',
                    'pc.Description as Master_Description',
                    'pc.Specification as Master_Specification',
                    'pc.Product_Brand',
                    'pc.Product_Type',
                    'pc.Unit_Name as Master_Unit_Name',
                    'pc.UnitPrice as Master_UnitPrice',
                    'pc.Price as Master_Price',
                    'pc.Qty_Stock',
                    'pc.Location as Master_Location',
                    'pc.status_Muncul',
                    'pc.Status as Master_Status'
                )
                ->get();
        }

        // Ambil user yang berhak/kandidat approval untuk tahap/posisi ini
        $posName = trim((string) ($task->Position_Name ?? ''));
        $posCode = trim((string) ($task->Position_Code ?? ''));

        $q = DB::table('users')
            ->select(
                'id',
                'Name as name',
                'username',
                'Position as position',
                'Department as dept',
                'Location as location',
                'PR_Approval as pr_approval',
                'BP_Approval as bp_approval',
                'Status as status'
            )
            ->where(function ($stQ) {
                $stQ->whereNull('Status')
                    ->orWhereNotIn(DB::raw("UPPER(TRIM(Status))"), ['0', 'INACTIVE', 'NONACTIVE', 'DISABLED', 'RESIGN', 'DELETED', '']);
            });

        $hasSpecificFilter = false;
        $q->where(function ($sub) use ($posName, $posCode, &$hasSpecificFilter) {
            if ($posName !== '' && strtoupper($posName) !== 'PR APPROVAL') {
                $hasSpecificFilter = true;
                $sub->orWhereRaw("UPPER(TRIM(Position)) = ?", [strtoupper($posName)]);
            }
            if ($posCode !== '' && strtoupper($posCode) !== 'PR') {
                $hasSpecificFilter = true;
                $sub->orWhereRaw("UPPER(TRIM(Position)) = ?", [strtoupper($posCode)])
                    ->orWhereRaw("UPPER(TRIM(Position)) LIKE ?", [strtoupper($posCode) . ' %'])
                    ->orWhereRaw("UPPER(TRIM(Position)) LIKE ?", ['% - ' . strtoupper($posCode)])
                    ->orWhereRaw("UPPER(TRIM(Position)) LIKE ?", ['%(' . strtoupper($posCode) . ')%']);
            }
        });

        if (!$hasSpecificFilter) {
            $q->whereIn(
                DB::raw("UPPER(TRIM(COALESCE(PR_Approval, '')))"),
                ['1', 'Y', 'YES', 'TRUE', 'ACTIVE', 'APPROVE', 'APPROVED']
            );
        }

        $candidates = $q->orderBy('Name', 'ASC')->get();

        // Ambil seluruh task yang sudah ada pada request ini
        $allStages = DB::table('approval_request_task')
            ->where('Request_ID', $task->Request_ID)
            ->orderBy('Stage_Order')
            ->orderBy('Level_Number')
            ->orderBy('ID')
            ->get();

        // Ambil daftar step konfigurasi dari approval_policy_step (atau Policy_Snapshot)
        // Ambil daftar step konfigurasi dari approval_policy_step (atau Policy_Snapshot)
        $policySteps = collect();
        if (!empty($row->policy_id)) {
            try {
                $mpCols = Schema::hasTable('master_position') ? Schema::getColumnListing('master_position') : [];
                $posNameCol = in_array('Position_Name', $mpCols) ? 'mp.Position_Name'
                    : (in_array('Position', $mpCols) ? 'mp.Position'
                    : (in_array('Name', $mpCols) ? 'mp.Name' : null));
                $posCodeCol = in_array('Position_Code', $mpCols) ? 'mp.Position_Code'
                    : (in_array('Code', $mpCols) ? 'mp.Code' : null);

                $qSteps = DB::table('approval_policy_step as s')
                    ->where('s.Policy_ID', $row->policy_id)
                    ->orderBy('s.Stage_Order', 'ASC')
                    ->orderBy('s.Level_Number', 'ASC')
                    ->orderBy('s.ID', 'ASC');

                $selects = ['s.*'];
                if ($posNameCol && Schema::hasColumn('approval_policy_step', 'Position_ID')) {
                    $qSteps->leftJoin('master_position as mp', 'mp.ID', '=', 's.Position_ID');
                    $selects[] = DB::raw("{$posNameCol} as Master_Position_Name");
                }
                if ($posCodeCol && Schema::hasColumn('approval_policy_step', 'Position_ID')) {
                    $selects[] = DB::raw("{$posCodeCol} as Master_Position_Code");
                }

                $policySteps = $qSteps->select($selects)->get();
            } catch (\Throwable $stepErr) {
                $policySteps = DB::table('approval_policy_step')
                    ->where('Policy_ID', $row->policy_id)
                    ->orderBy('Stage_Order', 'ASC')
                    ->orderBy('Level_Number', 'ASC')
                    ->orderBy('ID', 'ASC')
                    ->get();
            }
        }

        if ($policySteps->isEmpty() && !empty($row->policy_snapshot)) {
            $snap = json_decode($row->policy_snapshot, true);
            $snapSteps = $snap['steps'] ?? ($snap['policy_steps'] ?? []);
            if (is_array($snapSteps) && count($snapSteps) > 0) {
                $policySteps = collect($snapSteps)->map(function ($st) {
                    return (object) $st;
                });
            }
        }

        // Susun Jenjang Approval Lengkap (gabungan step policy + task aktual)
        $approvalMatrix = [];
        $usedTaskIds = [];

        foreach ($policySteps as $st) {
            $stageOrder = (int) ($st->Stage_Order ?? $st->stage_order ?? 1);
            $levelNum   = (int) ($st->Level_Number ?? $st->level_number ?? $stageOrder);
            $stepPosId  = $st->Position_ID ?? $st->position_id ?? null;
            $stepPosCode = trim((string) ($st->Position_Code ?? $st->position_code ?? $st->Master_Position_Code ?? ''));
            $stepPosName = trim((string) ($st->Label ?? $st->label ?? $st->Master_Position_Name ?? $st->Position_Name ?? ($stepPosCode ?: 'Approver')));

            // Cari task yang cocok dengan stage / posisi step ini
            $matchedTask = $allStages->first(function ($tItem) use ($stageOrder, $stepPosId, $stepPosCode, $usedTaskIds) {
                if (in_array($tItem->ID, $usedTaskIds, true)) {
                    return false;
                }
                if ((int) ($tItem->Stage_Order ?? 1) !== $stageOrder) {
                    return false;
                }
                if (!empty($stepPosId) && !empty($tItem->Position_ID) && (int) $stepPosId === (int) $tItem->Position_ID) {
                    return true;
                }
                if ($stepPosCode !== '' && !empty($tItem->Position_Code) && strtoupper(trim((string) $tItem->Position_Code)) === strtoupper($stepPosCode)) {
                    return true;
                }
                return true;
            });

            if ($matchedTask) {
                $usedTaskIds[] = $matchedTask->ID;
                $rawStatus = strtoupper(trim((string) ($matchedTask->Status ?? 'PENDING')));
                $actor = trim((string) ($matchedTask->Acted_By ?? ''));
                $actedAt = $matchedTask->Acted_At ?? null;
                $notes = trim((string) ($matchedTask->Notes ?? ''));

                if ($rawStatus === 'PENDING') {
                    $statusLabel = 'Menunggu Approval';
                    $statusBadge = 'PENDING';
                    $executorText = $actor !== '' ? $actor : ('Kandidat: ' . ($task->Position_Name ?? $stepPosName));
                    $noteText = $notes !== '' ? $notes : '-';
                } elseif ($rawStatus === 'WAITING') {
                    $statusLabel = 'Menunggu Giliran';
                    $statusBadge = 'WAITING';
                    $executorText = $actor !== '' ? $actor : '-';
                    $noteText = $notes !== '' ? $notes : 'Menunggu persetujuan tahap sebelumnya.';
                } elseif (in_array($rawStatus, ['APPROVED', 'AUTO_APPROVED'], true)) {
                    $statusLabel = $rawStatus === 'AUTO_APPROVED' ? 'Auto Approved' : 'Disetujui';
                    $statusBadge = 'APPROVED';
                    $executorText = $actor !== '' ? $actor : '-';
                    $noteText = $notes !== '' ? $notes : '-';
                } elseif ($rawStatus === 'REJECTED') {
                    $statusLabel = 'Ditolak';
                    $statusBadge = 'REJECTED';
                    $executorText = $actor !== '' ? $actor : '-';
                    $noteText = $notes !== '' ? $notes : '-';
                } elseif (in_array($rawStatus, ['SKIPPED', 'CANCELLED'], true)) {
                    $statusLabel = $rawStatus === 'SKIPPED' ? 'Dilewati' : 'Dibatalkan';
                    $statusBadge = $rawStatus;
                    $executorText = $actor !== '' ? $actor : '-';
                    $noteText = $notes !== '' ? $notes : '-';
                } else {
                    $statusLabel = $rawStatus ?: 'Menunggu Giliran';
                    $statusBadge = $rawStatus ?: 'WAITING';
                    $executorText = $actor !== '' ? $actor : '-';
                    $noteText = $notes !== '' ? $notes : '-';
                }

                $approvalMatrix[] = [
                    'stage_order'   => $stageOrder,
                    'level_number'  => $levelNum,
                    'stage_label'   => 'Stage ' . $stageOrder,
                    'position_name' => trim((string) ($matchedTask->Position_Name ?? '')) ?: $stepPosName,
                    'position_code' => trim((string) ($matchedTask->Position_Code ?? '')) ?: $stepPosCode,
                    'status_raw'    => $rawStatus,
                    'status_badge'  => $statusBadge,
                    'status_label'  => $statusLabel,
                    'executor'      => $executorText,
                    'acted_at'      => $actedAt,
                    'notes'         => $noteText,
                ];
            } else {
                // Stage mendatang yang belum memiliki task di `approval_request_task`
                $approvalMatrix[] = [
                    'stage_order'   => $stageOrder,
                    'level_number'  => $levelNum,
                    'stage_label'   => 'Stage ' . $stageOrder,
                    'position_name' => $stepPosName,
                    'position_code' => $stepPosCode,
                    'status_raw'    => 'WAITING',
                    'status_badge'  => 'WAITING',
                    'status_label'  => 'Menunggu Giliran',
                    'executor'      => '-',
                    'acted_at'      => null,
                    'notes'         => 'Menunggu persetujuan tahap sebelumnya.',
                ];
            }
        }

        // Jika ada task di `approval_request_task` yang belum masuk (misal tanpa policy step)
        foreach ($allStages as $tItem) {
            if (!in_array($tItem->ID, $usedTaskIds, true)) {
                $stageOrder = (int) ($tItem->Stage_Order ?? 1);
                $rawStatus = strtoupper(trim((string) ($tItem->Status ?? 'PENDING')));
                $actor = trim((string) ($tItem->Acted_By ?? ''));
                $notes = trim((string) ($tItem->Notes ?? ''));

                $statusMap = [
                    'PENDING'       => 'Menunggu Approval',
                    'WAITING'       => 'Menunggu Giliran',
                    'APPROVED'      => 'Disetujui',
                    'AUTO_APPROVED' => 'Auto Approved',
                    'REJECTED'      => 'Ditolak',
                    'SKIPPED'       => 'Dilewati',
                    'CANCELLED'     => 'Dibatalkan',
                ];

                $approvalMatrix[] = [
                    'stage_order'   => $stageOrder,
                    'level_number'  => (int) ($tItem->Level_Number ?? $stageOrder),
                    'stage_label'   => 'Stage ' . $stageOrder,
                    'position_name' => trim((string) ($tItem->Position_Name ?? 'Approver')),
                    'position_code' => trim((string) ($tItem->Position_Code ?? '')),
                    'status_raw'    => $rawStatus,
                    'status_badge'  => in_array($rawStatus, ['APPROVED', 'AUTO_APPROVED'], true) ? 'APPROVED' : $rawStatus,
                    'status_label'  => $statusMap[$rawStatus] ?? $rawStatus,
                    'executor'      => $actor !== '' ? $actor : ($rawStatus === 'PENDING' ? 'Kandidat: ' . ($tItem->Position_Name ?? '-') : '-'),
                    'acted_at'      => $tItem->Acted_At ?? null,
                    'notes'         => $notes !== '' ? $notes : ($rawStatus === 'WAITING' ? 'Menunggu persetujuan tahap sebelumnya.' : '-'),
                ];
            }
        }

        usort($approvalMatrix, function ($a, $b) {
            if ($a['stage_order'] === $b['stage_order']) {
                return $a['level_number'] <=> $b['level_number'];
            }
            return $a['stage_order'] <=> $b['stage_order'];
        });

        // Ambil audit trail dari `approval_log` jika tabel tersedia
        $auditLogs = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('approval_log')) {
                $auditLogs = DB::table('approval_log')
                    ->where('Request_ID', $task->Request_ID)
                    ->orderBy('ID', 'ASC')
                    ->get()
                    ->map(function ($log) {
                        $arr = (array) $log;
                        return [
                            'time'   => $arr['Created_At'] ?? ($arr['Acted_At'] ?? ($arr['Timestamp'] ?? '-')),
                            'actor'  => $arr['Actor_Name'] ?? ($arr['Acted_By'] ?? ($arr['User_Id'] ?? 'SYSTEM')),
                            'action' => strtoupper((string) ($arr['Action'] ?? ($arr['Event_Type'] ?? 'ACTION'))),
                            'stage'  => isset($arr['Stage_Order']) && $arr['Stage_Order'] !== null && $arr['Stage_Order'] !== ''
                                ? 'Stage ' . $arr['Stage_Order']
                                : 'Stage -',
                            'notes'  => $arr['Notes'] ?? ($arr['Remarks'] ?? ($arr['Description'] ?? '-')),
                        ];
                    })
                    ->values()
                    ->all();
            }
        } catch (\Throwable $ignoreLogErr) {
            $auditLogs = [];
        }

        return response()->json([
            'success'         => true,
            'task'            => $task,
            'items'           => $items,
            'candidates'      => $candidates,
            'stages'          => $allStages,
            'approval_matrix' => $approvalMatrix,
            'audit_logs'      => $auditLogs,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
        ], 500);
    }
    }

    public function grantSelfApprovalException(Request $request)
{
    $validated = $request->validate([
        'task_id'    => 'required',
        'reason'     => 'required|string|max:500',
        'expires_at' => 'nullable|date',
    ]);

    $authUser = auth()->user();
    if (!$authUser) {
        return response()->json([
            'success' => false,
            'message' => 'Sesi login tidak ditemukan.'
        ], 401);
    }

    $actorName = trim((string) ($authUser->Name ?? $authUser->name ?? $authUser->username ?? 'ADMINISTRATOR'));

    $task = DB::table('approval_request_task')->where('ID', $validated['task_id'])->first();
    if (!$task) {
        return response()->json([
            'success' => false,
            'message' => 'Task approval tidak ditemukan.'
        ], 404);
    }

    $expiresAt = !empty($validated['expires_at'])
        ? \Carbon\Carbon::parse($validated['expires_at'])
        : null;

    if (Schema::hasTable('approval_self_approval_exception')) {
        DB::table('approval_self_approval_exception')->insert([
            'Request_ID' => $task->Request_ID,
            'Task_ID'    => $task->ID,
            'Reason'     => trim($validated['reason']),
            'Expires_At' => $expiresAt ? $expiresAt->format('Y-m-d H:i:s') : null,
            'Granted_By' => $actorName,
            'Created_At' => now(),
        ]);
    }

    if (Schema::hasTable('approval_log')) {
        $expText = $expiresAt ? ' (Berlaku s/d ' . $expiresAt->format('Y-m-d H:i') . ')' : ' (Tanpa batas waktu)';
        DB::table('approval_log')->insert([
            'Request_ID' => $task->Request_ID,
            'Stage_Order'=> $task->Stage_Order ?? null,
            'Actor_Name' => strtoupper($actorName),
            'Action'     => 'GRANT_EXCEPTION',
            'Notes'      => trim($validated['reason']) . $expText,
            'Created_At' => now(),
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Self-Approval Exception berhasil diberikan.'
    ]);
}
}