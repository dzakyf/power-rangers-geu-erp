<?php

namespace Tests\Feature\CNS;

use App\ShipmentInstruction;
use App\User;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShipmentInstructionDeleteTest extends TestCase
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
    public function it_renders_delete_si_button_on_si_list_page(): void
    {
        $response = $this->actingAs($this->testUser())
            ->get('/cns-export-shipment-instruction');

        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString('id="DeleteSI"', $html);
        $this->assertStringContainsString('value="Delete SI"', $html);
        $this->assertStringContainsString('onclick="DeleteSI()"', $html);
        $this->assertStringContainsString('cns-export-shipment-instruction-delete', $html);
    }

    /** @test */
    public function it_fails_when_id_is_missing(): void
    {
        $response = $this->actingAs($this->testUser())
            ->postJson('/cns-export-shipment-instruction-delete', []);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
        ]);
    }

    /** @test */
    public function it_fails_when_si_not_found(): void
    {
        $response = $this->actingAs($this->testUser())
            ->postJson('/cns-export-shipment-instruction-delete', ['id' => 999999999]);

        $response->assertStatus(404);
        $response->assertJson([
            'status' => 'error',
        ]);
    }

    /** @test */
    public function it_cannot_delete_final_si(): void
    {
        $user = $this->testUser();
        $si = ShipmentInstruction::create([
            'SI_No' => 'TEST-SI-FINAL-' . uniqid(),
            'Final_SI_No' => 'TEST-FINALSI-' . uniqid(),
            'SI_Date' => now()->toDateString(),
            'Status' => 'FINAL',
            'Created_By' => $user->username,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/cns-export-shipment-instruction-delete', ['id' => $si->id]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Shipping Instruction yang sudah menjadi Final SI tidak dapat dihapus.',
        ]);

        $this->assertDatabaseHas('shipment_instructions', ['id' => $si->id]);
    }

    /** @test */
    public function it_successfully_deletes_si_and_releases_assigned_containers(): void
    {
        $user = $this->testUser();
        $orderNo = 'TEST-ORDER-' . uniqid();
        $siNo = 'TEST-SI-' . uniqid();

        $schedule = VesselSchedule::create([
            'Order_No' => $orderNo,
            'Vessel' => 'MV Test',
            'Created_By' => $user->username,
        ]);

        $containerId = DB::table('vessel_schedule_dtls')->insertGetId([
            'Task_ID' => $schedule->id,
            'Order_No' => $orderNo,
            'Container' => 'TEST1234567',
            'Seal' => 'SEAL001',
            'SI_No' => $siNo,
            'Status' => 'ACTIVE',
            'Created_By' => $user->username,
            'Created_Date' => now(),
            'Updated_By' => $user->username,
            'Updated_Date' => now(),
        ]);

        $si = ShipmentInstruction::create([
            'SI_No' => $siNo,
            'Order_No' => $orderNo,
            'SI_Date' => now()->toDateString(),
            'Status' => 'CREATED',
            'Created_By' => $user->username,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/cns-export-shipment-instruction-delete', [
                'id' => $si->id,
                'Task' => $si->SI_No,
            ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'message' => 'Shipping Instruction berhasil dihapus.',
        ]);

        $this->assertDatabaseMissing('shipment_instructions', ['id' => $si->id]);

        $container = DB::table('vessel_schedule_dtls')->where('ID', $containerId)->first();
        $this->assertNull($container->SI_No);
    }
}
