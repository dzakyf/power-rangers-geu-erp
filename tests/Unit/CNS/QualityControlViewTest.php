<?php

namespace Tests\Unit\CNS;

use Tests\TestCase;

class QualityControlViewTest extends TestCase
{
    /** @test */
    public function create_view_renders_nomor_kontrak_and_nomor_booking_after_tanggal(): void
    {
        $contracts = collect([
            (object) [
                'ID' => 10,
                'Contract_No' => 'CNS/BC/2026/001',
                'Buyer_Name' => 'PT Buyer Test',
            ],
            (object) [
                'ID' => 11,
                'Contract_No' => 'CNS/BC/2026/002',
                'Buyer_Name' => 'PT Global Import',
            ],
        ]);

        $html = view('cns.quality.create', [
            'contracts' => $contracts,
        ])->render();

        $this->assertStringContainsString('Tanggal', $html);
        $this->assertStringContainsString('Nomor Kontrak', $html);
        $this->assertStringContainsString('Nomor Booking Confirmation', $html);

        $posTanggal = strpos($html, 'label for="Created_At"');
        $posKontrak = strpos($html, 'label for="Contract_ID"');
        $posBooking = strpos($html, 'label for="Booking_ID"');

        $this->assertNotFalse($posTanggal);
        $this->assertNotFalse($posKontrak);
        $this->assertNotFalse($posBooking);

        // Nomor Kontrak and Nomor Booking must appear AFTER Tanggal
        $this->assertTrue($posKontrak > $posTanggal, 'Nomor Kontrak must appear after Tanggal');
        $this->assertTrue($posBooking > $posKontrak, 'Nomor Booking Confirmation must appear after Nomor Kontrak');

        // Check options rendered
        $this->assertStringContainsString('value="10"', $html);
        $this->assertStringContainsString('CNS/BC/2026/001 - PT Buyer Test', $html);
        $this->assertStringContainsString('value="11"', $html);
        $this->assertStringContainsString('CNS/BC/2026/002 - PT Global Import', $html);

        // Check dependent javascript
        $this->assertStringContainsString('loadQualityBookings', $html);
        $this->assertStringContainsString('cns-quality-bookings', $html);
    }

    /** @test */
    public function edit_view_renders_nomor_kontrak_and_nomor_booking_after_tanggal(): void
    {
        $contracts = collect([
            (object) [
                'ID' => 10,
                'Contract_No' => 'CNS/BC/2026/001',
                'Buyer_Name' => 'PT Buyer Test',
            ],
        ]);

        $html = view('cns.quality.edit', [
            'contracts' => $contracts,
        ])->render();

        $this->assertStringContainsString('Tanggal', $html);
        $this->assertStringContainsString('Nomor Kontrak', $html);
        $this->assertStringContainsString('Nomor Booking Confirmation', $html);

        $posTanggal = strpos($html, 'label for="edit_Created_At"');
        $posKontrak = strpos($html, 'label for="edit_Contract_ID"');
        $posBooking = strpos($html, 'label for="edit_Booking_ID"');

        $this->assertNotFalse($posTanggal);
        $this->assertNotFalse($posKontrak);
        $this->assertNotFalse($posBooking);

        $this->assertTrue($posKontrak > $posTanggal, 'Nomor Kontrak must appear after Tanggal in edit form');
        $this->assertTrue($posBooking > $posKontrak, 'Nomor Booking must appear after Nomor Kontrak in edit form');
    }
}

