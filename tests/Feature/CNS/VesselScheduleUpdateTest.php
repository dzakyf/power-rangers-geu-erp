<?php

namespace Tests\Feature\CNS;

use App\User;
use App\VesselSchedule;
use App\VesselScheduleLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VesselScheduleUpdateTest extends TestCase
{
    use DatabaseTransactions;

    /** @var User|null */
    private $user;

    private function testUser(): User
    {
        if ($this->user) {
            return $this->user;
        }

        $user = User::query()->orderBy('id')->first();

        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        return $this->user = $user;
    }

    /** @test */
    public function updating_vessel_schedule_does_not_create_duplicate_record(): void
    {
        $oldOrderNo = 'TEST-VS-OLD-' . uniqid();
        $newOrderNo = 'TEST-VS-NEW-' . uniqid();
        $vs = $this->makeVesselSchedule($oldOrderNo);

        $response = $this->actingAs($this->testUser())
            ->post('/cns-vessel-schedule-detail-submit', $this->updatePayload($vs->id, $newOrderNo));

        $response->assertOk();

        $this->assertSame(
            0,
            VesselSchedule::where('Task_ID', $vs->Task_ID)->where('Order_No', $oldOrderNo)->count(),
            'The old order number must no longer exist.'
        );
        $this->assertSame(
            1,
            VesselSchedule::where('Task_ID', $vs->Task_ID)->where('Order_No', $newOrderNo)->count(),
            'Updating a schedule must leave exactly one record.'
        );
        $this->assertSame(
            1,
            VesselScheduleLog::where('Last_ID', $vs->id)->where('Action_Status', 'UPDATE DATA')->count(),
            'The update must be written to the log table.'
        );
    }

    /** @test */
    public function updating_vessel_schedule_syncs_linked_stuffing_containers(): void
    {
        $oldOrderNo = 'TEST-VS-STUFF-OLD-' . uniqid();
        $newOrderNo = 'TEST-VS-STUFF-NEW-' . uniqid();
        $vs = $this->makeVesselSchedule($oldOrderNo);
        $this->makeStuffingContainer($vs, $oldOrderNo);

        $response = $this->actingAs($this->testUser())
            ->post('/cns-vessel-schedule-detail-submit', $this->updatePayload($vs->id, $newOrderNo));

        $response->assertOk();

        $this->assertSame(
            1,
            DB::table('vessel_schedule_dtls')
                ->where('Task_ID', $vs->Task_ID)
                ->where('Order_No', $newOrderNo)
                ->count(),
            'Stuffing rows must follow the updated order number.'
        );
    }

    private function makeVesselSchedule(string $orderNo): VesselSchedule
    {
        return VesselSchedule::create([
            'Task_ID' => 'VS/TEST/' . uniqid(),
            'Order_No' => $orderNo,
            'Qty' => '1 x 20 FT',
            'Qty1' => '1',
            'Qty2' => '20 FT',
            'Vessel' => 'TEST VESSEL',
            'POL' => 'TEST POL',
            'POD' => 'TEST POD',
            'Stuffing' => '2026-10-01',
            'Checking_BC' => '2026-10-02',
            'Closing_CY' => '2026-10-03',
            'ETD' => '2026-10-04',
            'ETA' => '2026-10-05',
            'Note' => 'Initial note',
            'Created_By' => $this->testUser()->username,
            'Created_Date' => now(),
        ]);
    }

    private function makeStuffingContainer(VesselSchedule $vs, string $orderNo): void
    {
        DB::table('vessel_schedule_dtls')->insert([
            'Task_ID' => $vs->Task_ID,
            'Order_No' => $orderNo,
            'Container' => 'TESTU1234567',
            'Seal' => 'SEAL123',
            'Time_From' => '08:00:00',
            'Time_To' => '09:00:00',
            'Date' => '2026-10-01',
            'Police_No' => 'B 1234 TEST',
            'Tare' => 2000,
            'Netto_KG' => 18000,
            'Netto_MT' => 18,
            'Gross_KG' => 20000,
            'Gross_MT' => 20,
            'Note' => 'Test container',
            'Status' => 'CLOSED',
            'Created_By' => $this->testUser()->username,
            'Created_Date' => now(),
            'Updated_By' => $this->testUser()->username,
            'Updated_Date' => now(),
        ]);
    }

    private function updatePayload(int $id, string $orderNo): array
    {
        return [
            'id' => $id,
            'Order_No' => $orderNo,
            'Qty1' => '1',
            'Qty2' => '20 FT',
            'Vessel' => 'UPDATED VESSEL',
            'POL' => 'UPDATED POL',
            'POD' => 'UPDATED POD',
            'Stuffing' => '2026-10-11',
            'Checking_BC' => '2026-10-12',
            'Closing_CY' => '2026-10-13',
            'ETD' => '2026-10-14',
            'ATD' => '2026-10-15',
            'ETA' => '2026-10-16',
            'ATA' => '2026-10-17',
            'Note' => 'Updated note',
        ];
    }
}
