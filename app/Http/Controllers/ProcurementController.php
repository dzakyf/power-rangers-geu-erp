<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
    public function createDetail(Request $request) {
        $rfqNo = $request->query('rfq');

        $rfq = DB::table('rfq as r')
                ->leftJoin('supplier as s', 'r.Supplier_ID', '=', 's.Supplier_ID')
                ->where('r.RFQ_No', $rfqNo)
                ->first();

        if (!$rfq) {
            return redirect()->back()->with('error', 'Data RFQ tidak ditemukan.');
        }

        $pr_items = DB::table('purchase_request_dtl')
                    ->where('PR_No', $rfq->PR_No)
                    ->get();

        // return view('procurement.qtn-create-detail', compact('rfq', 'pr_items'));
                    // Contoh di ProcurementController.php saat mereturn view create PR:
            return view('humanresources.procurement.purchase-request-create', compact('generated_pr_no', 'materials', 'departments', 'userDepartment'));
    }


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
        // Validasi input data utama dan file attachment di setiap baris item
        $request->validate([
            'pr_no'              => 'required|string',
            'pr_date'            => 'required|date',
            'department'         => 'required',
            'product_code'       => 'required|array',
            'product_code.*'     => 'required|string',
            'qty'                => 'required|array',
            'qty.*'              => 'required|numeric|min:1',
            // Validasi file attachment: opsional, namun jika diisi harus berformat aman dan max 5MB
            'attachment'         => 'nullable|array',
            'attachment.*'       => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120'
        ]);

        $totalQty = array_sum($request->qty ?? [0]);
        $username = session('username') ?? (Auth::user()->username ?? 'admin');
        $location = Auth::user()->Location ?? 'GEU-HO';

        // 1. Simpan ke Header purchase_request
        DB::table('purchase_request')->insert([
            'Location'         => $location,
            'Supplies_Type'=> 'NON-OPS',
            'PR_No'        => $request->pr_no,
            'PR_Date'      => $request->pr_date,
            'Department'   => $request->department,
            'Category'     => $request->Category,
            'Sub_Category' => $request->Sub_Category,
            'PIC'          => $request->pic_name,
            'PIC_Phone'    => $request->PIC_Phone,
            'PIC_Email'    => $request->pic_email,
            'PIC_Address' => $request->site_destination,
            'Note'         => $request->note,
            'Status'       => 'PENDING',
            'Created_By'   => session('username') ?? 'admin',
            'Created_Date' => now(),

        ]);

        // 1.1 SIMPAN HISTORY NOTE KE pr_notes_history (Jika note diisi)
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

                    // Handle upload file attachment per baris item dengan pengecekan aman
                    if ($request->hasFile("attachment.{$index}")) {
                        $file = $request->file('attachment')[$index];
                        if ($file->isValid()) {
                            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            
                            // Pastikan folder tujuan fisik tersedia
                            $destinationPath = public_path('uploads/pr_attachments');
                            if (!file_exists($destinationPath)) {
                                mkdir($destinationPath, 0777, true);
                            }

                            // Pindahkan file ke folder tujuan
                            $file->move($destinationPath, $fileName);
                            $attachmentPath = 'uploads/pr_attachments/' . $fileName;
                        }
                    }

                    DB::table('purchase_request_dtl')->insert([
                        'PR_No'        => $request->pr_no,
                        'Product_Code' => $code,
                        'Product_Desc' => $request->product_desc[$index] ?? '',
                        'Specification'=> $request->specification[$index] ?? '',
                        'Qty'          => $request->qty[$index] ?? 1,
                        'Unit'         => $request->unit[$index] ?? 'PCS',
                        'Attachment'   => $attachmentPath,
                    ]);
                }
            }
        }

        return redirect('purchase-request-list')->with('success', 'Purchase Request berhasil disimpan!');
    }

    // Menampilkan Detail PR Status PENDING
    public function detailPRPending(Request $request) {
        $prNo = $request->query('PR_No');

        $header = DB::table('purchase_request')->where('PR_No', $prNo)->first();
        $details = DB::table('purchase_request_dtl')->where('PR_No', $prNo)->get();
        
        // Ambil riwayat percakapan dari pr_notes_history
        $noteHistories = DB::table('pr_notes_history')
                            ->where('PR_No', $prNo)
                            ->orderBy('ID', 'ASC')
                            ->get();
        
        // Ambil master data agar dropdown bisa dipilih
        $materials = DB::table('product_code')->select('Product_Code', 'Description')->get();
        $departments = DB::table('department')->orderBy('Department', 'ASC')->get();

        return view('humanresources.procurement.purchase-request-detail-pending', compact('header', 'details', 'materials', 'departments', 'noteHistories'));
    }


    // Proses Approve atau Reject PR
    
    public function approvePR(Request $request) {
        $prNo     = $request->pr_no;
        $action   = $request->action; // 'APPROVED' atau 'REJECTED'
        $note     = $request->approval_note;
        $username = session('username') ?? 'admin'; // <-- Perbaikan: Definisikan $username di sini

        DB::table('purchase_request')
            ->where('PR_No', $prNo)
            ->update([
                'Status'              => $action,
                'Note'                => $note,
                'Approval_User'       => $username,
                'Approval_Date'       => now(),
                'Approval_Department' => session('department') ?? 'ICT',
            ]);

        // Simpan log aksi approval/rejection ke pr_notes_history jika ada catatan
        if (!empty(trim($note))) {
            DB::table('pr_notes_history')->insert([
                'PR_No'         => $prNo,
                'Action_Status' => $action,
                'Note'          => trim($note),
                'Created_By'    => $username,
                'Created_Date'  => now(),
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Purchase Request {$prNo} berhasil diubah menjadi {$action}!"
        ]);
    }

    
    // Proses Update / Edit PR yang Statusnya PENDING
    
    public function updatePR(Request $request) {
        $prNo = $request->pr_no;
        $username = session('username') ?? 'admin';

        // Validasi paling longgar agar tidak pernah 422
        $request->validate([
            'pr_date'      => 'required|date',
            'department'   => 'required',
            'product_code' => 'required|array',
            'product_code.*' => 'required|string',
            'qty'          => 'required|array',
            'qty.*'        => 'required|numeric|min:1',
        ]);

        $totalQty = array_sum($request->qty ?? [0]);

        // 1. Update Header purchase_request
        DB::table('purchase_request')->where('PR_No', $prNo)->update([
            'PR_Date'          => $request->pr_date,
            'Department'       => $request->department,
            'Category'         => $request->main_category ?? $request->Category ?? 'ASSET',
            'Sub_Category'     => $request->category ?? $request->Sub_Category ?? 'BARANG',
            'PIC'              => $request->pic_name ?? '',
            'PIC_Phone'        => $request->pic_phone ?? '',
            'PIC_Email'        => $request->pic_email ?? '',
            'PIC_Address'      => $request->site_destination ?? '',
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

        // 2. Hapus detail lama & masukkan kembali data detail baru
        DB::table('purchase_request_dtl')->where('PR_No', $prNo)->delete();

        if ($request->has('product_code')) {
            foreach ($request->product_code as $index => $code) {
                if (!empty($code)) {
                    $attachmentPath = $request->old_attachment[$index] ?? null;

                    // Cek upload file aman per baris
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

                    DB::table('purchase_request_dtl')->insert([
                        'PR_No'         => $prNo,
                        'Product_Code'  => $code,
                        'Product_Desc'  => $request->product_desc[$index] ?? '',
                        'Specification' => $request->specification[$index] ?? '',
                        'Qty'           => $request->qty[$index] ?? 1,
                        'Unit'          => $request->unit[$index] ?? 'PCS',
                        'Attachment'    => $attachmentPath ?? '',
                    ]);
                }
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Purchase Request {$prNo} berhasil diperbarui!"
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
}