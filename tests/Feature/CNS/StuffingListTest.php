<?php

namespace Tests\Feature\CNS;

use App\User;
use App\VesselSchedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StuffingListTest extends TestCase
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
    public function stuffing_list_shows_one_row_per_schedule_when_duplicate_schedules_exist(): void
    {
        $orderNo = 'TEST-STUFF-LIST-' . uniqid();
        $taskId = 'VS/TEST/' . uniqid();
        $this->makeVesselSchedule($orderNo, $taskId);
        $this->makeVesselSchedule($orderNo, $taskId);
        $this->makeStuffingContainer($taskId, $orderNo, 'TESTU7654321', 'SEAL-LIST');

        $_SERVER['HTTP_ACCEPT'] = 'text/xml';

        ob_start();
        $response = $this->actingAs($this->testUser())
            ->get('/xml-cns-stuffing?tgl1=2026-10-01&tgl2=2026-10-01');
        $content = ob_get_clean();

        $response->assertOk();

        $this->assertSame(
            1,
            substr_count($content, '<row '),
            'The stuffing list must render one row for duplicate schedule records.'
        );
        $this->assertSame(
            1,
            substr_count($content, 'TESTU7654321'),
            'The linked container must appear only once.'
        );
    }

    /** @test */
    public function stuffing_status_is_red_when_not_started(): void
    {
        $orderNo = 'TEST-STUFF-STATUS-RED-' . uniqid();
        $taskId = 'VS/TEST/' . uniqid();
        $this->makeVesselSchedule($orderNo, $taskId);

        $content = $this->getStuffingXml();

        $this->assertStringContainsString('background-color: #ffccccff', $content);
    }

    /** @test */
    public function stuffing_status_is_yellow_when_containers_exist_but_done_is_unchecked(): void
    {
        $orderNo = 'TEST-STUFF-STATUS-YELLOW-' . uniqid();
        $taskId = 'VS/TEST/' . uniqid();
        $this->makeVesselSchedule($orderNo, $taskId);
        $this->makeStuffingContainer($taskId, $orderNo, 'TESTU7654322', 'SEAL-YELLOW');

        $content = $this->getStuffingXml();

        $this->assertStringContainsString('background-color: #feffccff', $content);
        $this->assertStringNotContainsString('background-color: #ccffcfff', $content);
    }

    /** @test */
    public function stuffing_status_is_green_when_done_is_checked(): void
    {
        $orderNo = 'TEST-STUFF-STATUS-GREEN-' . uniqid();
        $taskId = 'VS/TEST/' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo, $taskId);
        DB::table('vessel_schedules')->where('id', $vs->id)->update(['Checklist_Done_Kapal_Berangkat' => 1]);

        $content = $this->getStuffingXml();

        $this->assertStringContainsString('background-color: #ccffcfff', $content);
    }

    private function getStuffingXml(): string
    {
        $_SERVER['HTTP_ACCEPT'] = 'text/xml';

        ob_start();
        $response = $this->actingAs($this->testUser())
            ->get('/xml-cns-stuffing?tgl1=2026-10-01&tgl2=2026-10-01');
        $content = ob_get_clean();

        $response->assertOk();

        return $content;
    }

    /** @test */
    public function duplicate_cleanup_removes_identical_schedules_and_preserves_log_references(): void
    {
        $orderNo = 'TEST-STUFF-CLEAN-' . uniqid();
        $taskId = 'VS/TEST/' . uniqid();
        $canonical = $this->makeVesselSchedule($orderNo, $taskId);
        $duplicate = $this->makeVesselSchedule($orderNo, $taskId);
        $this->makeStuffingContainer($taskId, $orderNo, 'TESTU1111111', 'SEAL-CLEAN');

        $logId = DB::table('vessel_schedule_logs')->insertGetId([
            'Task_ID' => $taskId,
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
            'Action_By' => $this->testUser()->username,
            'Action_Date' => now(),
            'Action_Status' => 'UPDATE DATA',
            'Last_ID' => $duplicate->id,
        ]);

        $this->artisan('cns:dedupe-vessel-schedules', ['--force' => true]);

        $this->assertSame(
            1,
            VesselSchedule::where('Task_ID', $taskId)->count(),
            'Only the canonical schedule should remain.'
        );
        $this->assertSame(
            $canonical->id,
            (int) DB::table('vessel_schedule_logs')->where('id', $logId)->value('Last_ID'),
            'Log references must point to the kept schedule.'
        );
        $this->assertSame(
            1,
            DB::table('vessel_schedule_dtls')->where('Task_ID', $taskId)->count(),
            'Stuffing rows must not be deleted.'
        );
    }

    /** @test */
    public function duplicate_cleanup_skips_schedules_with_different_data(): void
    {
        $orderNo = 'TEST-STUFF-DIVERGED-' . uniqid();
        $taskId = 'VS/TEST/' . uniqid();
        $this->makeVesselSchedule($orderNo, $taskId);
        $this->makeVesselSchedule($orderNo, $taskId, 'Different note');

        $this->artisan('cns:dedupe-vessel-schedules', ['--force' => true]);

        $this->assertSame(
            2,
            VesselSchedule::where('Task_ID', $taskId)->count(),
            'Diverged schedules must be reported and left untouched.'
        );
    }

    private function makeVesselSchedule(string $orderNo, string $taskId, string $note = 'Initial note'): VesselSchedule
    {
        return VesselSchedule::create([
            'Task_ID' => $taskId,
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
            'Note' => $note,
            'Created_By' => $this->testUser()->username,
            'Created_Date' => now(),
        ]);
    }

    private function makeStuffingContainer(string $taskId, string $orderNo, string $container, string $seal): void
    {
        DB::table('vessel_schedule_dtls')->insert([
            'Task_ID' => $taskId,
            'Order_No' => $orderNo,
            'Container' => $container,
            'Seal' => $seal,
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
}
