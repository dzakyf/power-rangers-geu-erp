<?php

namespace App\Services\CNS;

use App\VesselSchedule;
use App\VesselScheduleDtl;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StuffingReportService
{
    public function generate(VesselSchedule $schedule): string
    {
        $contract = $schedule->Contract_ID
            ? DB::table('cns_buyer_contracts')->where('ID', $schedule->Contract_ID)->first()
            : null;
        $supplier = $contract && $contract->Supplier_ID
            ? DB::table('supplier')->where('ID', $contract->Supplier_ID)->first()
            : null;

        $containers = VesselScheduleDtl::active();
        if ($schedule->Order_No) {
            $containers->where('Order_No', $schedule->Order_No);
        } else {
            $containers->where('Task_ID', $schedule->Task_ID);
        }
        $containers = $containers->orderBy('ID')->get();

        $nwKg = (float) $containers->sum('Netto_KG');
        $nwMt = round($nwKg / 1000, 2);
        $gwKg = (float) $containers->sum('Gross_KG');
        $gwMt = round($gwKg / 1000, 2);
        $containerCount = $containers->count();
        $contractQty = $contract ? (float) $contract->Quantity_MT : null;
        $difference = $contractQty === null ? 'TBD' : round($nwMt - $contractQty, 2);

        $template = public_path('template_final_stuffing.xlsx');
        $spreadsheet = IOFactory::load($template);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('B4', $this->first([
            $contract ? $contract->Buyer_Name : null,
            $supplier ? $supplier->Company_Name : null,
        ]));
        $sheet->setCellValue('F4', $this->first([
            $schedule->Contract_No,
            $contract ? $contract->Contract_No : null,
        ]));
        $sheet->setCellValue('B5', $this->loadingPeriod($contract));
        $sheet->setCellValue('F5', $this->first([$schedule->DO_No]));
        $sheet->setCellValue('B6', $this->first([
            $schedule->Incoterms,
            $contract ? $contract->Incoterms : null,
        ]));
        $sheet->setCellValue('F6', $this->first([$schedule->Booking_No]));
        $sheet->setCellValue('B7', $this->first([$schedule->Vessel]));
        $sheet->setCellValue('F7', $this->formatDate($schedule->ETD));
        $sheet->setCellValue('B8', $this->first([$schedule->POD]));
        $sheet->setCellValue('F8', now()->format('d F Y H:i'));

        $sheet->setCellValue('B11', $contractQty === null ? 'TBD' : $contractQty);
        $sheet->setCellValue('D11', $nwKg);
        $sheet->setCellValue('F11', $nwMt);
        $sheet->setCellValue('H11', $containerCount);
        $sheet->setCellValue('B12', $gwKg);
        $sheet->setCellValue('D12', $gwMt);
        $sheet->setCellValue('F12', $difference);
        $sheet->setCellValue('H12', $this->first([$schedule->DO_Status]));

        $sheet->setCellValue('A16', $schedule->Checklist_Pickup_Container ? 'Sudah' : 'Belum');
        $sheet->setCellValue('B16', $schedule->Checklist_Stuffing_Process ? 'Sudah' : 'Belum');
        $sheet->setCellValue('C16', $schedule->Checklist_Rilis_NPE ? 'Sudah' : 'Belum');
        $sheet->setCellValue('D16', $schedule->Checklist_Get_In_CY ? 'Sudah' : 'Belum');
        $sheet->setCellValue('E16', $schedule->Checklist_Done_Kapal_Berangkat ? 'Sudah' : 'Belum');
        $sheet->setCellValue('F16', $this->first([
            $schedule->Checklist_Updated_By,
            $schedule->Updated_By,
        ]));
        $sheet->setCellValue('G16', $this->formatDateTime($schedule->Checklist_Updated_Date));
        $sheet->setCellValue('H16', $this->first([$schedule->Note]));
        $sheet->setCellValue('A19', $this->first([
            $schedule->DO_Note,
            $schedule->Note,
        ]));

        $sheet->getStyle('B11')->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet->getStyle('D11')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('F11')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('H11')->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle('B12')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('D12')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('F12')->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00');

        $output = sys_get_temp_dir() . '/final_stuffing_report_' . uniqid() . '.xlsx';
        (new Xlsx($spreadsheet))->save($output);
        $spreadsheet->disconnectWorksheets();

        return $output;
    }

    private function loadingPeriod($contract): string
    {
        if (!$contract || !$contract->Loading_Start || !$contract->Loading_End) {
            return 'TBD';
        }

        return $this->formatDate($contract->Loading_Start) . ' - ' . $this->formatDate($contract->Loading_End);
    }

    private function first(array $values): string
    {
        foreach ($values as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return (string) $value;
            }
        }

        return 'TBD';
    }

    private function formatDate($value): string
    {
        $value = trim((string) $value);

        if ($value === '' || strpos($value, '0000-00-00') === 0) {
            return 'TBD';
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('d F Y', $timestamp) : 'TBD';
    }

    private function formatDateTime($value): string
    {
        $value = trim((string) $value);

        if ($value === '' || strpos($value, '0000-00-00') === 0) {
            return 'TBD';
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('d F Y H:i', $timestamp) : 'TBD';
    }
}
