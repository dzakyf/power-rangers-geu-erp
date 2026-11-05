<?php

namespace Tests\Feature\CNS;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ShipmentInstructionSearchTest extends TestCase
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
    public function create_new_si_initializes_searchable_select2_fields(): void
    {
        $response = $this->actingAs($this->testUser())
            ->get('/cns-export-shipment-instruction-new');

        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('id="Contract_ID"', $html);
        $this->assertStringContainsString('id="Contract_No"', $html);
        $this->assertStringContainsString('id="Delivery_Terms"', $html);
        $this->assertStringContainsString('id="Shipper"', $html);
        $this->assertStringContainsString('id="Consignee"', $html);
        $this->assertStringContainsString('id="Notify"', $html);
        $this->assertStringContainsString('id="Destination_Port"', $html);

        $this->assertStringContainsString(
            "initShipmentInstructionSelect2('#Contract_ID, #Shipper, #Consignee, #Notify, #Destination_Port, #HS_Code')",
            $html
        );
        $this->assertStringContainsString("trigger('change.select2')", $html);
        $this->assertStringContainsString('Create Draft SI', $html);
    }
}
