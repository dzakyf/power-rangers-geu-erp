<?php

namespace Tests\Unit\CNS;

use Tests\TestCase;

class DecimalNumberFormatTest extends TestCase
{
    /** @test */
    public function mainlayout_cns_defines_global_decimal_helpers(): void
    {
        $content = file_get_contents(resource_path('views/layout/mainlayout-cns.blade.php'));

        $this->assertStringContainsString('window.cnsParseNumber', $content);
        $this->assertStringContainsString('window.cnsFormatNumber', $content);
        $this->assertStringContainsString('window.cnsInitDecimalInputs', $content);
        $this->assertStringContainsString("input.cns-decimal", $content);
        $this->assertStringNotContainsString('id-ID', $content);
    }

    /** @test */
    public function cns_views_controllers_and_services_have_no_indonesian_locale_number_formatting(): void
    {
        $paths = [
            resource_path('views/cns'),
            app_path('Http/Controllers/CNS'),
            app_path('Services/CNS'),
        ];

        $badPatterns = [
            "/id-ID/",
            "/toLocaleString/",
            "/number_format\([^)]*['\"],['\"],\s*['\"]\.['\"]/",
        ];

        foreach ($paths as $dir) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if (!$file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $content = file_get_contents($file->getPathname());
                foreach ($badPatterns as $pattern) {
                    $this->assertNotRegExp(
                        $pattern,
                        $content,
                        "Found forbidden Indonesian format matching {$pattern} in {$file->getPathname()}"
                    );
                }
            }
        }
    }

    /** @test */
    public function cns_views_have_no_active_three_zero_dhtmlx_number_format(): void
    {
        $dir = resource_path('views/cns');
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $lines = file($file->getPathname());
            foreach ($lines as $lineNo => $line) {
                if (preg_match('/^\s*\/\//', $line)) {
                    continue;
                }

                $this->assertNotRegExp(
                    '/setNumberFormat\(\s*["\']0,000["\']/',
                    $line,
                    "Found active non-decimal setNumberFormat in {$file->getPathname()}:" . ($lineNo + 1)
                );
            }
        }
    }

    /** @test */
    public function key_cns_views_use_decimal_format_and_cns_decimal_inputs(): void
    {
        $prDetail = file_get_contents(resource_path('views/cns/procurement/purchase-request-detail.blade.php'));
        $this->assertStringContainsString('PRNewDetail.setNumberFormat("0,000.00",4,".",",");', $prDetail);
        $this->assertStringContainsString('PRNewDetail.setNumberFormat("0,000.00",10,".",",");', $prDetail);
        $this->assertStringContainsString('PRNewDetail.setNumberFormat("0,000.00",11,".",",");', $prDetail);

        $prNew = file_get_contents(resource_path('views/cns/procurement/purchase-request-new.blade.php'));
        $this->assertStringContainsString('class="cns-decimal" id="Subtotal"', $prNew);
        $this->assertStringContainsString('PRNewDetail.setNumberFormat("0,000.00", 10, ".", ",");', $prNew);
        $this->assertStringContainsString('PRNewDetail.setNumberFormat("0,000.00", 11, ".", ",");', $prNew);

        $salesNew = file_get_contents(resource_path('views/cns/sales/input-order-new.blade.php'));
        $this->assertStringContainsString('cns-decimal', $salesNew);

        $salesScript = file_get_contents(resource_path('views/cns/sales/partials/item-row-script.blade.php'));
        $this->assertStringContainsString('cnsFormatNumber', $salesScript);
        $this->assertStringContainsString('cnsParseNumber', $salesScript);

        $kemasan = file_get_contents(resource_path('views/cns/master/kemasan.blade.php'));
        $this->assertStringContainsString('cns-decimal', $kemasan);
        $this->assertStringContainsString('cnsFormatNumber(cnsParseNumber', $kemasan);

        $kemasanCtrl = file_get_contents(app_path('Http/Controllers/CNS/MasterKemasanController.php'));
        $this->assertStringContainsString("number_format((float) \$item->Volume, 2, '.', ',')", $kemasanCtrl);
        $this->assertStringContainsString("number_format((float) (\$item->Price ?? 0), 2, '.', ',')", $kemasanCtrl);

        $ciNew = file_get_contents(resource_path('views/cns/export/ci-new.blade.php'));
        $this->assertStringContainsString('id="Qty" value="0.00"', $ciNew);
        $this->assertStringContainsString('cns-decimal', $ciNew);
    }

    /** @test */
    public function export_print_templates_render_comma_thousands_and_two_decimals(): void
    {
        \Illuminate\Support\Facades\Auth::shouldReceive('user')->andReturn((object) [
            'Location' => 'SBY',
            'username' => 'tester',
        ]);

        $ciHtml = view('cns.export.print.ci', [
            'buyer' => (object) ['Company_Name' => 'PT GREEN ENERGI UTAMA'],
            'companies' => [
                'SUP-1' => (object) ['Company_Name' => 'PT GREEN ENERGI UTAMA', 'Address' => 'Jakarta', 'Logo' => ''],
                'BUY-1' => (object) ['Company_Name' => 'Buyer Co', 'Address' => 'Street 1'],
            ],
            'invoice' => (object) [
                'Inv_No' => 'INV-001',
                'Inv_Date' => '2026-11-12',
                'Order_No' => 'ORD-001',
                'Contract_No' => 'CTR-001',
                'Shipper' => 'SUP-1',
                'Consignee' => 'BUY-1',
                'Description' => 'UCO',
                'Qty' => 1234.5,
                'Unit_Price' => 1000.25,
                'Amount' => 1234808.625,
                'Advance_Payment' => 500,
                'Other' => 100.5,
                'Total' => 1234409.13,
                'Shipping_From' => 'Surabaya',
                'Shipping_To' => 'Rotterdam',
                'Shipping_Terms' => 'CIF',
                'BL_No' => 'BL-001',
                'Carrier' => 'MAERSK',
                'Consignee_Name' => 'Buyer',
                'Consignee_Address' => 'Street 1',
            ],
        ])->render();

        $this->assertStringContainsString('1,234.50', $ciHtml);
        $this->assertStringContainsString('1,000.25', $ciHtml);
        $this->assertStringContainsString('500.00', $ciHtml);
        $this->assertStringContainsString('1,234,409.13', $ciHtml);

        $sd = new class {
            public $SD_No = 'SD-001';
            public $Consignee = 'BUY-1';
            public $Issue_Date = '2026-11-12';
            public $Contract_No = 'CTR-001';
            public $Dispatch_Date = '2026-11-12';
            public $Dispatch_Same_As_Supplier = 1;
            public $Dispatch_Address = 'SBY';
            public $Receipt_Same_As_Recipient = 1;
            public $Receipt_Address = 'Rotterdam';
            public $Certificate_No = 'CERT-1';
            public $Product_Type = 'UCO';
            public $Raw_Material_Type = 'Used Cooking Oil';
            public $Additional_Info = '';
            public $Country_Of_Origin = 'Indonesia';
            public $Quantity = 1500.5;
            public $ISCC_Compliant = 1;
            public $EU_RED_Compliant = 1;
            public $Chain_Of_Custody = 'Mass balance';
            public $GHG_Total_Default = 1;
            public $Transport_Ship_KM = 0;
            public $Transport_Train_KM = 0;
            public $Transport_Road_KM = 0;
            public $GHG_DDV_Oil_Extraction = 1;
            public $GHG_DDV_Soil_N2O = 1;
            public $GHG_Esca_Bonus_Cap = '';
            public $GHG_NUTS2_Area = '';
            public $GHG_El_Bonus = 'Yes';
            public $Waste_Permit_No = '';
            public $Note = '';

            public function __get($name)
            {
                return 0;
            }
        };

        $sdHtml = view('cns.export.print.sd', [
            'companies' => [
                'BUY-1' => (object) ['Company_Name' => 'Buyer Co', 'Address' => 'Street 1'],
            ],
            'sd' => $sd,
        ])->render();

        $this->assertStringContainsString('1,500.50 mt', $sdHtml);
    }
}
