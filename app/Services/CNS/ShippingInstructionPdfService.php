<?php

namespace App\Services\CNS;

use App\ShippingInstruction;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class ShippingInstructionPdfService
{
    protected $printService;

    public function __construct(ShippingInstructionPrintService $printService)
    {
        $this->printService = $printService;
    }

    public function render(ShippingInstruction $shipment, string $prefix = 'SI'): array
    {
        $print = $this->printService->resolve($shipment);
        $vsds = $this->printService->containersFor($shipment);

        $gross = $vsds->sum('Gross_KG');
        $netto = $vsds->sum('Netto_KG');
        $mt = round($netto / 1000, 2);

        if ($prefix === 'FSI') {
            $companies = collect();
            try {
                $rawSuppliers = DB::table('supplier')->get();
                $companies = $rawSuppliers->keyBy('Supplier_ID')->union($rawSuppliers->keyBy('ID'));
            } catch (\Throwable $e) {
                // Keep empty collection when DB is unreachable
            }
            $approver = $print['approver'];
            $approverSignature = $print['approver_signature'];
            $logoDataUri = $print['shipper']['logo_data_uri'];

            $html = view('cns.export.print.fsi', compact(
                'print', 'shipment', 'vsds', 'gross', 'netto', 'mt',
                'companies', 'approver', 'approverSignature', 'logoDataUri'
            ))->render();
        } else {
            $html = view('cns.export.print.si', compact('print', 'vsds', 'gross', 'netto', 'mt'))->render();
        }

        $headerHtml = $this->imageHtml($print['shipper']['letterhead_data_uri']);
        $footerHtml = $this->imageHtml($print['shipper']['footer_data_uri']);

        $mpdf = new Mpdf([
            'format'        => 'A4',
            'tempDir'       => storage_path('app/mpdf'),
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'margin_left'   => 0,
            'margin_right'  => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
            'setAutoTopMargin'    => 'stretch',
            'setAutoBottomMargin' => 'stretch',
        ]);

        $mpdf->splitTableBorderWidth = 1;

        if ($headerHtml !== '') {
            $mpdf->SetHTMLHeader($headerHtml);
        }

        if ($footerHtml !== '') {
            $mpdf->SetHTMLFooter($footerHtml);
        }

        $mpdf->WriteHTML($html);

        $filename = $prefix . '_' . $print['values']['si_no'];
        $mpdf->SetTitle($filename);

        return [
            'content' => $mpdf->Output($filename . '.pdf', 'S'),
            'filename' => $filename . '.pdf',
        ];
    }

    private function imageHtml($dataUri): string
    {
        if (!$dataUri) {
            return '';
        }

        return '<div style="margin:0;padding:0;line-height:0;">'
            . '<img src="' . $dataUri . '" style="width:100%;display:block;">'
            . '</div>';
    }
}
