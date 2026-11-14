<?php

namespace Tests\Feature\Credential;

use App\Role;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RoleDepartmentTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('username', 'admin')->firstOrFail();
    }

    private function uniqueName(string $prefix): string
    {
        return $prefix . '-' . strtoupper(substr(uniqid(), -6));
    }

    /** @test */
    public function role_can_be_created_with_department()
    {
        $name = $this->uniqueName('MANAGER');

        $response = $this->actingAs($this->admin())->post('/master-credential-role-create', [
            'name' => $name,
            'department' => 'FIN',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('roles', [
            'name' => $name,
            'department' => 'FIN',
            'guard_name' => 'web',
        ]);
    }

    /** @test */
    public function same_role_name_is_allowed_in_different_departments()
    {
        $name = $this->uniqueName('MANAGER');

        $this->actingAs($this->admin())->post('/master-credential-role-create', [
            'name' => $name,
            'department' => 'FIN',
        ])->assertOk()->assertJson(['success' => true]);

        $this->actingAs($this->admin())->post('/master-credential-role-create', [
            'name' => $name,
            'department' => 'HCGA',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertSame(2, Role::where('name', $name)->count());
    }

    /** @test */
    public function duplicate_role_name_in_same_department_is_rejected()
    {
        $name = $this->uniqueName('MANAGER');

        $this->actingAs($this->admin())->post('/master-credential-role-create', [
            'name' => $name,
            'department' => 'FIN',
        ])->assertOk()->assertJson(['success' => true]);

        $response = $this->actingAs($this->admin())->post('/master-credential-role-create', [
            'name' => $name,
            'department' => 'FIN',
        ]);

        $response->assertOk()->assertJson(['success' => false]);
        $this->assertSame(1, Role::where('name', $name)->where('department', 'FIN')->count());
    }

    /** @test */
    public function duplicate_global_role_is_rejected()
    {
        $name = $this->uniqueName('GLOBAL');

        $this->actingAs($this->admin())->post('/master-credential-role-create', [
            'name' => $name,
        ])->assertOk()->assertJson(['success' => true]);

        $response = $this->actingAs($this->admin())->post('/master-credential-role-create', [
            'name' => $name,
        ]);

        $response->assertOk()->assertJson(['success' => false]);
        $this->assertSame(1, Role::where('name', $name)->whereNull('department')->count());
    }

    /** @test */
    public function role_list_returns_department()
    {
        $name = $this->uniqueName('LIST');
        Role::create(['name' => $name, 'guard_name' => 'web', 'department' => 'CNS']);

        $response = $this->actingAs($this->admin())->get('/master-credential-role-list?' . http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 100,
            'search' => ['value' => $name],
        ]));

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('name', $name);
        $this->assertNotNull($row);
        $this->assertSame('CNS', $row['department']);
    }

    /** @test */
    public function credential_role_related_pages_render_with_role_models()
    {
        $admin = $this->actingAs($this->admin());

        $admin->get('/credential-role')->assertOk();
        $admin->get('/credential-user-management')->assertOk();
        $admin->get('/credential-privilege-mapping')->assertOk();
    }

    /** @test */
    public function role_page_has_department_field_and_permissions_button()
    {
        $response = $this->actingAs($this->admin())->get('/credential-role');

        $response->assertOk();
        $response->assertSee('id="roleDepartment"', false);
        $response->assertSee('id="permissionsBtn"', false);
        $response->assertSee('Department', false);

        $view = file_get_contents(resource_path('views/credential/role.blade.php'));
        $this->assertStringNotContainsString("$('#permission-table').empty()", $view);
        $this->assertStringContainsString('permDt.ajax.reload()', $view);
    }
}
