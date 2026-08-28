<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

class ApprovalChangesController extends Controller
{
    /**
     * Creator menyetujui perubahan yang dilakukan approver
     * Endpoint: POST hrd-procurement-changes-approve
     */
    public function approve(Request $request)
    {
        try {
            DB::beginTransaction();

            $prNo = $request->input('PR_No');
            $remark = $request->input('Remark', '');
            $action = $request->input('Action', 'approve'); // approve / reject

            $creator = Auth::user();
            $LSP = $creator->Location;

            // Ambil PR
            $pr = DB::table('purchase_request')
                ->where('PR_No', $prNo)
                ->first();

            if (!$pr) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'PR tidak ditemukan'
                ], 404);
            }

            // Verifikasi: hanya creator yang bisa 승인
            if ($pr->Created_By !== $creator->username) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hanya creator PR yang dapat menyetujui perubahan'
                ], 403);
            }

            // Verifikasi status: harus CHANGES_REVIEWED_BY_CREATOR
            if ($pr->Status !== 'CHANGES_REVIEWED_BY_CREATOR') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'PR tidak dalam status CHANGES_REVIEWED_BY_CREATOR'
                ], 400);
            }

            // Tentukan status berikutnya berdasarkan Approval_Department
            if ($pr->Approval_Department === 'HCGA') {
                $nextStatus = 'APPROVAL_GA';
            } else {
                $nextStatus = 'APPROVAL_UPPER';
            }

            // Update PR
            DB::table('purchase_request')
                ->where('ID', $pr->ID)
                ->update([
                    'Status' => $nextStatus,
                    'Note' => $pr->Note . PHP_EOL . now()->format('Y-m-d H:i:s') . ' => Creator menyetujui perubahan. PR dikembalikan ke tahap approval.',
                    'Remark' => $remark,
                ]);

            // Insert log
            DB::table('purchase_request_log')->insert([
                'PR_No' => $pr->PR_No,
                'Action_By' => $creator->username,
                'Action_Date' => now(),
                'Action_Status' => 'CREATOR_APPROVED_CHANGES: PR dikembalikan ke tahap approval ' . $nextStatus,
                'Last_ID' => $pr->ID,
                'Category' => $pr->Category,
                'Sub_Category' => $pr->Sub_Category,
                'Supplies_Type' => $pr->Supplies_Type,
                'Note' => $pr->Note,
                'Reference' => $pr->Reference,
            ]);

            // Jika reject, set status ke REJECTED
            if ($action === 'reject') {
                DB::table('purchase_request')
                    ->where('ID', $pr->ID)
                    ->update([
                        'Status' => 'REJECTED_CHANGES',
                        'Note' => $pr->Note . PHP_EOL . now()->format('Y-m-d H:i:s') . ' => Creator menolak perubahan.',
                        'Remark' => $remark,
                    ]);

                DB::table('purchase_request_log')->insert([
                    'PR_No' => $pr->PR_No,
                    'Action_By' => $creator->username,
                    'Action_Date' => now(),
                    'Action_Status' => 'CREATOR_REJECTED_CHANGES: Perubahan ditolak oleh creator',
                    'Last_ID' => $pr->ID,
                    'Category' => $pr->Category,
                    'Sub_Category' => $pr->Sub_Category,
                    'Supplies_Type' => $pr->Supplies_Type,
                    'Note' => $remark,
                    'Reference' => $pr->Reference,
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Perubahan ditolak. PR dikembalikan ke approver untuk direvisi.',
                    'redirect' => '/hrd-procurement-index'
                ]);
            }

            // Kirim notifikasi ke approver berikutnya
            $approver = $this->getNextApprover($nextStatus, $pr->Approval_Department);

            if ($approver) {
                (new NotificationService)->makeNotification(
                    "PR {$pr->PR_No} Kembali ke Approval",
                    "PR {$pr->PR_No} telah disetujui oleh creator setelah perubahan data. Silakan lanjutkan approval.",
                    'approval',
                    $creator->id,
                    $approver->id
                );

                // Kirim email
                DB::insert(DB::raw("
                    INSERT INTO mail_logs (Email_To, Subject, Contents, Status, Inserted_Date, Send_Date)
                    VALUES ('{$approver->Email}', 'PR {$pr->PR_No} Kembali ke Approval', 
                    'PR {$pr->PR_No} telah disetujui oleh creator setelah perubahan data. Silakan lanjutkan approval.', 'UNSENT', NOW(), NOW())
                "));
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Perubahan disetujui. PR dikembalikan ke tahap approval ' . $nextStatus . '.',
                'redirect' => '/hrd-procurement-index'
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Tampilkan halaman konfirmasi perubahan untuk creator
     */
    public function confirmView(Request $request)
    {
        $prNo = $request->query('PR_No');

        $pr = DB::table('purchase_request')
            ->where('PR_No', $prNo)
            ->first();

        if (!$pr) {
            abort(404);
        }

        // Verifikasi creator
        if ($pr->Created_By !== Auth::user()->username) {
            abort(403, 'Hanya creator yang dapat melihat halaman ini.');
        }

        // Verifikasi status
        if ($pr->Status !== 'CHANGES_REVIEWED_BY_CREATOR') {
            return redirect('/hrd-procurement-index')
                ->with('error', 'PR tidak dalam status menunggu konfirmasi perubahan.');
        }

        // Ambil data detail
        $details = DB::table('purchase_request_dtl')
            ->where('PR_No', $prNo)
            ->get();

        // Ambil data department dan supplier untuk form
        $dept = Auth::user()->Department;
        $departmentQuery = DB::table('master_department');
        if ($dept !== 'ICT' && $dept !== 'BOD' && $dept !== 'HCGA') {
            $departmentQuery->where('Initial', $dept);
        }
        $Department = $departmentQuery->get();
        $company = DB::table('supplier')->where('Status', 'NORMAL')->get();

        // Ambil log perubahan
        $changeLogs = DB::table('purchase_request_log')
            ->where('PR_No', $prNo)
            ->where('Action_Status', 'like', '%CHANGES_DETECTED%')
            ->orderBy('Action_Date', 'DESC')
            ->get();

        return view('humanresources.procurement.changes-confirm', compact(
            'pr', 'details', 'Department', 'company', 'changeLogs'
        ));
    }

    /**
     * Dapatkan approver berikutnya berdasarkan status dan department
     */
    private function getNextApprover($status, $department)
    {
        $levelGeneral = DB::table('level_general')
            ->where('Status_Approval', 'like', "%{$status}%")
            ->first();

        if (!$levelGeneral) {
            return null;
        }

        $deptFilter = $status === 'APPROVAL_GA' ? 'HCGA' : $department;

        $approver = DB::table('organization_structure')
            ->where('Level' . $levelGeneral->Level, 1)
            ->where('Department', $deptFilter)
            ->first();

        if (!$approver) {
            return null;
        }

        return DB::table('users')
            ->where('Emp_ID', $approver->NIK)
            ->first();
    }
}
