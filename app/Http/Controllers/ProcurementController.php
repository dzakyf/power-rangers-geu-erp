<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcurementController extends Controller
{
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

        // 1. Simpan ke Header purchase_request
        DB::table('purchase_request')->insert([
            'PR_No'        => $request->pr_no,
            'PR_Date'      => $request->pr_date,
            'Department'   => $request->department,
            'Status'       => 'PENDING',
            'Created_By'   => session('username') ?? 'admin',
            'Created_Date' => now(),
        ]);

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
}