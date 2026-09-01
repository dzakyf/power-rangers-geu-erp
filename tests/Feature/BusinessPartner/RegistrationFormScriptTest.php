<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Regression guard: the "Form Registrasi Partnership" fragment must ship a
 * syntactically intact script - a duplicated function declaration used to
 * kill the entire inline script (select2 chained dropdowns + Get GPS), showing
 * "Uncaught SyntaxError: Unexpected end of input".
 *
 * The address fields are selects fed by the region API (master-province ->
 * master-city -> master-district -> master-subdistrict -> master-postal-code);
 * they only initialize when the script parses.
 */
class RegistrationFormScriptTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function registration_fragment_has_single_gps_handler_and_select_fields()
    {
        $user = User::where('username', 'admin')->firstOrFail();

        $html = $this->actingAs($user)
            ->get('/partner-business-registration-detail?ID=1')
            ->assertOk()
            ->getContent();

        // Exactly one bindGpsButton declaration - the duplicate used to
        // break the whole script block.
        $this->assertEquals(1, substr_count($html, 'function bindGpsButton(btnId, latId, lngId, statusId) {'));

        // The chained address dropdowns must exist as <select>s.
        $this->assertStringContainsString('<select name="I_Address_Province"', $html);
        $this->assertStringContainsString('<select name="I_Address_City"', $html);
        $this->assertStringContainsString('<select name="I_Address_Subdistrict"', $html);
        $this->assertStringContainsString('<select name="I_Address_Ward"', $html);
        $this->assertStringContainsString('<select name="I_Address_Postal"', $html);

        // The chained init must be present so the selects become select2.
        $this->assertStringContainsString('initAddressChainedWhenReady()', $html);

        // "Informasi Lokasi" Province/City selects + their select2 wiring.
        $this->assertStringContainsString('<select name="Province" id="Province"', $html);
        $this->assertStringContainsString('<select name="City" id="City"', $html);
        $this->assertStringContainsString('function initInformasiLokasi()', $html);
        $this->assertStringContainsString("makeSelect2(pSel, 'master-province'", $html);
        $this->assertStringContainsString("makeSelect2(cSel, 'master-city'", $html);
    }
}
