<?php

namespace Tests\Feature\CNS;

use App\BuyerContract;
use App\CnsProductAllocation;
use App\Http\Controllers\CNS\ProductAllocationController;
use App\Services\CNS\CnsPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductAllocationTest extends TestCase
{
    public function test_menu_and_permission_mapping_are_registered()
    {
        $menus = require base_path('database/seeds/data/menu_lists_cns.php');

        $this->assertArrayHasKey('SI00352', $menus);
        $this->assertSame(
            ['Product Allocation & Volume Planning', 'cns-product-allocation', 'CNS', 4, 'SS00084', 6],
            $menus['SI00352']
        );
        $this->assertSame('product_allocation', CnsPermissionService::getSlug('SI00352'));
        $this->assertSame('SI00352', CnsPermissionService::getMenuId('product_allocation'));
    }

    public function test_product_allocation_routes_and_middleware_are_registered()
    {
        $routes = collect(Route::getRoutes()->getRoutes());

        $expected = [
            ['GET', 'cns-product-allocation', 'can:cns.product_allocation.view'],
            ['GET', 'cns-product-allocation-data', 'can:cns.product_allocation.view'],
            ['GET', 'xml-cns-product-allocation', 'can:cns.product_allocation.view'],
            ['GET', 'xml-cns-upcoming-export', 'can:cns.product_allocation.view'],
            ['GET', 'cns-product-allocation-excel', 'can:cns.product_allocation.view'],
            ['POST', 'cns-product-allocation', 'can:cns.product_allocation.create'],
            ['POST', 'cns-product-allocation/{id}/update', 'can:cns.product_allocation.edit'],
            ['POST', 'cns-product-allocation/{id}/delete', 'can:cns.product_allocation.delete'],
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
        $this->assertSame('cns_product_allocations', (new CnsProductAllocation())->getTable());
        $this->assertTrue(view()->exists('cns.sales.product-allocation'));

        $compiled = app('blade.compiler')->compileString(
            file_get_contents(resource_path('views/cns/sales/product-allocation.blade.php'))
        );

        $this->assertStringContainsString('Product Allocation & Volume Planning', $compiled);
        $this->assertStringContainsString('Upcoming Export Volume', $compiled);
        $this->assertStringContainsString('AllocationGrid', $compiled);
        $this->assertStringContainsString('UpcomingGrid', $compiled);
    }

    public function test_store_rejects_invalid_payload_before_db()
    {
        $this->expectException(ValidationException::class);

        $controller = new ProductAllocationController();
        $controller->store(Request::create('/cns-product-allocation', 'POST', [
            'product_name' => 'UCO',
            'period_month' => 'invalid-month',
            'planned_mt'   => -5,
        ]));
    }
}
