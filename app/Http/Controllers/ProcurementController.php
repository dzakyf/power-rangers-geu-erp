<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
    public function xmlOutstandingPO()
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
            ->whereIn('po.Status', ['OPEN', 'APPROVED', 'DELIVERY', 'PARTIAL'])
            ->orderBy('po.ID', 'desc')
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rows>';

        $no = 1;
        foreach ($pos as $row) {
            $vendorDisplay = $row->Company_Name
                ? $row->Supplier . ' - ' . $row->Company_Name
                : $row->Supplier;

            $xml .= '<row id="' . htmlspecialchars($row->PO_No) . '">';
            $xml .= '<cell>' . $no++ . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->PO_No) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->PO_Date ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->PR_No ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($vendorDisplay ?? '-') . '</cell>';
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
            ->select('po.*', 's.Company_Name', 's.Address as Supplier_Address', 's.Phone as Supplier_Phone')
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
            ->select('po.*', 's.Company_Name', 's.Address as Supplier_Address', 's.Phone as Supplier_Phone', 's.Email as Supplier_Email')
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

}