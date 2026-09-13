<?php

namespace Tests\Feature\BusinessPartner;

use Tests\TestCase;

/**
 * The lead/opportunity lists must show the real package ("Mobil Listrik" /
 * "Booth") instead of the legacy "Opsi N" wording, and must never render
 * "Opsi 0" when the package is known (via Hub_Type or package_option) or
 * unknown.
 *
 * Reference values:
 *   mobil / option2        -> Mobil Listrik
 *   booth / option1        -> Booth
 *   Partnership_Interest 1 -> Mobil Listrik, 2 -> Booth
 */
class LeadsLabelTest extends TestCase
{
    /** @test */
    public function package_option_is_labelled_with_the_real_package(): void
    {
        $this->assertSame('Mobil Listrik', leads_interest_label('mobil'));
        $this->assertSame('Booth', leads_interest_label('booth'));
        $this->assertSame('Mobil Listrik', leads_interest_label('option2'));
        $this->assertSame('Booth', leads_interest_label('option1'));
    }

    /** @test */
    public function hub_type_is_labelled_with_the_real_package(): void
    {
        $this->assertSame('Mobil Listrik', leads_interest_label(null, 'mobil'));
        $this->assertSame('Booth', leads_interest_label(null, 'booth'));
    }

    /** @test */
    public function partnership_interest_fallback_maps_to_the_real_package(): void
    {
        $this->assertSame('Mobil Listrik', leads_interest_label(null, null, 1));
        $this->assertSame('Booth', leads_interest_label(null, null, 2));
    }

    /** @test */
    public function hub_type_wins_over_a_stale_partnership_interest(): void
    {
        // Many live rows have Hub_Type set but Partnership_Interest = 0.
        $this->assertSame('Booth', leads_interest_label(null, 'booth', 0));
        $this->assertSame('Mobil Listrik', leads_interest_label(null, 'mobil', 0));
    }

    /** @test */
    public function unknown_package_is_not_labelled_opsi_zero(): void
    {
        $label = leads_interest_label(null, null, 0);

        $this->assertStringNotContainsString('Opsi', $label);
        $this->assertNotSame('Opsi 0', $label);
    }
}
