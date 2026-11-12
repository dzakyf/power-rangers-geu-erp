<?php

namespace Tests\Feature\BusinessPartner;

use App\Role;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BpSurveyMasterAccessTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function head_manager_and_sales_roles_are_granted_master_survey_access()
    {
        $this->seed(\BpSurveyMasterAccessSeeder::class);

        foreach (['HEAD', 'MANAGER', 'SALES'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            $this->assertNotNull($role, "Role {$roleName} not found");
            $this->assertTrue(
                $role->hasPermissionTo('menu.SI00227.view'),
                "{$roleName} should have menu.SI00227.view"
            );
        }
    }

    /** @test */
    public function affected_users_sub_menu_includes_master_survey()
    {
        $this->seed(\BpSurveyMasterAccessSeeder::class);

        $user = User::where('username', 'manager1')->first();
        if (!$user) {
            $this->markTestSkipped('manager1 user missing');
        }

        $this->assertStringContainsString('SI00227', (string) $user->fresh()->Sub_Menu);
    }

    /** @test */
    public function bp_pages_use_bootstrap_5_dropdown_toggle()
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        foreach (['partner-outstanding-leads', 'partner-approval-index', 'partner-survey-approval-index'] as $path) {
            $response = $this->actingAs($admin)->get('/' . $path);
            $response->assertOk();
            $response->assertSee('data-bs-toggle="dropdown"', false);
        }
    }
}
