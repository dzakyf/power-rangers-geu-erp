<?php

namespace Tests\Unit\CNS;

use Tests\TestCase;

class AnnexVIIFinalSiFilterTest extends TestCase
{
    public function test_annex_controller_filters_approved_unused_final_si_and_guards_endpoints(): void
    {
        $source = file_get_contents(base_path('app/Http/Controllers/CNS/AnnexVIIController.php'));

        $this->assertNotFalse($source);
        $this->assertStringContainsString("where('Status', 'APPROVED')", $source);
        $this->assertStringContainsString("whereNotNull('Final_SI_No')", $source);
        $this->assertStringContainsString("whereNotIn('Final_SI_No', \$usedFinalSiNos)", $source);
        $this->assertStringContainsString("AnnexVII::where('SI_No', \$shipment->Final_SI_No)->exists()", $source);
        $this->assertStringContainsString("AnnexVII::where('SI_No', \$finalSiNo)->exists()", $source);
        $this->assertStringNotContainsString('where("Status", "FINAL")', $source);
        $this->assertStringContainsString("VesselScheduleDtl::active()->where('SI_No', \$shipment->SI_No)", $source);
        $this->assertStringContainsString("arranger_address", $source);
        $this->assertStringContainsString("consignee_email", $source);
        $this->assertStringContainsString("actual_quantity", $source);
        $this->assertStringContainsString("Container{\$i}", $source);
        $this->assertStringContainsString("VesselSchedule::where('Order_No', \$shipment->Order_No)", $source);
        $this->assertStringContainsString("cns_buyer_contracts", $source);
        $this->assertStringContainsString("Destination_Port", $source);
        $this->assertStringContainsString("\$shipment->Destination_Port = \$pod;", $source);

        $view = file_get_contents(base_path('resources/views/cns/export/annex-new.blade.php'));
        $this->assertNotFalse($view);
        $this->assertStringContainsString('<input type="text" id="Destination_Port" name="Destination_Port"', $view);
    }
}
