<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\ReimbursementMobileService;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReimbursementMobileController extends Controller
{
    /**
     * @var ReimbursementMobileService
     */
    protected $mobileService;

    /**
     * @var UploadService
     */
    protected $uploadService;

    public function __construct(ReimbursementMobileService $mobileService, UploadService $uploadService)
    {
        $this->mobileService = $mobileService;
        $this->uploadService = $uploadService;
    }

    /**
     * View Utama Mobile Webview Reimbursement (Daftar & Status)
     */
    public function mobileIndex(Request $request)
    {
        $user = Auth::user();
        $status = $request->query('status', 'ALL');
        $reimbursements = $this->mobileService->getUserReimbursementList($user->id, $status, 30);

        return view('humanresources.reimbursement.mobile.index', compact('user', 'reimbursements', 'status'));
    }

    /**
     * View Form Pengajuan Reimbursement Mobile Webview
     */
    public function mobileForm(Request $request)
    {
        $user = Auth::user();
        $departments = DB::table('master_department')->orderBy('Department', 'ASC')->get();
        $Reimbursement_No = 'RMB/' . ($user->Location ?: 'GEU-HO') . '/' . date('ymd') . '-' . date('His');

        return view('humanresources.reimbursement.mobile.form', compact('user', 'departments', 'Reimbursement_No'));
    }

    /**
     * View Detail Reimbursement Mobile Webview
     */
    public function mobileDetail(Request $request)
    {
        $user = Auth::user();
        $id = $request->query('id', $request->query('ID'));
        if (!$id) {
            abort(404, 'ID Reimbursement tidak ditemukan');
        }

        $detail = $this->mobileService->getDetail($id, $user);
        if (!$detail) {
            abort(404, 'Data Reimbursement tidak ditemukan');
        }

        return view('humanresources.reimbursement.mobile.detail', array_merge($detail, ['user' => $user]));
    }

    /**
     * View Antrean Approval Reimbursement Mobile Webview
     */
    public function mobileApproval(Request $request)
    {
        $user = Auth::user();
        $queue = $this->mobileService->getApproverQueue($user, 30);

        return view('humanresources.reimbursement.mobile.approval', compact('user', 'queue'));
    }
}

