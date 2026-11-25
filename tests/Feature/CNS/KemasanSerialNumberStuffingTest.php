<?php

namespace Tests\Feature\CNS;

use App\CnsKemasanSerialNumber;
use App\CnsMasterKemasan;
use App\Services\CNS\NonUcoInventoryService;
use App\Services\CNS\StuffingContainerService;
use App\VesselSchedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class KemasanSerialNumberStuffingTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function stuffing_reserves_releases_and_deducts_flexibag_by_serial_number(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database connection unavailable: ' . $e->getMessage());
        }

        if (!Schema::hasTable('cns_kemasan_serial_numbers') || !Schema::hasColumn('vessel_schedule_dtls', 'serial_number')) {
            $this->markTestSkipped('Migration 2026_11_22_000001_create_cns_kemasan_serial_numbers_table has not been run.');
        }

        $kemasan = CnsMasterKemasan::create([
            'Name' => 'Flexibag 24KL ' . uniqid(),
            'product_type' => 'Kemasan',
            'Volume' => 24000,
            'Is_Ampas' => false,
            'Max_Stock' => 100,
            'Unit' => 'PCS',
            'Price' => 1000000,
        ]);

        $sn1 = 'FB-SN-' . uniqid() . '-1';
        $sn2 = 'FB-SN-' . uniqid() . '-2';

        CnsKemasanSerialNumber::create([
            'kemasan_id' => $kemasan->Kemasan_ID,
            'serial_number' => $sn1,
            'status' => CnsKemasanSerialNumber::STATUS_AVAILABLE,
        ]);
        CnsKemasanSerialNumber::create([
            'kemasan_id' => $kemasan->Kemasan_ID,
            'serial_number' => $sn2,
            'status' => CnsKemasanSerialNumber::STATUS_AVAILABLE,
        ]);

        $nonUcoService = new NonUcoInventoryService();
        $nonUcoService->recordStockMutation($kemasan->Name, 'Flexibag', 'PCS', 'IN', 5, 'GR-TEST', 'Initial stock', 'tester');

        $schedule = new VesselSchedule();
        $schedule->Task_ID = 'TASK-' . uniqid();
        $schedule->Order_No = 'ORD-' . uniqid();
        $schedule->Booking_No = 'BK-' . uniqid();
        $schedule->Shipping_Method = 'CONTAINER';
        $schedule->Qty1 = 10;
        $schedule->save();

        $containerService = new StuffingContainerService();

        $c1 = $containerService->save($schedule, [
            'Date' => date('Y-m-d'),
            'Container' => 'CONT-001',
            'Seal' => 'SEAL-001',
            'serial_number' => $sn1,
        ]);

        $this->assertSame(
            CnsKemasanSerialNumber::STATUS_RESERVED,
            CnsKemasanSerialNumber::where('serial_number', $sn1)->value('status')
        );

        $rejected = false;
        try {
            $containerService->save($schedule, [
                'Date' => date('Y-m-d'),
                'Container' => 'CONT-002',
                'Seal' => 'SEAL-002',
                'serial_number' => $sn1,
            ]);
        } catch (ValidationException $e) {
            $rejected = true;
        }
        $this->assertTrue($rejected, 'Reserved serial number must not be reusable by another container.');

        $containerService->cancelMany($schedule, [$c1->ID]);
        $this->assertSame(
            CnsKemasanSerialNumber::STATUS_AVAILABLE,
            CnsKemasanSerialNumber::where('serial_number', $sn1)->value('status')
        );

        $containerService->save($schedule, [
            'Date' => date('Y-m-d'),
            'Container' => 'CONT-003',
            'Seal' => 'SEAL-003',
            'serial_number' => $sn1,
        ]);
        $containerService->save($schedule, [
            'Date' => date('Y-m-d'),
            'Container' => 'CONT-004',
            'Seal' => 'SEAL-004',
            'serial_number' => $sn2,
        ]);

        $mutation = $nonUcoService->recordStuffingDeduction($schedule, 'tester');

        $this->assertNotNull($mutation);
        $this->assertSame(2.0, (float) $mutation->qty_out);
        $this->assertSame(3.0, (float) $mutation->current_balance);
        $this->assertStringContainsString($sn1, $mutation->notes);
        $this->assertStringContainsString($sn2, $mutation->notes);
        $this->assertSame(CnsKemasanSerialNumber::STATUS_USED, CnsKemasanSerialNumber::where('serial_number', $sn1)->value('status'));
        $this->assertSame(CnsKemasanSerialNumber::STATUS_USED, CnsKemasanSerialNumber::where('serial_number', $sn2)->value('status'));
    }
}
