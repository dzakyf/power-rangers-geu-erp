<?php

namespace Tests\Feature\CNS;

use App\CnsSalesForecast;
use App\Http\Controllers\CNS\SalesForecastController;
use App\Services\CNS\CnsPermissionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SalesForecastTest extends TestCase
{
    public function test_menu_and_permission_mapping_are_registered()
    {
        $menus = require base_path('database/seeds/data/menu_lists_cns.php');

        $this->assertArrayHasKey('SI00354', $menus);
        $this->assertSame(
            ['Sales Forecast & Pipeline Tracking', 'cns-sales-forecast', 'CNS', 4, 'SS00084', 7],
            $menus['SI00354']
        );
        $this->assertSame('sales_forecast', CnsPermissionService::getSlug('SI00354'));
        $this->assertSame('SI00354', CnsPermissionService::getMenuId('sales_forecast'));
    }

    public function test_sales_forecast_routes_and_middleware_are_registered()
    {
        $routes = collect(Route::getRoutes()->getRoutes());

        $expected = [
            ['GET', 'cns-sales-forecast', 'can:cns.sales_forecast.view'],
            ['GET', 'cns-sales-forecast-data', 'can:cns.sales_forecast.view'],
            ['POST', 'cns-sales-forecast', 'can:cns.sales_forecast.create'],
            ['POST', 'cns-sales-forecast/{id}/update', 'can:cns.sales_forecast.edit'],
            ['POST', 'cns-sales-forecast/{id}/delete', 'can:cns.sales_forecast.delete'],
        ];

        foreach ($expected as [$method, $uri, $middleware]) {
            $route = $routes->first(function ($r) use ($method, $uri) {
                return $r->uri() === $uri && in_array($method, $r->methods(), true);
            });
            $this->assertNotNull($route, "Route {$method} {$uri} not found");
            $this->assertContains($middleware, $route->gatherMiddleware());
        }
    }

    public function test_model_and_view_exist_and_blade_compiles()
    {
        $this->assertSame('cns_sales_forecasts', (new CnsSalesForecast())->getTable());
        $this->assertTrue(view()->exists('cns.sales.sales-forecast'));

        $compiled = app('blade.compiler')->compileString(
            file_get_contents(resource_path('views/cns/sales/sales-forecast.blade.php'))
        );
        $this->assertNotEmpty($compiled);
    }

    public function test_forecast_stuffing_progress_and_bc_pipeline_calculation()
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite driver not available');
        }

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        DB::purge('sqlite');

        Schema::create('cns_sales_forecasts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('period_month', 7)->unique();
            $table->decimal('planned_mt', 15, 3)->default(0);
            $table->decimal('planned_value', 18, 2)->default(0);
            $table->string('notes', 255)->nullable();
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('cns_buyer_contracts', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Contract_No', 100);
            $table->string('Buyer_Name', 150);
            $table->decimal('Quantity_MT', 15, 3)->default(0);
            $table->decimal('Price_Per_MT', 18, 2)->default(0);
        });

        Schema::create('cns_buyer_contract_bookings', function (Blueprint $table) {
            $table->increments('ID');
            $table->unsignedInteger('Contract_ID');
            $table->string('Label', 20);
            $table->string('Booking_No', 100);
            $table->dateTime('Created_Date')->nullable();
            $table->tinyInteger('Checklist_Verified')->default(0);
            $table->tinyInteger('Finance_Payment_Verified')->default(0);
            $table->tinyInteger('Payment_Approved')->default(0);
        });

        Schema::create('vessel_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('Booking_ID')->nullable();
            $table->string('Order_No', 100)->nullable();
            $table->date('Stuffing')->nullable();
            $table->date('ETD')->nullable();
            $table->string('Qty', 50)->nullable();
        });

        Schema::create('vessel_schedule_dtls', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Order_No', 100)->nullable();
            $table->double('Netto_MT')->default(0);
            $table->string('Status', 50)->default('ACTIVE');
        });

        $controller = new SalesForecastController();
        $controller->store(Request::create('/cns-sales-forecast', 'POST', [
            'period_month'  => '2026-11',
            'planned_mt'    => 200,
            'planned_value' => 150000,
            'notes'         => 'Target November',
        ]));

        DB::table('cns_buyer_contracts')->insert([
            ['ID' => 1, 'Contract_No' => 'CTR-001', 'Buyer_Name' => 'Buyer A', 'Quantity_MT' => 200, 'Price_Per_MT' => 800],
            ['ID' => 2, 'Contract_No' => 'CTR-002', 'Buyer_Name' => 'Buyer B', 'Quantity_MT' => 100, 'Price_Per_MT' => 750],
        ]);

        DB::table('cns_buyer_contract_bookings')->insert([
            ['ID' => 10, 'Contract_ID' => 1, 'Label' => 'BC 1', 'Booking_No' => 'BC-010', 'Created_Date' => '2026-11-05 10:00:00', 'Checklist_Verified' => 1, 'Finance_Payment_Verified' => 1, 'Payment_Approved' => 1],
            ['ID' => 11, 'Contract_ID' => 1, 'Label' => 'BC 2', 'Booking_No' => 'BC-011', 'Created_Date' => '2026-11-06 10:00:00', 'Checklist_Verified' => 0, 'Finance_Payment_Verified' => 1, 'Payment_Approved' => 0],
            ['ID' => 12, 'Contract_ID' => 2, 'Label' => 'BC 1', 'Booking_No' => 'BC-012', 'Created_Date' => '2026-11-07 10:00:00', 'Checklist_Verified' => 0, 'Finance_Payment_Verified' => 0, 'Payment_Approved' => 0],
        ]);

        DB::table('vessel_schedules')->insert([
            ['id' => 100, 'Booking_ID' => 10, 'Order_No' => 'ORD-1', 'Stuffing' => '2026-11-10', 'ETD' => '2026-11-15', 'Qty' => '50'],
            ['id' => 101, 'Booking_ID' => 11, 'Order_No' => 'ORD-2', 'Stuffing' => '2026-11-12', 'ETD' => '2026-11-18', 'Qty' => '50'],
            ['id' => 102, 'Booking_ID' => 12, 'Order_No' => 'ORD-3', 'Stuffing' => '2026-11-14', 'ETD' => '2026-11-20', 'Qty' => '25'],
        ]);

        DB::table('vessel_schedule_dtls')->insert([
            ['Order_No' => 'ORD-1', 'Netto_MT' => 75.5, 'Status' => 'ACTIVE'],
            ['Order_No' => 'ORD-2', 'Netto_MT' => 24.5, 'Status' => 'ACTIVE'],
            ['Order_No' => 'ORD-3', 'Netto_MT' => 25.0, 'Status' => 'CANCELED'],
        ]);

        $data = $controller->buildDashboardData(2026);

        $nov = collect($data['monthly'])->firstWhere('period_month', '2026-11');
        $this->assertNotNull($nov);
        $this->assertSame(200.0, (float) $nov['planned_mt']);
        $this->assertSame(100.0, (float) $nov['stuffed_mt']);
        $this->assertSame(100.0, (float) $nov['remaining_mt']);
        $this->assertSame(50.0, (float) $nov['achieved_pct']);
        $this->assertSame(150000.0, (float) $nov['planned_value']);
        $this->assertSame(80000.0, (float) $nov['stuffed_value']);
        $this->assertSame(3, $nov['bc_count']);

        $this->assertSame(200.0, (float) $data['kpis']['total_planned_mt']);
        $this->assertSame(100.0, (float) $data['kpis']['total_stuffed_mt']);
        $this->assertSame(50.0, (float) $data['kpis']['overall_pct']);
        $this->assertSame(150000.0, (float) $data['kpis']['total_planned_value']);
        $this->assertSame(80000.0, (float) $data['kpis']['total_stuffed_value']);
        $this->assertSame(3, $data['kpis']['total_bc']);

        $breakdown = collect($data['pipeline']['breakdown_rows'])->keyBy('contract_no');
        $this->assertSame(100.0, (float) $breakdown['CTR-001']['stuffed_mt']);
        $this->assertSame(50.0, (float) $breakdown['CTR-001']['progress_pct']);
        $this->assertSame(80000.0, (float) $breakdown['CTR-001']['stuffed_value']);
        $this->assertCount(2, $breakdown['CTR-001']['bcs']);
        $this->assertSame('Approved', $breakdown['CTR-001']['bcs'][0]['payment_status']);
        $this->assertTrue($breakdown['CTR-001']['bcs'][0]['checklist_verified']);
        $this->assertSame('Finance Verified', $breakdown['CTR-001']['bcs'][1]['payment_status']);
        $this->assertSame('Unpaid', $breakdown['CTR-002']['bcs'][0]['payment_status']);
    }
}
