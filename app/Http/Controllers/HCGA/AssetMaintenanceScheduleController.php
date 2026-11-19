<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\AssetMaintenanceScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetMaintenanceScheduleController extends Controller
{
    /** @var AssetMaintenanceScheduleService */
    protected $scheduleService;

    public function __construct(AssetMaintenanceScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index()
    {
        $schedules = $this->scheduleService->listSchedules();
        $assets = DB::table('asset_list')
            ->where('Status', '!=', 'DISPOSED')
            ->select('Asset_No', 'Product_Description', 'Asset_Location')
            ->orderBy('Asset_No', 'ASC')
            ->get();
        $suppliers = DB::table('supplier')->orderBy('Company_Name', 'ASC')->get();

        return view('humanresources.asset-fixing.schedule', compact('schedules', 'assets', 'suppliers'));
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $username = $user->username ?? 'system';
            $row = $this->scheduleService->createSchedule($request->all(), $username, (int) ($user->id ?? 0));
            return response()->json(['status' => 'success', 'message' => 'Schedule dibuat & diajukan ke Approval: ' . $row->Schedule_No, 'data' => $row]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function approval(Request $request)
    {
        try {
            $user = Auth::user();
            $username = $user->username ?? 'system';
            $row = $this->scheduleService->approveByHcgaManager(
                (int) $request->input('id'),
                (string) $request->input('action', 'APPROVE'),
                $username,
                $request->input('note'),
                (int) ($user->id ?? 0)
            );
            return response()->json(['status' => 'success', 'message' => 'Approval diproses.', 'data' => $row]);
        } catch (\App\Exceptions\ApprovalWorkflowException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getStatusCode() ?: 422);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function resubmit(Request $request)
    {
        try {
            $user = Auth::user();
            $username = $user->username ?? 'system';
            $row = $this->scheduleService->resubmitSchedule(
                (int) $request->input('id'),
                $request->all(),
                $username,
                (int) ($user->id ?? 0)
            );
            return response()->json(['status' => 'success', 'message' => 'Schedule berhasil diajukan ulang.', 'data' => $row]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function confirmSchedule(Request $request)
    {
        try {
            $row = $this->scheduleService->confirmScheduleAndNotify((int) $request->input('id'));
            return response()->json(['status' => 'success', 'message' => 'Schedule tersimpan & notifikasi dikirim.', 'data' => $row]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function startExecution(Request $request)
    {
        try {
            $username = Auth::user()->username ?? 'system';
            $row = $this->scheduleService->startExecution((int) $request->input('id'), $username);
            return response()->json(['status' => 'success', 'message' => 'Eksekusi dimulai. Status aset: MAINTENANCE.', 'data' => $row]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function completeExecution(Request $request)
    {
        try {
            $username = Auth::user()->username ?? 'system';
            $executedDate = $request->input('executed_date', date('Y-m-d'));
            $row = $this->scheduleService->completeExecution((int) $request->input('id'), $executedDate, $username);
            return response()->json(['status' => 'success', 'message' => 'Perawatan selesai. Status aset kembali ACTIVE.', 'data' => $row]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
