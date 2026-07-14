<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use stdClass;

class EmploymentFormController extends Controller
{
    public function showList()
    {
        $employees = DB::table('employment')
            ->where('Location', auth()->user()->Location)
            ->orderBy('Name')
            ->get();
 
        // Daftar approver (bisa difilter jabatan tertentu / level manager ke atas)
        $approvers = DB::table('employment')
            ->where('Location', auth()->user()->Location)
            ->orderBy('Name')
            ->get();
 
        return view('humanresources.employment.employment-form-new', compact('employees', 'approvers'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NIK'                           => 'required|exists:employment,NIK',
            'Proposed_Jabatan'               => 'nullable|string|max:100',
            'Proposed_Divisi'                => 'nullable|string|max:100',
            'Proposed_Departemen'            => 'nullable|string|max:100',
            'Proposed_Grade'                 => 'nullable|string|max:50',
            'Proposed_Lokasi_Kerja'          => 'nullable|string|max:100',
            'Proposed_Status_Kekaryawanan'   => 'nullable|string|max:50',
            'Proposed_Marital_Status'        => 'nullable|string|max:10',
            'Proposed_Salary'                => 'nullable|numeric|min:0',
            'Proposed_Benefit'               => 'nullable|string|max:255',
            'Deskripsi'                      => 'nullable|string',
            'Effective_Date'                 => 'required|date',
            'Proposed_By'                    => 'required|exists:employment,NIK',
            'Approved_By'                    => 'required|exists:employment,NIK',
            'Verified_By'                    => 'required|exists:employment,NIK',
        ], [
            'NIK.required'         => 'Karyawan wajib dipilih.',
            'NIK.exists'           => 'Data karyawan tidak ditemukan.',
            'Effective_Date.required' => 'Tanggal berlaku wajib diisi.',
            'Proposed_By.required' => 'Pengusul wajib dipilih.',
            'Approved_By.required' => 'Penyetuju wajib dipilih.',
            'Verified_By.required' => 'Verifikator wajib dipilih.',
        ]);
 
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
 
        // ── Ambil data karyawan (kondisi "Saat ini") ────────────────────
        $employee = DB::table('employment')->where('NIK', $request->NIK)->first();
 
        if (!$employee) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Data karyawan tidak ditemukan.');
        }
 
        DB::beginTransaction();
        try {
            $id = DB::table('employment_status_change')->insertGetId([
                'NIK'                          => $request->NIK,
 
                // Snapshot kondisi saat ini (diambil dari master employee saat form disubmit)
                'Current_Jabatan'              => $employee->Jabatan,
                'Current_Divisi'               => $employee->Divisi,
                'Current_Departemen'           => $employee->Departemen,
                'Current_Grade'                => $employee->Grade,
                'Current_Lokasi_Kerja'         => $employee->Lokasi_Kerja,
                'Current_Status_Kekaryawanan'  => $employee->Status_Kekaryawanan,
                'Current_Marital_Status'       => $employee->Marital_Status,
                'Current_Salary'               => $employee->Salary,
                'Current_Benefit'              => $employee->Benefit,
 
                // Data usulan dari form
                'Proposed_Jabatan'             => $request->Proposed_Jabatan,
                'Proposed_Divisi'              => $request->Proposed_Divisi,
                'Proposed_Departemen'          => $request->Proposed_Departemen,
                'Proposed_Grade'               => $request->Proposed_Grade,
                'Proposed_Lokasi_Kerja'        => $request->Proposed_Lokasi_Kerja,
                'Proposed_Status_Kekaryawanan' => $request->Proposed_Status_Kekaryawanan,
                'Proposed_Marital_Status'      => $request->Proposed_Marital_Status,
                'Proposed_Salary'              => $request->Proposed_Salary,
                'Proposed_Benefit'             => $request->Proposed_Benefit,
 
                'Deskripsi'                    => $request->Deskripsi,
                'Effective_Date'               => $request->Effective_Date,
 
                'Proposed_By'                  => $request->Proposed_By,
                'Approved_By'                  => $request->Approved_By,
                'Verified_By'                  => $request->Verified_By,
 
                'Status'                       => 'Pending', // Pending / Approved / Rejected
                'Created_By'                   => auth()->user()->NIK ?? auth()->id(),
                'Created_At'                   => now(),
                'Updated_At'                   => now(),
            ]);
 
            DB::commit();
 
            return redirect()
                ->route('perubahan-status.index')
                ->with('success', 'Form perubahan status berhasil disimpan.');
 
        } catch (\Exception $e) {
            DB::rollBack();
 
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function printPerubahanStatus(Request $request)
    {
        // ── Ambil data karyawan & perubahan status ─────────────────────────
        $employee = DB::table('employment')->where('NIK', $request->NIK)->first();
    
        $changeRequest = DB::table('employment_status_change')
            ->where('ID', $request->Id)
            ->first();
        
        // $changeRequest = new stdClass;
    
        // Data "Saat ini" & "Usulan" - sesuaikan dengan struktur tabel Anda
        // Bisa saja satu tabel dengan kolom prefix Current_/Proposed_,
        // atau dua row terpisah. Contoh di bawah asumsi satu row dengan prefix.
        $current = (object) [
            'Jabatan'            => $changeRequest->Current_Jabatan ?? '',
            'Divisi'             => $changeRequest->Current_Divisi ?? '',
            'Departemen'         => $changeRequest->Current_Departemen ?? '',
            'Grade'              => $changeRequest->Current_Grade ?? '',
            'Lokasi_Kerja'       => $changeRequest->Current_Lokasi_Kerja ?? '',
            'Status_Kekaryawanan'=> $changeRequest->Current_Status_Kekaryawanan ?? '',
            'Marital_Status'     => $changeRequest->Current_Marital_Status ?? '',
            'Salary'             => $changeRequest->Current_Salary ?? 0,
            'Benefit'            => $changeRequest->Current_Benefit ?? '',
        ];
    
        $proposed = (object) [
            'Jabatan'            => $changeRequest->Proposed_Jabatan ?? '',
            'Divisi'             => $changeRequest->Proposed_Divisi ?? '',
            'Departemen'         => $changeRequest->Proposed_Departemen ?? '',
            'Grade'              => $changeRequest->Proposed_Grade ?? '',
            'Lokasi_Kerja'       => $changeRequest->Proposed_Lokasi_Kerja ?? '',
            'Status_Kekaryawanan'=> $changeRequest->Proposed_Status_Kekaryawanan ?? '',
            'Marital_Status'     => $changeRequest->Proposed_Marital_Status ?? '',
            'Salary'             => $changeRequest->Proposed_Salary ?? 0,
            'Benefit'            => $changeRequest->Proposed_Benefit ?? '',
        ];
    
        // ── Hitung masa kerja ───────────────────────────────────────────────
        $joinDate = \Carbon\Carbon::parse($employee->Join_Date ?? now());
        $now      = \Carbon\Carbon::now();
        $diff     = $joinDate->diff($now);
        $masaKerja = "{$diff->y} tahun {$diff->m} bulan {$diff->d} hari";
    
        // ── PIC (proposed by / approved by / verified by) ──────────────────
        $proposedBy = DB::table('employment')->where('NIK', $changeRequest->Proposed_By ?? '')->first();
        $approvedBy = DB::table('employment')->where('NIK', $changeRequest->Approved_By ?? '')->first();
        $verifiedBy = DB::table('employment')->where('NIK', $changeRequest->Verified_By ?? '')->first();
    
        // ── Render HTML dari Blade view ─────────────────────────────────────
        $html = view('humanresources.employment.prints.perubahan_status', compact(
            'employee', 'current', 'proposed', 'masaKerja',
            'proposedBy', 'approvedBy', 'verifiedBy'
        ))->with([
            'deskripsi'     => $changeRequest->Deskripsi ?? '',
            'effectiveDate' => $changeRequest->Effective_Date ?? '',
        ])->render();
    
        // ── Inisialisasi mPDF ────────────────────────────────────────────────
        $mpdf = new \Mpdf\Mpdf([
            'format'      => 'A4',
            'tempDir'     => storage_path('app/mpdf'),
            'margin_top'    => 10,
            'margin_bottom' => 10,
            'margin_left'   => 10,
            'margin_right'  => 10,
        ]);
    
        $mpdf->splitTableBorderWidth = 1;
    
        $mpdf->WriteHTML($html);
    
        // ── Output ──────────────────────────────────────────────────────────
        $filename = 'Form_Perubahan_Status_' . ($employee->NIK ?? 'draft');
        $mpdf->SetTitle($filename);
    
        // 'I' = tampil di browser, 'D' = download, 'S' = string (buat response manual)
        return response($mpdf->Output($filename . '.pdf', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '.pdf"',
        ]);
    }
}
