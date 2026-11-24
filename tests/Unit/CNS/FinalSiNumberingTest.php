<?php

namespace Tests\Unit\CNS;

use App\ShippingInstruction;
use Tests\TestCase;

class FinalSiNumberingTest extends TestCase
{
    /** @test */
    public function it_starts_from_001_when_no_existing_final_si_numbers(): void
    {
        $no = ShippingInstruction::nextFinalSiNoFromExisting([], 'FINALSI/EXP/', '/XI/2026');

        $this->assertSame('FINALSI/EXP/001/XI/2026', $no);
    }

    /** @test */
    public function it_uses_max_sequence_plus_one_even_with_gaps(): void
    {
        $existing = [
            'FINALSI/EXP/001/XI/2026',
            'FINALSI/EXP/003/XI/2026',
        ];

        $no = ShippingInstruction::nextFinalSiNoFromExisting($existing, 'FINALSI/EXP/', '/XI/2026');

        $this->assertSame('FINALSI/EXP/004/XI/2026', $no);
    }

    /** @test */
    public function it_handles_sequences_above_999(): void
    {
        $existing = [
            'FINALSI/EXP/999/XI/2026',
            'FINALSI/EXP/1000/XI/2026',
        ];

        $no = ShippingInstruction::nextFinalSiNoFromExisting($existing, 'FINALSI/EXP/', '/XI/2026');

        $this->assertSame('FINALSI/EXP/1001/XI/2026', $no);
    }

    /** @test */
    public function both_controllers_use_shared_final_si_generator_and_guard_existing_number(): void
    {
        $fsiController = file_get_contents(app_path('Http/Controllers/CNS/FinalShippingInstructionController.php'));
        $siController = file_get_contents(app_path('Http/Controllers/CNS/ShippingInstructionController.php'));

        $this->assertStringContainsString('ShippingInstruction::generateNextFinalSiNo', $fsiController);
        $this->assertStringContainsString('!empty($si->Final_SI_No)', $fsiController);
        $this->assertStringContainsString('ShippingInstruction::generateNextFinalSiNo', $siController);
        $this->assertStringContainsString('!empty($si->Final_SI_No)', $siController);
    }
}
