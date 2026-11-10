<?php

namespace Tests\Unit\CNS;

use App\Http\Controllers\CNS\MasterTankController;
use App\MasterTank;
use App\MasterTankLog;
use Illuminate\Http\Request;
use Tests\TestCase;

class MasterTankControllerTest extends TestCase
{
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new MasterTankController();
    }

    /** @test */
    public function new_tank_action_returns_view()
    {
        $view = $this->controller->newTank();
        $this->assertEquals('cns.master.tank-new', $view->name());
    }

    /** @test */
    public function store_validation_fails_on_empty_request()
    {
        $request = new Request();
        $response = $this->controller->store($request);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
    }

    /** @test */
    public function show_returns_404_for_non_existent_tank()
    {
        $response = $this->controller->show(999999);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Data tangki tidak ditemukan.', $data['message']);
    }

    /** @test */
    public function update_returns_404_for_non_existent_tank()
    {
        $request = new Request(['name' => 'TK-999', 'capacity' => 1000, 'status' => 'ACTIVE']);
        $response = $this->controller->update($request, 999999);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
    }

    /** @test */
    public function destroy_returns_404_for_non_existent_tank()
    {
        $response = $this->controller->destroy(999999);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
    }

    /** @test */
    public function store_show_update_and_soft_delete_with_audit_logs()
    {
        $testTankName = 'TK-PHASE3-' . time();

        // 1. Store
        $createRequest = new Request([
            'name'     => $testTankName,
            'capacity' => 75000,
            'notes'    => 'Test Tank Phase 3',
            'status'   => 'ACTIVE',
        ]);
        $storeRes = $this->controller->store($createRequest);
        $this->assertEquals(200, $storeRes->getStatusCode());
        $storeData = json_decode($storeRes->getContent(), true);
        $this->assertTrue($storeData['success']);
        $tankId = $storeData['data']['id'];

        // Verify CREATE audit log
        $createLog = MasterTankLog::where('tank_id', $tankId)->where('action', 'CREATE')->first();
        $this->assertNotNull($createLog);
        $this->assertEquals($testTankName, $createLog->tank_name);

        // 2. Show
        $showRes = $this->controller->show($tankId);
        $this->assertEquals(200, $showRes->getStatusCode());
        $showData = json_decode($showRes->getContent(), true);
        $this->assertEquals($testTankName, $showData['data']['name']);
        $this->assertEquals(75000, $showData['data']['capacity']);

        // 3. Options
        $optRes = $this->controller->options();
        $this->assertEquals(200, $optRes->getStatusCode());
        $optData = json_decode($optRes->getContent(), true);
        $this->assertTrue($optData['success']);

        // 4. Balances
        $balRes = $this->controller->tankBalances();
        $this->assertEquals(200, $balRes->getStatusCode());
        $balData = json_decode($balRes->getContent(), true);
        $this->assertTrue($balData['success']);

        // 5. Update
        $updateRequest = new Request([
            'name'     => $testTankName . '-UPDATED',
            'capacity' => 85000,
            'notes'    => 'Updated note Phase 3',
            'status'   => 'ACTIVE',
        ]);
        $updateRes = $this->controller->update($updateRequest, $tankId);
        $this->assertEquals(200, $updateRes->getStatusCode());

        // Verify UPDATE audit log
        $updateLog = MasterTankLog::where('tank_id', $tankId)->where('action', 'UPDATE')->first();
        $this->assertNotNull($updateLog);

        // 6. Logs endpoint
        $logsRes = $this->controller->logs($tankId);
        $this->assertEquals(200, $logsRes->getStatusCode());
        $logsData = json_decode($logsRes->getContent(), true);
        $this->assertTrue($logsData['success']);
        $this->assertGreaterThanOrEqual(2, count($logsData['logs']));

        // 7. Soft Delete
        $deleteRes = $this->controller->destroy($tankId);
        $this->assertEquals(200, $deleteRes->getStatusCode());
        $deleteData = json_decode($deleteRes->getContent(), true);
        $this->assertTrue($deleteData['success']);

        // Verify DELETE audit log
        $deleteLog = MasterTankLog::where('tank_id', $tankId)->where('action', 'DELETE')->first();
        $this->assertNotNull($deleteLog);

        // Verify soft-deleted
        $this->assertNull(MasterTank::find($tankId));
        $this->assertNotNull(MasterTank::withTrashed()->find($tankId));

        // Clean up test logs and row
        MasterTankLog::where('tank_id', $tankId)->delete();
        MasterTank::withTrashed()->where('id', $tankId)->forceDelete();
    }
}
