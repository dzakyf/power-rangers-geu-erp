<?php

namespace Tests\Feature\CNS;

use App\Services\Traceability\TraceabilityPriceAllocator;
use Tests\TestCase;

class TraceabilityPriceAllocatorTest extends TestCase
{
    /** @test */
    public function it_allocates_by_quantity_and_preserves_the_exact_total(): void
    {
        $allocations = TraceabilityPriceAllocator::allocate(720000.00, [73.8, 26.2]);

        $this->assertSame(531360.00, $allocations[0]);
        $this->assertSame(188640.00, $allocations[1]);
        $this->assertSame(720000.00, array_sum($allocations));
    }

    /** @test */
    public function it_puts_rounding_remainder_on_the_last_fragment(): void
    {
        $allocations = TraceabilityPriceAllocator::allocate(100.00, [1.0, 1.0, 1.0]);

        $this->assertSame(100.00, array_sum($allocations));
        $this->assertSame(33.33, $allocations[0]);
        $this->assertSame(33.33, $allocations[1]);
        $this->assertSame(33.34, $allocations[2]);
    }

    /** @test */
    public function it_handles_zero_quantities_without_dividing_by_zero(): void
    {
        $allocations = TraceabilityPriceAllocator::allocate(50.00, [0.0, 0.0]);

        $this->assertSame(25.00, $allocations[0]);
        $this->assertSame(25.00, $allocations[1]);
    }
}
