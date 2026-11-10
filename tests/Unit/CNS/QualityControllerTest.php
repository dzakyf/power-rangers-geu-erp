<?php

namespace Tests\Unit\CNS;

use App\Http\Controllers\CNS\QualityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QualityControllerTest extends TestCase
{
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new QualityController();
    }

    /** @test */
    public function insert_daily_qc_without_tank_id_returns_422()
    {
        $request = new Request([
            'Created_At'  => '2026-11-09',
            'QC_Type'     => 'DAILY_QC',
            'QC_Category' => 'INTERNAL',
            'Tank_ID'     => null,
        ]);

        $response = $this->controller->insertQualityControl($request);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertStringContainsString('Nama Tangki wajib dipilih', $data['message']);
    }

    /** @test */
    public function get_monthly_stats_returns_expected_structure_with_mocked_db()
    {
        $mockQuery = \Mockery::mock();
        $mockQuery->shouldReceive('join')->once()->andReturnSelf();
        $mockQuery->shouldReceive('where')->with('qc.Is_Deleted', 0)->once()->andReturnSelf();
        $mockQuery->shouldReceive('where')->with('d.Sample_Level', 'AVERAGE')->once()->andReturnSelf();
        $mockQuery->shouldReceive('whereBetween')->once()->andReturnSelf();
        $mockQuery->shouldReceive('selectRaw')->once()->andReturnSelf();
        $mockQuery->shouldReceive('first')->once()->andReturn((object) [
            'min_ffa_palmitic' => '2.50',
            'max_ffa_palmitic' => '4.80',
            'min_ffa_oleic'    => '2.40',
            'max_ffa_oleic'    => '4.60',
            'min_miu'          => '1.20',
            'max_miu'          => '2.10',
            'min_sulphur'      => '15.00',
            'max_sulphur'      => '30.00',
            'min_iv'           => '55.00',
            'max_iv'           => '60.00',
            'min_chlorides'    => '25.00',
            'max_chlorides'    => '40.00',
            'min_phosphorus'   => '12.00',
            'max_phosphorus'   => '20.00',
            'min_metals'       => '8.00',
            'max_metals'       => '16.00',
            'total_tests'      => 2,
        ]);

        DB::shouldReceive('table')->with('cns_quality_controls as qc')->once()->andReturn($mockQuery);

        $request = new Request([
            'month'    => '2026-11',
            'category' => 'ALL',
            'type'     => 'ALL',
        ]);

        $response = $this->controller->getMonthlyStats($request);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('2026-11', $data['month']);
        $this->assertEquals(2, $data['total_tests']);

        $params = collect($data['parameters'])->keyBy('key');
        $this->assertEquals(2.50, $params['ffa_palmitic']['min']);
        $this->assertEquals(4.80, $params['ffa_palmitic']['max']);
        $this->assertEquals(1.20, $params['miu']['min']);
        $this->assertEquals(2.10, $params['miu']['max']);
        $this->assertEquals(15.00, $params['sulphur']['min']);
        $this->assertEquals(30.00, $params['sulphur']['max']);
        $this->assertEquals(55.00, $params['iv']['min']);
        $this->assertEquals(60.00, $params['iv']['max']);
    }
}
