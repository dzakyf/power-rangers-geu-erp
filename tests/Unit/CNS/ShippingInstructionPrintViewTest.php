<?php

namespace Tests\Unit\CNS;

use Tests\TestCase;

class ShippingInstructionPrintViewTest extends TestCase
{
    /** @test */
    public function si_print_view_layout_matches_pl_layout(): void
    {
        $content = file_get_contents(resource_path('views/cns/export/print/si.blade.php'));

        // Font and body
        $this->assertStringContainsString('font-size: 8.5pt;', $content);
        $this->assertStringNotContainsString('#FFD700', $content);

        // Title block layout
        $this->assertStringContainsString('rowspan="2" width="28mm"', $content);
        $this->assertStringContainsString('colspan="4" class="title-heading"', $content);
        $this->assertStringContainsString('width="18%" style="border-right:1px solid #000;padding:2px 4px;font-size:7pt;font-weight:bold;">Process Owner :', $content);

        // Party box dimensions
        $this->assertStringContainsString('height:12mm;', $content);
        $this->assertStringContainsString('width: 30mm;', $content);

        // Cargo table column widths and Total row
        $this->assertStringContainsString('width:38mm;border-right:1px solid #000;border-bottom:1px solid #000;" rowspan="2">SEQ', $content);
        $this->assertStringContainsString('width:21mm;border-right:1px solid #000;border-bottom:1px solid #000;">NET WEIGHT', $content);
        $this->assertStringContainsString('width:21mm;border-right:1px solid #000;border-bottom:1px solid #000;">GROSS WEIGHT', $content);
        $this->assertStringContainsString('width:21mm;border-right:1px solid #000;border-bottom:1px solid #000;">QTY', $content);
        $this->assertStringContainsString('width:21mm;border-bottom:1px solid #000;">QTY', $content);
        $this->assertStringContainsString('>TOTAL</td>', $content);
        $this->assertStringContainsString('background-color:#e0e0e0', $content);

        // Single signature block (Approver on the left)
        $this->assertStringContainsString('<table width="45%"', $content);
        $this->assertStringNotContainsString('Muhammad Abdul Jabar', $content);
        $this->assertStringContainsString('$print[\'approver_signature\']', $content);
        $this->assertStringContainsString('$print[\'approver\']->Name', $content);

        // Summary row matches FSI and PL
        $this->assertStringContainsString('INCOTERM: {{ $incotermStr }}', $content);
        $this->assertStringContainsString('OCEAN FREIGHT PREPAID', $content);
        $this->assertStringContainsString('QUANTITY : {{ number_format($mt, 2, \'.\', \',\') }} METRIC TONS', $content);
        $this->assertStringContainsString('COUNTRY OF ORIGIN: INDONESIA', $content);
        $this->assertStringNotContainsString('SHIPPING MARK: FREIGHT PREPAID', $content);
    }

    /** @test */
    public function fsi_print_view_layout_matches_pl_layout(): void
    {
        $content = file_get_contents(resource_path('views/cns/export/print/fsi.blade.php'));

        // Font and body
        $this->assertStringContainsString('font-size: 8.5pt;', $content);
        $this->assertStringNotContainsString('#FFD700', $content);

        // Title block layout
        $this->assertStringContainsString('rowspan="2" width="28mm"', $content);
        $this->assertStringContainsString('colspan="4" class="title-heading"', $content);
        $this->assertStringContainsString('width="18%" style="border-right:1px solid #000;padding:2px 4px;font-size:7pt;font-weight:bold;">Process Owner :', $content);

        // Party box dimensions
        $this->assertStringContainsString('height:12mm;', $content);
        $this->assertStringContainsString('width: 30mm;', $content);

        // Cargo table column widths and Total row
        $this->assertStringContainsString('width:38mm;border-right:1px solid #000;border-bottom:1px solid #000;" rowspan="2">SEQ', $content);
        $this->assertStringContainsString('width:21mm;border-right:1px solid #000;border-bottom:1px solid #000;">NET WEIGHT', $content);
        $this->assertStringContainsString('width:21mm;border-right:1px solid #000;border-bottom:1px solid #000;">GROSS WEIGHT', $content);
        $this->assertStringContainsString('width:21mm;border-right:1px solid #000;border-bottom:1px solid #000;">QTY', $content);
        $this->assertStringContainsString('width:21mm;border-bottom:1px solid #000;">QTY', $content);
        $this->assertStringContainsString('>TOTAL</td>', $content);
        $this->assertStringContainsString('background-color:#e0e0e0', $content);

        // Single signature block (Approver on the left)
        $this->assertStringContainsString('<table width="45%"', $content);
        $this->assertStringNotContainsString('Muhammad Abdul Jabar', $content);
        $this->assertStringContainsString('$approverSignature', $content);
        $this->assertStringContainsString('$approver->Name', $content);
    }

    /** @test */
    public function it_can_write_si_html_to_mpdf_without_table_offset_errors(): void
    {
        $mockPrint = [
            'values' => [
                'status_label' => 'APPROVED',
                'si_no' => 'SI-TEST-001',
                'si_date' => 'November, 09 2026',
                'contract_no' => 'CTR-001',
                'delivery_terms' => 'CIF',
                'quantity' => '10 x 20 FT',
                'voyage' => 'VESSEL 1',
                'shipped_by' => 'VESSEL 1',
                'etd' => 'November, 10 2026',
                'eta' => 'November, 15 2026',
                'stuffing' => 'November, 09 2026',
                'peb_no' => 'PEB-001',
                'peb_date' => 'November, 09 2026',
                'product_name' => 'REFINED USED COOKING OIL',
                'hs_code' => '15119020',
                'package_type' => '20 FT',
                'bl_no' => 'BL-001',
                'pol' => 'JAKARTA',
                'pod' => 'PASIR GUDANG',
            ],
            'shipper' => [
                'name' => 'PT Shipper',
                'address' => 'Shipper Address',
                'city' => 'Jakarta',
                'logo_data_uri' => null,
            ],
            'consignee' => [
                'name' => 'Consignee Ltd',
                'address' => 'Consignee Address',
                'city' => 'Singapore',
            ],
            'notify' => [
                'name' => 'Notify Ltd',
                'address' => 'Notify Address',
                'city' => 'Singapore',
            ],
            'destination' => [
                'name' => 'Port Authority',
                'address' => 'Port Address',
            ],
            'is_final' => false,
            'approver' => (object) [
                'Name' => 'Hani Septyana',
                'Position' => 'Commercial and Sustainability Manager',
            ],
            'approver_signature' => null,
        ];

        $vsds = collect([
            (object) [
                'Container' => 'CONT123',
                'Seal' => 'SEAL123',
                'Netto_KG' => 20000,
                'Gross_KG' => 22000,
            ],
        ]);

        $gross = 22000;
        $netto = 20000;
        $mt = 20.00;

        $html = view('cns.export.print.si', [
            'print' => $mockPrint,
            'vsds' => $vsds,
            'gross' => $gross,
            'netto' => $netto,
            'mt' => $mt,
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf'),
        ]);

        $mpdf->WriteHTML($html);
        $pdfOutput = $mpdf->Output('', 'S');

        $this->assertNotEmpty($pdfOutput);
        $this->assertStringStartsWith('%PDF', $pdfOutput);
    }
}
