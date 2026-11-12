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

        $posTanggal = strpos($html, 'label for="create_Created_At"');
        $posKontrak = strpos($html, 'label for="create_Contract_ID"');
        $posBooking = strpos($html, 'label for="create_Booking_ID"');

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

    /** @test */
    public function create_view_renders_daily_qc_option_and_tank_dropdown(): void
    {
        $tanks = collect([
            (object) ['id' => 1, 'name' => 'Tangki 01'],
            (object) ['id' => 2, 'name' => 'Tangki 02'],
        ]);

        $html = view('cns.quality.create', [
            'tanks' => $tanks,
        ])->render();

        $this->assertStringContainsString('value="DAILY_QC"', $html);
        $this->assertStringContainsString('Daily QC', $html);
        $this->assertStringContainsString('label for="create_Tank_ID"', $html);
        $this->assertStringContainsString('Nama Tangki', $html);
        $this->assertStringContainsString('value="1"', $html);
        $this->assertStringContainsString('Tangki 01', $html);
        $this->assertStringContainsString('value="2"', $html);
        $this->assertStringContainsString('Tangki 02', $html);
        $this->assertStringContainsString('daily-tank-group', $html);
    }

    /** @test */
    public function edit_view_renders_daily_qc_option_and_tank_dropdown(): void
    {
        $tanks = collect([
            (object) ['id' => 5, 'name' => 'Tangki Pelindo 5'],
        ]);

        $html = view('cns.quality.edit', [
            'tanks' => $tanks,
        ])->render();

        $this->assertStringContainsString('select name="QC_Type" id="edit_QC_Type"', $html);
        $this->assertStringContainsString('value="DAILY_QC"', $html);
        $this->assertStringContainsString('Daily QC', $html);
        $this->assertStringContainsString('label for="edit_Tank_ID"', $html);
        $this->assertStringContainsString('Nama Tangki', $html);
        $this->assertStringContainsString('value="5"', $html);
        $this->assertStringContainsString('Tangki Pelindo 5', $html);
        $this->assertStringContainsString('edit-daily-tank-group', $html);
    }

    /** @test */
    public function index_view_contains_monthly_min_max_summary_table(): void
    {
        $content = file_get_contents(resource_path('views/cns/quality/index.blade.php'));

        $this->assertStringContainsString('Nilai Minimum & Maksimum Parameter (1 Bulan)', $content);
        $this->assertStringContainsString('id="filter_stat_month"', $content);
        $this->assertStringContainsString('id="qcMonthlyTotalBadge"', $content);
        $this->assertStringContainsString('id="qcMonthlyStatsBodyLeft"', $content);
        $this->assertStringContainsString('id="qcMonthlyStatsBodyRight"', $content);
        $this->assertStringContainsString('id="qcMonthlyStatsCollapse"', $content);
        $this->assertStringContainsString('toggleMonthlyStats', $content);
        $this->assertStringContainsString('loadMonthlyStats', $content);
        $this->assertStringContainsString('cns-quality-monthly-stats', $content);
    }

    /** @test */
    public function edit_view_reads_correct_cell_indices_for_contract_booking_tank_and_parameters(): void
    {
        $content = file_get_contents(resource_path('views/cns/quality/edit.blade.php'));

        // Contract_ID is index 39, Booking_ID is 40, Tank_ID is 42
        $this->assertStringContainsString('contractId = grid.cells(rowId, 39) ? grid.cells(rowId, 39).getValue() : \'\';', $content);
        $this->assertStringContainsString('bookingId  = grid.cells(rowId, 40) ? grid.cells(rowId, 40).getValue() : \'\';', $content);
        $this->assertStringContainsString('tankId     = grid.cells(rowId, 42) ? grid.cells(rowId, 42).getValue() : \'\';', $content);

        // Parameters start at TOP 6, MIDDLE 14, BOTTOM 22
        $this->assertStringContainsString('6 + idx', $content);
        $this->assertStringContainsString('14 + idx', $content);
        $this->assertStringContainsString('22 + idx', $content);
    }

    /** @test */
    public function index_view_hides_correct_columns_and_handles_attachment_click(): void
    {
        $content = file_get_contents(resource_path('views/cns/quality/index.blade.php'));

        // Hidden columns 39, 40, 41, 42
        $this->assertStringContainsString('ProjectionGrid.setColumnHidden(39, true);', $content);
        $this->assertStringContainsString('ProjectionGrid.setColumnHidden(40, true);', $content);
        $this->assertStringContainsString('ProjectionGrid.setColumnHidden(41, true);', $content);
        $this->assertStringContainsString('ProjectionGrid.setColumnHidden(42, true);', $content);

        // Attachment click at index 38
        $this->assertStringContainsString('if (cInd === 38)', $content);
    }
}

