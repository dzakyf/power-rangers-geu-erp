<?php

namespace Tests\Unit\CNS;

use App\CnsOfferingLetter;
use Carbon\Carbon;
use Tests\TestCase;

class CnsOfferingLetterTest extends TestCase
{
    public function test_roman_month_and_offer_no_formatting()
    {
        $dateOct = Carbon::create(2026, 10, 29);
        $this->assertSame('016/OL/EXP-GNC/X/2026', CnsOfferingLetter::formatOfferNo(16, 'GNC', $dateOct));

        $dateNov = Carbon::create(2026, 11, 13);
        $this->assertSame('001/OL/EXP-GEU/XI/2026', CnsOfferingLetter::formatOfferNo(1, 'GEU', $dateNov));
    }

    public function test_next_sequence_from_existing_numbers()
    {
        $this->assertSame(1, CnsOfferingLetter::nextSequenceFromNumbers([]));
        $this->assertSame(17, CnsOfferingLetter::nextSequenceFromNumbers([
            '001/OL/EXP-GNC/I/2026',
            '016/OL/EXP-GNC/X/2026',
            '010/OL/EXP-GNC/V/2026',
        ]));
    }

    public function test_sellers_and_signatories_mapping()
    {
        $sellers = CnsOfferingLetter::sellers();
        $this->assertSame('Singapore', $sellers['GNC']['place']);
        $this->assertSame('Jakarta', $sellers['GEU']['place']);

        $signatories = CnsOfferingLetter::signatories();
        $this->assertSame('Puput Deni Iswara', $signatories['puput']['name']);
        $this->assertSame('Hani Septyana', $signatories['hani']['name']);
    }

    public function test_loading_period_formatting_and_fallback()
    {
        $this->assertSame('01 November 2026 - 30 November 2026', CnsOfferingLetter::formatLoadingPeriod('2026-11-01 - 2026-11-30'));
        $this->assertSame('15 November 2026', CnsOfferingLetter::formatLoadingPeriod('2026-11-15'));
        $this->assertSame('November 2026', CnsOfferingLetter::formatLoadingPeriod('2026-11'));
        $this->assertSame('Custom Period', CnsOfferingLetter::formatLoadingPeriod('Custom Period'));
        $this->assertSame('2026-11', CnsOfferingLetter::loadingPeriodMonthValue('2026-11'));
        $this->assertSame(date('Y-m'), CnsOfferingLetter::loadingPeriodMonthValue('invalid-date-string'));

        $this->assertSame(['start' => '2026-11-01', 'end' => '2026-11-15'], CnsOfferingLetter::parseLoadingPeriodRange('2026-11-01 - 2026-11-15'));
        $this->assertSame(['start' => '2026-11-15', 'end' => '2026-11-15'], CnsOfferingLetter::parseLoadingPeriodRange('2026-11-15'));
        $this->assertSame(['start' => '2026-11-01', 'end' => '2026-11-30'], CnsOfferingLetter::parseLoadingPeriodRange('2026-11'));
        $this->assertSame(['start' => '', 'end' => ''], CnsOfferingLetter::parseLoadingPeriodRange(''));
    }

    public function test_resolve_seller_code_mapping()
    {
        $this->assertSame('GEU', CnsOfferingLetter::resolveSellerCode('PT Green Energi Utama'));
        $this->assertSame('GEU', CnsOfferingLetter::resolveSellerCode('Green Energi Utama', 'COMP-001'));
        $this->assertSame('GEU', CnsOfferingLetter::resolveSellerCode('Any Company Name', 'GEU'));

        $this->assertSame('GNC', CnsOfferingLetter::resolveSellerCode('Grennew corporation'));
        $this->assertSame('GNC', CnsOfferingLetter::resolveSellerCode('Greennew Corporation PTE LTD'));
        $this->assertSame('GNC', CnsOfferingLetter::resolveSellerCode('Grennew Corporation PTE LTD', 'COMP-002'));
        $this->assertSame('GNC', CnsOfferingLetter::resolveSellerCode('Any Company Name', 'GNC'));

        $this->assertSame('ABC', CnsOfferingLetter::resolveSellerCode('Other Enterprise', 'ABC'));
    }

    public function test_image_to_data_uri_handling()
    {
        $controller = new \App\Http\Controllers\CNS\OfferingLetterController();
        $ref = new \ReflectionClass($controller);
        $method = $ref->getMethod('imageToDataUri');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($controller, ''));
        $this->assertNull($method->invoke($controller, null));
        $this->assertNull($method->invoke($controller, 'https://example.com/image.png'));
        $this->assertNull($method->invoke($controller, 'non-existent-image-path.png'));
    }

    public function test_print_view_fits_single_page()
    {
        $letter = (object) [
            'offer_no'            => '001/OL/EXP-GEU/XI/2026',
            'offer_date'          => Carbon::create(2026, 11, 16),
            'seller_name'         => 'PT Green Energi Utama',
            'place_of_issue'      => 'Jakarta',
            'buyer_name'          => 'PT Buyer Global Internasional',
            'product'             => 'Refined Oil (Used Cooking Oil Entirely of Vege Origin)',
            'hs_code'             => '15119020',
            'certificate'         => 'ISCC-EU',
            'spec_ffa'            => '5% max',
            'spec_mni'            => '2% max',
            'spec_unsaponifiable' => '1% max',
            'spec_iodine_value'   => '50 min',
            'spec_sulphur'        => '50 ppm max',
            'spec_total_metals'   => '85 max',
            'spec_phosphorus'     => '50 max',
            'spec_total_chloride' => '50 max',
            'quantity_mt'         => 1000.00,
            'quantity_note'       => '(+/- 5% operational tolerance) In bulk',
            'final_qty_quality'   => 'Quantity and quality shall be final and binding at the Port of Loading (POL) as determined by an independent third-party surveyor.',
            'price_currency'      => 'USD',
            'price_per_mt'        => 950.00,
            'loading_period'      => '2026-12',
            'delivery_term'       => 'CIF',
            'destination_port'    => 'Rotterdam, Netherlands',
            'packaging'           => 'In Flexibag Container',
            'payment_terms'       => "100% L/C at Sight upon shipping documents\nSeller’s commercial Invoice (Scanned copies acceptable)\nCertificate(s) of Origin issued by Seller (Scanned copies acceptable)\nCertificate(s) of Quality issued by Seller/Independent Surveyor (Scanned copies acceptable)\nCertificate(s) of Quantity issued by Seller (Scanned copies acceptable)\nISCC-EU Sustainability Declaration / Proof of Sustainability (whichever applicable) (Scanned copies acceptable)\nPacking list (Scanned copies acceptable)",
            'load_port_inspector' => 'Third Party Independent Surveyor (Sharing Cost 50/50)',
            'valid_until'         => Carbon::create(2026, 11, 30),
            'status'              => 'APPROVED',
            'signatory_name'      => 'Puput Deni Iswara',
            'signatory_title'     => 'CCO',
        ];

        $html = view('cns.offering-letter.print', [
            'letter'       => $letter,
            'signatureUri' => null,
        ])->render();

        $this->assertNotEmpty($html);

        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new \Mpdf\Mpdf([
            'format'              => [210, 330],
            'tempDir'             => $tempDir,
            'margin_top'          => 10,
            'margin_bottom'       => 10,
            'margin_left'         => 15,
            'margin_right'        => 15,
            'margin_header'       => 0,
            'margin_footer'       => 0,
            'setAutoTopMargin'    => false,
            'setAutoBottomMargin' => false,
        ]);

        $mpdf->WriteHTML($html);

        $this->assertSame(1, $mpdf->page, 'Offering Letter print must fit within 1 page.');
    }
}
