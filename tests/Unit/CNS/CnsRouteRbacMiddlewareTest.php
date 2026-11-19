<?php

namespace Tests\Unit\CNS;

use Tests\TestCase;
use App\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;

class CnsRouteRbacMiddlewareTest extends TestCase
{
    /** @test */
    public function all_cns_module_routes_have_authorization_middleware(): void
    {
        $cnsRoutes = collect(Route::getRoutes()->getRoutes())->filter(function ($route) {
            $uri = $route->uri();
            return strpos($uri, 'cns-') === 0 && $uri !== 'cns-master-warehouses';
        });

        $this->assertNotEmpty($cnsRoutes, 'CNS routes should be registered.');

        foreach ($cnsRoutes as $route) {
            $uri = $route->uri();
            $action = $route->getAction();
            $middleware = $action['middleware'] ?? [];

            $hasCanMiddleware = collect($middleware)->contains(function ($m) {
                return strpos($m, 'can:') === 0;
            });

            $this->assertTrue(
                $hasCanMiddleware,
                "Route '{$uri}' must have 'can:...' authorization middleware attached. Found middleware: " . json_encode($middleware)
            );
        }
    }

    /** @test */
    public function gate_before_authorizes_admin_and_adm_role(): void
    {
        $adminUser = new User();
        $adminUser->username = 'admin';

        $this->assertTrue(Gate::forUser($adminUser)->check('cns.shipping_instruction.view'));
        $this->assertTrue(Gate::forUser($adminUser)->check('cns.shipping_instruction.delete'));
        $this->assertTrue(Gate::forUser($adminUser)->check('cns.shipping_instruction.approve'));

        $admRoleUser = new User();
        $admRoleUser->username = 'johndoe';
        $admRoleUser->Priv_ID = 'ADM';

        $this->assertTrue(Gate::forUser($admRoleUser)->check('cns.shipping_instruction.view'));
        $this->assertTrue(Gate::forUser($admRoleUser)->check('cns.buyer_contract.create'));
    }

    /** @test */
    public function offering_letter_approve_has_correct_cns_permission_middleware(): void
    {
        $olApproveRoute = Route::getRoutes()->getByName('cns-offering-letter.approve');
        $this->assertNotNull($olApproveRoute);
        $this->assertContains('can:cns.offering_letter.approve', $olApproveRoute->gatherMiddleware());
    }
}
