<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\AssetVehicleService;
use App\Services\HCGA\VehicleDocumentReminderService;
use App\Services\HCGA\VehicleFleetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehicleManagementController extends Controller
{
    /** @var AssetVehicleService */
    protected $assetService;

    /** @var VehicleDocumentReminderService */
    protected $reminderService;

    /** @var VehicleFleetService */
    protected $fleetService;

    public function __construct(
        AssetVehicleService $assetService,
        VehicleDocumentReminderService $reminderService,
        VehicleFleetService $fleetService
    ) {
        $this->assetService = $assetService;
        $this->reminderService = $reminderService;
        $this->fleetService = $fleetService;
    }

    /**
     * Halaman utama Vehicle Management + Summary Reminder STNK & KEUR.
     */
    public function index()
    {
        try {
            $this->reminderService->syncInAppReminders(false);
        } catch (\Throwable $e) {
            // Jangan memblokir halaman jika sinkronisasi notifikasi gagal
        }

        $reminderSummary = $this->reminderService->getReminderSummary(100);

        return view('humanresources.vehicle-management.index', [
            'counts'        => $reminderSummary['counts'],
            'reminderItems' => $reminderSummary['items'],
        ]);
    }

    /**
     * Endpoint XML dhtmlXGrid khusus data kendaraan beserta status STNK & KEUR.
     */
    public function data(Request $request)
    {
        $httpAccept = $_SERVER['HTTP_ACCEPT'] ?? 'text/xml';
        if (!headers_sent() && stristr($httpAccept, 'application/xhtml+xml')) {
            header('Content-type: application/xhtml+xml');
        } elseif (!headers_sent()) {
            header('Content-type: text/xml');
        }
        echo "<?xml version='1.0' encoding='iso-8859-1' ?>\n";

        $docStatus = (string) $request->input('doc_status', 'ALL');
        $rows = $this->assetService->getVehicleGridData($docStatus);

        $nomor = 1;
        echo '<rows>';
        foreach ($rows as $row) {
            $v = $row['vehicle'];
            $stnk1 = $row['stnk_1'];
            $stnk5 = $row['stnk_5'];
            $keur  = $row['keur'];
            $overall = $row['overall'];

            $vehicleLabel = trim(($v->Unit_Name ?: $v->Product_Description ?: '') . ($v->Product_Brand ? ' (' . $v->Product_Brand . ')' : ''));

            echo '<row id="' . (int) $v->ID . '">';
            echo '<cell>' . $nomor . '</cell>';
            echo '<cell>' . $this->xml($v->Asset_No ?? '') . '</cell>';
            echo '<cell>' . $this->xml($v->Registration_No ?? '') . '</cell>';
            echo '<cell>' . $this->xml($vehicleLabel) . '</cell>';
            echo '<cell>' . $this->xml($v->Asset_Type ?? '') . '</cell>';
            echo '<cell>' . $this->xml($v->Asset_Location ?? '') . '</cell>';
            echo '<cell>' . $this->xml($v->Asset_Location_Department ?? '') . '</cell>';
            echo '<cell>' . $this->xml($v->User_Responsible ?? '') . '</cell>';
            echo '<cell>' . $this->xml($v->KM_Saat_Ini ?? '0') . '</cell>';
            echo '<cell>' . $this->xml($stnk1['due_date'] ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($stnk1['label']) . '</cell>';
            echo '<cell>' . $this->xml($stnk5['due_date'] ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($stnk5['label']) . '</cell>';
            echo '<cell>' . $this->xml($keur['due_date'] ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($keur['label']) . '</cell>';
            echo '<cell>' . $this->xml($overall['status']) . '</cell>';
            echo '<cell>' . $this->xml($v->Status ?? '') . '</cell>';
            echo '</row>';
            $nomor++;
        }
        echo '</rows>';
    }

    /**
     * Endpoint JSON daftar pengingat STNK & KEUR.
     */
    public function reminders(Request $request)
    {
        $limit = (int) $request->input('limit', 100);
        return response()->json([
            'status' => 'success',
            'data'   => $this->reminderService->getReminderSummary($limit),
        ]);
    }

    /**
     * Sinkronkan notifikasi In-App secara manual.
     */
    public function syncReminders()
    {
        try {
            $sent = $this->reminderService->syncInAppReminders(true);
            return response()->json([
                'status'  => 'success',
                'message' => "Sinkronisasi selesai. {$sent} notifikasi reminder dikirim.",
                'sent'    => $sent,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function taxRequests()
    {
        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'data'   => $this->fleetService->listTaxRequests(
                $user ? (int) $user->id : null,
                $user ? (string) ($user->username ?? '') : null
            ),
        ]);
    }

    public function storeTaxRequest(Request $request)
    {
        try {
            $user = Auth::user();
            $username = $user->username ?? 'system';
            $row = $this->fleetService->createTaxRequest($request->all(), $username, (int) ($user->id ?? 0));
            return response()->json(['status' => 'success', 'message' => 'Pengajuan pajak dibuat & diajukan ke Approval: ' . $row->Tax_Request_No, 'data' => $row]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function approveTaxRequest(Request $request)
    {
        try {
            $user = Auth::user();
            $username = $user->username ?? 'system';
            $row = $this->fleetService->approveTaxRequest(
                (int) $request->input('id'),
                (string) $request->input('action', 'APPROVE'),
                $username,
                $request->input('note'),
                (int) ($user->id ?? 0)
            );
            return response()->json(['status' => 'success', 'message' => 'Approval pengajuan pajak diproses.', 'data' => $row]);
        } catch (\App\Exceptions\ApprovalWorkflowException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getStatusCode() ?: 422);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function forceApproveTaxRequest(Request $request)
    {
        $user = Auth::user();
        if (!$user || strtolower((string) ($user->username ?? '')) !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Force Approve hanya diizinkan untuk username admin.'], 403);
        }

        try {
            $row = $this->fleetService->forceApproveTaxRequest(
                (int) $request->input('id'),
                (int) $user->id,
                (string) $request->input('reason', '')
            );
            return response()->json(['status' => 'success', 'message' => 'Force Approve berhasil diproses.', 'data' => $row]);
        } catch (\App\Exceptions\ApprovalWorkflowException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getStatusCode() ?: 422);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function resubmitTaxRequest(Request $request)
    {
        try {
            $user = Auth::user();
            $username = $user->username ?? 'system';
            $row = $this->fleetService->resubmitTaxRequest(
                (int) $request->input('id'),
                $request->all(),
                $username,
                (int) ($user->id ?? 0)
            );
            return response()->json(['status' => 'success', 'message' => 'Pengajuan pajak berhasil diajukan ulang.', 'data' => $row]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function payTaxRequest(Request $request)
    {
        try {
            $username = Auth::user()->username ?? 'system';
            $row = $this->fleetService->markTaxPaid(
                (int) $request->input('id'),
                (string) $request->input('paid_date', date('Y-m-d')),
                (string) $request->input('new_due_date', date('Y-m-d', strtotime('+1 year'))),
                $username,
                $request->input('bukti_bayar'),
                $request->file('bukti_file')
            );
            return response()->json(['status' => 'success', 'message' => 'Pembayaran pajak dicatat & masa berlaku diperbarui.', 'data' => $row]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function printTaxVoucher(Request $request)
    {
        $id = (int) $request->input('id', 0);
        $data = $this->fleetService->getTaxRequestForVoucher($id);
        if (!$data) {
            abort(404, 'Voucher pengajuan pajak tidak ditemukan.');
        }

        return view('humanresources.vehicle-management.voucher-tax', $data);
    }

    public function approveFleetVehicle(Request $request)
    {
        try {
            $user = Auth::user();
            $username = $user->username ?? 'system';
            $this->fleetService->approveFleetVehicle(
                (string) $request->input('Asset_No'),
                (string) $request->input('action', 'APPROVE'),
                $username,
                (int) ($user->id ?? 0),
                $request->input('note')
            );

            return response()->json(['status' => 'success', 'message' => 'Status data kendaraan diperbarui.']);
        } catch (\App\Exceptions\ApprovalWorkflowException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getStatusCode() ?: 422);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    protected function xml($value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
