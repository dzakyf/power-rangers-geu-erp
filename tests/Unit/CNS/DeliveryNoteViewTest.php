<?php

namespace Tests\Unit\CNS;

use Tests\TestCase;

class DeliveryNoteViewTest extends TestCase
{
    /** @test */
    public function new_view_renders_entitas_penerbit_dropdown(): void
    {
        $companies = [
            (object) [
                'Supplier_ID'  => 'SUPP-001',
                'Company_Name' => 'PT Green Energi Utama',
                'Address'      => 'Jl. Industri No 1',
            ],
            (object) [
                'Supplier_ID'  => 'SUPP-002',
                'Company_Name' => 'PT Energi Hijau Selaras',
                'Address'      => 'Jl. Industri No 2',
            ],
        ];

        $html = view('cns.export.dn-new', [
            'companies' => $companies,
            'DnNo'      => '001/DN-GEU/XI/2026',
        ])->render();

        $this->assertStringContainsString('Entitas Penerbit', $html);
        $this->assertStringContainsString('name="Shipper"', $html);
        $this->assertStringContainsString('id="Shipper"', $html);
        $this->assertStringContainsString('value="SUPP-001"', $html);
        $this->assertStringContainsString('value="SUPP-002"', $html);
        $this->assertStringContainsString('name="Consignee_Address"', $html);
        $this->assertStringContainsString('data-address="Jl. Industri No 1"', $html);
        $this->assertStringContainsString('updateAddressFromSelectedCompany', $html);
    }

    /** @test */
    public function detail_view_renders_entitas_penerbit_with_selected_option(): void
    {
        $companies = [
            (object) [
                'Supplier_ID'  => 'SUPP-001',
                'Company_Name' => 'PT Green Energi Utama',
                'Address'      => 'Jl. Industri No 1',
            ],
            (object) [
                'Supplier_ID'  => 'SUPP-002',
                'Company_Name' => 'PT Energi Hijau Selaras',
                'Address'      => 'Jl. Industri No 2',
            ],
        ];

        $dn = (object) [
            'DN_No'             => '001/DN-GEU/XI/2026',
            'Shipper'           => 'SUPP-002',
            'Consignee'         => 'SUPP-001',
            'Consignee_Address' => 'Jl. Custom Consignee No 10',
            'Delivery_Date'     => '2026-11-04',
            'Supplier_ISCC_Ref' => 'ISCC-1234',
            'Contract_No'       => 'CTR-001',
            'Product'           => 'Used Cooking Oil',
            'Total_Tonnage'     => '50.000',
            'Country_Of_Origin' => 'Indonesia',
            'GHG_Value'         => 'Standard',
            'Note'              => 'Test note',
        ];

        $html = view('cns.export.dn-detail', [
            'companies' => $companies,
            'dn'        => $dn,
        ])->render();

        $this->assertStringContainsString('Entitas Penerbit', $html);
        $this->assertStringContainsString('name="Shipper"', $html);
        $this->assertStringContainsString('value="SUPP-002" selected', $html);
        $this->assertStringContainsString('name="Consignee_Address"', $html);
        $this->assertStringContainsString('Jl. Custom Consignee No 10', $html);
        $this->assertStringContainsString('data-address="Jl. Industri No 1"', $html);
        $this->assertStringContainsString('updateAddressFromSelectedCompany', $html);
    }

    /** @test */
    public function print_view_renders_letterhead_when_issuer_has_letterhead(): void
    {
        $issuer = (object) [
            'Supplier_ID'    => 'SUPP-002',
            'Company_Name'   => 'PT Energi Hijau Selaras',
            'Address'        => 'Jl. Industri No 12, Surabaya',
            'City'           => 'Surabaya',
            'Contact_Person' => 'Budi Santoso',
            'Sales_Name'     => 'Direktur Utama',
            'Letterhead'     => 'filesupplier/LetterHead_Custom.png',
            'Footer'         => 'filesupplier/Footer_Custom.png',
            'Logo'           => null,
        ];

        $consignee = (object) [
            'Supplier_ID'  => 'CONS-001',
            'Company_Name' => 'Global Buyer B.V.',
        ];

        $dn = (object) [
            'DN_No'             => '001/DN-GEU/XI/2026',
            'Shipper'           => 'SUPP-002',
            'Consignee'         => 'CONS-001',
            'Delivery_Date'     => '2026-11-04',
            'Supplier_ISCC_Ref' => 'ISCC-REF-999',
            'Contract_No'       => 'CTR-EXPORT-99',
            'Product'           => 'Used Cooking Oil',
            'Total_Tonnage'     => 43.600,
            'Country_Of_Origin' => 'Indonesia',
            'GHG_Value'         => 'Default',
            'Note'              => '',
        ];

        $companies = [
            'SUPP-002' => $issuer,
            'CONS-001' => $consignee,
        ];

        $html = view('cns.export.print.dn', [
            'dn'        => $dn,
            'companies' => $companies,
            'issuer'    => $issuer,
        ])->render();

        $this->assertStringContainsString('filesupplier/LetterHead_Custom.png', $html);
        $this->assertStringContainsString('filesupplier/Footer_Custom.png', $html);
        $this->assertStringContainsString('PT Energi Hijau Selaras', $html);
        $this->assertStringContainsString('Budi Santoso', $html);
        $this->assertStringContainsString('Direktur Utama', $html);
        $this->assertStringContainsString('GLOBAL BUYER B.V.', $html);
    }

    /** @test */
    public function print_view_renders_fallback_header_when_no_letterhead(): void
    {
        $issuer = (object) [
            'Supplier_ID'    => 'SUPP-001',
            'Company_Name'   => 'PT Green Energi Utama',
            'Address'        => "A2 Pergudangan Tanrise K-Walk\nMalang",
            'City'           => 'Malang',
            'Contact_Person' => 'Puput Deni Iswara',
            'Sales_Name'     => 'CMO',
            'Letterhead'     => null,
            'Footer'         => null,
            'Logo'           => 'filesupplier/Logo_GEU.jpeg',
        ];

        $dn = (object) [
            'DN_No'             => '002/DN-GEU/XI/2026',
            'Shipper'           => 'SUPP-001',
            'Consignee'         => 'CONS-001',
            'Delivery_Date'     => '2026-11-04',
            'Supplier_ISCC_Ref' => '',
            'Contract_No'       => '',
            'Product'           => 'Used Cooking Oil',
            'Total_Tonnage'     => 20.000,
            'Country_Of_Origin' => 'Indonesia',
            'GHG_Value'         => '',
            'Note'              => '',
        ];

        $companies = [
            'SUPP-001' => $issuer,
        ];

        $html = view('cns.export.print.dn', [
            'dn'        => $dn,
            'companies' => $companies,
            'issuer'    => $issuer,
        ])->render();

        $this->assertStringContainsString('PT Green Energi Utama', $html);
        $this->assertStringContainsString('filesupplier/Logo_GEU.jpeg', $html);
        $this->assertStringContainsString('Puput Deni Iswara', $html);
        $this->assertStringContainsString('CMO', $html);
    }
}
