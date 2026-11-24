<?php

namespace App\Http\Controllers\HCGA;

use App\Exceptions\ApprovalWorkflowException;
use App\Http\Controllers\Controller;
use App\Services\HCGA\VehicleMaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VehicleMaintenanceApprovalController extends Controller
{
    /** @var VehicleMaintenanceService */
    protected $maintenanceService;

    public function __construct(VehicleMaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * Tampilan utama daftar approval perawatan kendaraan.
     */
    public function index()
    {
        return view('humanresources.vehicle-maintenance.approval.index');
    }

    /**
     * XML grid daftar pengajuan WAITING_APPROVAL yang sesuai dengan kandidat ApprovalWorkflowService.
     */
    public function data()
    {
        $httpAccept = $_SERVER['HTTP_ACCEPT'] ?? 'text/xml';
        if (!headers_sent() && stristr($httpAccept, 'application/xhtml+xml')) {
            header('Content-type: application/xhtml+xml');
        } elseif (!headers_sent()) {
            header('Content-type: text/xml');
        }
        echo('<?xml version="1.0" encoding="iso-8859-1"?>' . "\n");

        $results = $this->maintenanceService->listPendingApprovalsForUser(Auth::user());
        $nomor = 1;

        echo '<rows>';
        foreach ($results as $Fetch) {
            echo '<row id="' . $Fetch->id . '">';
            echo '<cell>' . $nomor . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $Fetch->No_Trx) . '</cell>';
            echo '<cell>' . htmlspecialchars(ucfirst((string) $Fetch->Jenis_Perawatan)) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $Fetch->Asset_No) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $Fetch->Product_Description) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $Fetch->Plat_Nomor) . '</cell>';
            echo '<cell>' . htmlspecialchars(number_format((float) ($Fetch->Estimasi_Biaya ?? 0), 0, ',', '.')) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $Fetch->Status) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) ($Fetch->Nama_Pengaju ?? '')) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) ($Fetch->Dept_Pengaju ?? '')) . '</cell>';
            echo '</row>';
            $nomor++;
        }
        echo '</rows>';
    }

    /**
     * Detail permohonan perawatan kendaraan beserta tombol Approve / Reject dan timeline.
     */
    public function detail(Request $request)
    {
        $id = (int) $request->input('ID');
        $detail = $this->maintenanceService->getDetailWithTimeline($id, Auth::user());
        if (!$detail) {
            abort(404);
        }

        $data = $detail['data'];
        $timeline = $detail['timeline'];
        $canApprove = $detail['canApprove'];
        $canForceApprove = $detail['canForceApprove'];

        $approver = DB::table('users')
            ->where('username', Auth::user()->username)
            ->select('Name', 'Department', 'Position', 'Status as UserStatus')
            ->first();

        return view(
            'humanresources.vehicle-maintenance.approval.detail',
            compact('data', 'approver', 'timeline', 'canApprove', 'canForceApprove')
        );
    }

    /**
     * Proses APPROVE / REJECT melalui ApprovalWorkflowService.
     */
    public function approve(Request $request)
    {
        $id = (int) $request->input('Data');
        $action = (string) $request->input('Action', 'APPROVE');
        $note = (string) $request->input('Note', '');

        try {
            $updated = $this->maintenanceService->actApproval($id, $action, $note, Auth::user());
            Log::info('VehicleMaintenance approval: id=' . $id . ' action=' . $action . ' newStatus=' . $updated->Status . ' by=' . Auth::user()->username);

            return response()->json([
                'status'    => 'success',
                'message'   => 'Berhasil melakukan ' . (strtoupper($action) === 'REJECT' ? 'penolakan' : 'approval') . '.',
                'newStatus' => $updated->Status,
            ]);
        } catch (ApprovalWorkflowException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getStatusCode() ?: 403);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Riwayat timeline approval.
     */
    public function approvalHistory(Request $request)
    {
        $id = (int) $request->input('ID');
        $detail = $this->maintenanceService->getDetailWithTimeline($id, Auth::user());
        if (!$detail) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'status'  => 'success',
            'history' => $detail['timeline'],
        ]);
    }
}
