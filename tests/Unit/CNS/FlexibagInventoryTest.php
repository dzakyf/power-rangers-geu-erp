<?php

namespace Tests\Unit\CNS;

use Tests\TestCase;

class FlexibagInventoryTest extends TestCase
{
    /** @test */
    public function flexibag_inventory_view_renders_correctly()
    {
        $content = file_get_contents(resource_path('views/cns/inventory-flexibag/index.blade.php'));

        $this->assertStringContainsString('INVENTORY FLEXI BAG (MUTASI STOK)', $content);
        $this->assertStringContainsString('xml-cns-inventory-flexibag', $content);
        $this->assertStringContainsString('cns-inventory-flexibag-balance', $content);
        $this->assertStringContainsString('FlexibagGrid', $content);
        $this->assertStringContainsString('Qty OUT (Pcs)', $content);
        $this->assertStringContainsString('initialBalanceWrapper', $content);
        $this->assertStringContainsString('Saldo Awal Periode', $content);
    }

    /** @test */
    public function stuffing_detail_view_has_lock_guards_for_done_kapal_berangkat()
    {
        $content = file_get_contents(resource_path('views/cns/vs/stuffing-detail.blade.php'));

        $this->assertStringContainsString('Checklist_Done_Kapal_Berangkat ? \'disabled\' : \'\'', $content);
        $this->assertStringContainsString('Status pengiriman sudah final (Done/Kapal Berangkat)', $content);
    }

    /** @test */
    public function routes_contain_flexibag_endpoints()
    {
        $content = file_get_contents(base_path('routes/modules/cns.php'));

        $this->assertStringContainsString('$cnsFlexiPref = "cns-inventory-flexibag";', $content);
        $this->assertStringContainsString('Route::get("$cnsFlexiPref"', $content);
        $this->assertStringContainsString('Route::get("xml-$cnsFlexiPref"', $content);
        $this->assertStringContainsString('Route::get("$cnsFlexiPref-balance"', $content);
    }
}
