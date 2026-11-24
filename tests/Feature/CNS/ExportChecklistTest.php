<?php

namespace Tests\Feature\CNS;

use App\Services\CNS\CnsPermissionService;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExportChecklistTest extends TestCase
{
    public function test_menu_and_permission_mapping_are_registered()
    {
        $menus = require base_path('database/seeds/data/menu_lists_cns.php');

        $this->assertArrayHasKey('SI00350', $menus);
        $this->assertSame(['Export Checklist', 'cns-export-checklist', 'CNS', 4, 'SS00082', 10], $menus['SI00350']);
        $this->assertSame('export_checklist', CnsPermissionService::getSlug('SI00350'));
        $this->assertSame('SI00350', CnsPermissionService::getMenuId('export_checklist'));
    }

    public function test_export_checklist_routes_and_middleware_are_registered()
    {
        $routes = collect(Route::getRoutes()->getRoutes());

        $expected = [
            'cns-export-checklist'                  => 'can:cns.export_checklist.view',
            'xml-cns-export-checklist'              => 'can:cns.export_checklist.view',
            'cns-export-checklist-detail'           => 'can:cns.export_checklist.view',
            'cns-export-checklist-detail-submit'    => 'can:cns.export_checklist.edit',
            'cns-export-checklist-approve-payment'  => 'can:cns.export_checklist.edit',
        ];

        foreach ($expected as $uri => $middleware) {
            $route = $routes->first(function ($r) use ($uri) {
                return $r->uri() === $uri;
            });
            $this->assertNotNull($route, "Route {$uri} not found");
            $this->assertContains($middleware, $route->gatherMiddleware());
        }

        $apiRoute = $routes->first(function ($r) {
            return $r->uri() === 'api/cns/export-payment-verified';
        });
        $this->assertNotNull($apiRoute, 'Route api/cns/export-payment-verified not found');
    }

    public function test_finance_payment_webhook_requires_configured_valid_token()
    {
        config(['services.finance.webhook_token' => '']);
        $this->postJson('/api/cns/export-payment-verified', ['Booking_No' => 'BC-001'])
            ->assertStatus(503);

        config(['services.finance.webhook_token' => 'secret-token']);
        $this->postJson('/api/cns/export-payment-verified', ['Booking_No' => 'BC-001'])
            ->assertStatus(401);
    }
}
