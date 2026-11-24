<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\VehicleMaintenanceService;
use App\Services\HCGA\VehiclePmReminderService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class VehicleMaintenanceController extends Controller
{
    /** @var VehicleMaintenanceService */
    protected $maintenanceService;

    /** @var VehiclePmReminderService */
    protected $pmReminderService;

    public function __construct(
        VehicleMaintenanceService $maintenanceService,
        VehiclePmReminderService $pmReminderService
    ) {
        $this->maintenanceService = $maintenanceService;
        $this->pmReminderService = $pmReminderService;
    }

    /**
     * Tampilan utama: KPI Cards, Grid Perawatan Kendaraan, dan Tab Jadwal PM Jatuh Tempo.
     */
    public function index()
    {
        $dept = (string) (Auth::user()->Department ?? '');
        $duePmItems = $this->pmReminderService->getDuePmList('ALL');
        $duePmCount = count(array_filter($duePmItems, function ($item) {
            return !empty($item['needs_action']);
        }));
        $kpi = $this->maintenanceService->getDashboardKpi($dept, $duePmCount);

        return view('humanresources.vehicle-maintenance.index', compact('kpi', 'duePmItems'));
    }

    /**
     * Endpoint XML untuk dhtmlXGrid Daftar Perawatan Kendaraan.
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

        $dept = (string) (Auth::user()->Department ?? '');
        $results = $this->maintenanceService->listForGrid($dept);
        $nomor = 1;

        echo '<rows>';
        foreach ($results as $Fetch) {
            $biayaDisplay = $Fetch->Biaya_Aktual !== null && (float) $Fetch->Biaya_Aktual > 0
                ? number_format((float) $Fetch->Biaya_Aktual, 0, ',', '.')
                : number_format((float) ($Fetch->Estimasi_Biaya ?? 0), 0, ',', '.');

            echo '<row id="' . $Fetch->id . '">';
            echo '<cell>' . $nomor . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $Fetch->No_Trx) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $Fetch->Asset_No) . '</cell>';
            echo '<cell>' . htmlspecialchars(ucfirst((string) $Fetch->Jenis_Perawatan)) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $Fetch->Plat_Nomor) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $Fetch->Product_Description) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) ($Fetch->Kilometer_Selesai ?: $Fetch->Kilometer_Saat_Ini)) . '</cell>';
            echo '<cell>' . htmlspecialchars($biayaDisplay) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $Fetch->Status) . '</cell>';
            echo '</row>';
            $nomor++;
        }
        echo '</rows>';
    }

    /**
     * Partial/JSON refresh untuk daftar Jadwal PM Jatuh Tempo.
     */
    public function duePmData(Request $request)
    {
        $filter = (string) $request->input('filter', 'ALL');
        $duePmItems = $this->pmReminderService->getDuePmList($filter);

        return response()->json([
            'status' => 'success',
            'count'  => count($duePmItems),
            'items'  => $duePmItems,
        ]);
    }

    /**
     * Form create (partial), mendukung pre-fill dari tombol 1-klik (?asset_no=...&jenis=preventif).
     */
    public function create(Request $request)
    {
        $assetList = $this->maintenanceService->getVehicleAssets();
        $odoMap = $this->maintenanceService->getEffectiveOdometerMap($assetList->pluck('Asset_No')->all());

        foreach ($assetList as $asset) {
            if (isset($odoMap[$asset->Asset_No]) && $odoMap[$asset->Asset_No] > (int) ($asset->KM_Saat_Ini ?? 0)) {
                $asset->KM_Saat_Ini = $odoMap[$asset->Asset_No];
            }
        }

        $prefillAssetNo = trim((string) $request->input('asset_no', ''));
        $prefillJenis = strtolower(trim((string) $request->input('jenis', 'preventif')));
        if (!in_array($prefillJenis, ['preventif', 'korektif'], true)) {
            $prefillJenis = 'preventif';
        }

        return view('humanresources.vehicle-maintenance.create', compact('assetList', 'prefillAssetNo', 'prefillJenis'));
    }

    /**
     * Simpan pengajuan perawatan kendaraan (DRAFT atau langsung submit ke ApprovalWorkflowService).
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $asDraft = filter_var($request->input('as_draft', false), FILTER_VALIDATE_BOOLEAN);
            $record = $this->maintenanceService->createMaintenance($request->all(), $user, $asDraft);

            return response()->json([
                'status'  => 'success',
                'message' => $asDraft
                    ? 'Draft perawatan kendaraan berhasil disimpan (' . $record->No_Trx . ').'
                    : 'Pengajuan perawatan berhasil disimpan & diajukan ke Approval (' . $record->No_Trx . ').',
                'data'    => $record,
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('VehicleMaintenance store error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Ajukan draft atau ajukan ulang pengajuan berstatus RETURNED / REJECTED.
     */
    public function submitDraft(Request $request)
    {
        try {
            $id = (int) $request->input('id');
            $record = $this->maintenanceService->submitDraft($id, Auth::user());

            return response()->json([
                'status'  => 'success',
                'message' => 'Pengajuan berhasil dikirim ke alur Approval.',
                'data'    => $record,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mulai proses servis di bengkel (APPROVED -> IN_PROGRESS, asset_list.Status -> MAINTENANCE).
     */
    public function startExecution(Request $request)
    {
        try {
            $id = (int) $request->input('id');
            $startDate = (string) $request->input('Tanggal_Mulai_Service', date('Y-m-d'));
            $record = $this->maintenanceService->startExecution($id, $startDate, Auth::user());

            return response()->json([
                'status'  => 'success',
                'message' => 'Servis kendaraan dimulai. Status aset diperbarui menjadi MAINTENANCE.',
                'data'    => $record,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Selesaikan servis (IN_PROGRESS -> COMPLETED), upload nota, kembalikan aset ACTIVE, hitung PM berikutnya.
     */
    public function completeExecution(Request $request)
    {
        try {
            $id = (int) $request->input('id');
            $notaFile = $request->file('Bukti_Nota');
            $record = $this->maintenanceService->completeExecution($id, $request->all(), $notaFile, Auth::user());

            return response()->json([
                'status'  => 'success',
                'message' => 'Servis selesai dicatat. Status aset kembali ACTIVE & jadwal PM berikutnya diperbarui.',
                'data'    => $record,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Simpan pengaturan jadwal PM kendaraan dari modal Atur Jadwal PM.
     */
    public function updatePmSchedule(Request $request)
    {
        try {
            $assetNo = trim((string) $request->input('Asset_No', ''));
            if ($assetNo === '') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Asset_No wajib diisi.',
                ], 422);
            }

            $asset = $this->maintenanceService->updateVehiclePmSchedule($assetNo, $request->all(), Auth::user());

            return response()->json([
                'status'  => 'success',
                'message' => 'Jadwal PM kendaraan ' . $asset->Asset_No . ' berhasil diperbarui.',
                'data'    => $asset,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Detail satu record perawatan beserta timeline approval & aksi lifecycle.
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

        return view('humanresources.vehicle-maintenance.detail', compact('data', 'timeline'));
    }

    /**
     * Export daftar perawatan kendaraan ke Excel (.xlsx) menggunakan PhpSpreadsheet.
     */
    public function exportExcel(Request $request)
    {
        $dept = (string) (Auth::user()->Department ?? '');
        $rows = $this->maintenanceService->listForGrid($dept);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Vehicle Maintenance');

        $sheet->setCellValue('A1', 'LAPORAN PERAWATAN KENDARAAN (VEHICLE MAINTENANCE)');
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $sheet->setCellValue('A2', 'Diekspor pada: ' . date('d/m/Y H:i') . ' | Departemen: ' . ($dept ?: 'ALL'));
        $sheet->mergeCells('A2:L2');

        $headers = [
            'No', 'Request No', 'Tanggal Pengajuan', 'Asset No', 'Plat Nomor',
            'Nama Kendaraan', 'Jenis Perawatan', 'Kilometer Awal', 'Kilometer Selesai',
            'Estimasi Biaya (Rp)', 'Biaya Aktual (Rp)', 'Vendor / Bengkel', 'Status', 'Pengaju',
        ];

        $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
        foreach ($headers as $idx => $label) {
            $sheet->setCellValue($colLetters[$idx] . '4', $label);
        }

        $sheet->getStyle('A4:N4')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $rowIdx = 5;
        $no = 1;
        foreach ($rows as $r) {
            $sheet->setCellValue('A' . $rowIdx, $no++);
            $sheet->setCellValue('B' . $rowIdx, $r->No_Trx);
            $sheet->setCellValue('C' . $rowIdx, $r->Tanggal_Pengajuan ?: substr((string) $r->created_at, 0, 10));
            $sheet->setCellValue('D' . $rowIdx, $r->Asset_No);
            $sheet->setCellValue('E' . $rowIdx, $r->Plat_Nomor);
            $sheet->setCellValue('F' . $rowIdx, $r->Product_Description);
            $sheet->setCellValue('G' . $rowIdx, ucfirst((string) $r->Jenis_Perawatan));
            $sheet->setCellValue('H' . $rowIdx, (int) ($r->Kilometer_Saat_Ini ?? 0));
            $sheet->setCellValue('I' . $rowIdx, (int) ($r->Kilometer_Selesai ?? 0));
            $sheet->setCellValue('J' . $rowIdx, (float) ($r->Estimasi_Biaya ?? 0));
            $sheet->setCellValue('K' . $rowIdx, (float) ($r->Biaya_Aktual ?? 0));
            $sheet->setCellValue('L' . $rowIdx, $r->Vendor_Bengkel);
            $sheet->setCellValue('M' . $rowIdx, $r->Status);
            $sheet->setCellValue('N' . $rowIdx, $r->Display_Pengaju ?? $r->User_Trx);

            $sheet->getStyle("H{$rowIdx}:K{$rowIdx}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("A{$rowIdx}:N{$rowIdx}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $rowIdx++;
        }

        foreach ($colLetters as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Vehicle_Maintenance_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Cetak Form / SPK Perawatan Kendaraan (PDF) menggunakan DomPDF.
     */
    public function printSpk(Request $request)
    {
        $id = (int) $request->input('ID');
        $detail = $this->maintenanceService->getDetailWithTimeline($id, Auth::user());
        if (!$detail) {
            abort(404);
        }

        $data = $detail['data'];
        $timeline = $detail['timeline'];

        $html = view('humanresources.vehicle-maintenance.pdf.spk', compact('data', 'timeline'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeNo = preg_replace('/[^A-Za-z0-9\-_]/', '_', (string) $data->No_Trx);
        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="SPK_' . $safeNo . '.pdf"',
        ]);
    }
}
