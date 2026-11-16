<?php

namespace Tests\Feature\BusinessPartner;

use App\Permission;
use App\Role;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BdpRbacTest extends TestCase
{
    use DatabaseTransactions;

    private function userWithoutAdm(): User
    {
        $user = User::query()->where('username', '!=', 'admin')->orderBy('id')->first();
        if (!$user) {
            $this->markTestSkipped('No non-admin user exists in the test database.');
        }

        return $user;
    }

    private function roleWith(array $permissionNames): Role
    {
        $role = Role::create([
            'name' => 'RBAC-BDP-' . strtoupper(substr(uniqid(), -8)),
            'guard_name' => 'web',
            'department' => null,
        ]);

        $permissions = Permission::whereIn('name', $permissionNames)->get();
        $role->givePermissionTo($permissions);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }

    /** @test */
    public function view_only_role_can_open_the_index_but_cannot_mutate_or_see_action_buttons(): void
    {
        $user = $this->userWithoutAdm();
        $role = $this->roleWith(['menu.SI00258.view']);
        $user->syncRoles([$role]);

        $this->actingAs($user)->get('/master-container')
            ->assertOk()
            ->assertSee('Master Container')
            ->assertDontSee('Buat Data')
            ->assertDontSee('Hapus');

        $this->actingAs($user)->post('/master-container-submit', [])->assertForbidden();
        $this->actingAs($user)->post('/master-container-edit', [])->assertForbidden();
        $this->actingAs($user)->delete('/master-container-delete', ['id' => 1])->assertForbidden();
    }

    /** @test */
    public function role_with_create_and_delete_permissions_can_reach_those_endpoints(): void
    {
        $user = $this->userWithoutAdm();
        $role = $this->roleWith([
            'menu.SI00258.view',
            'menu.SI00258.create',
            'menu.SI00258.delete',
        ]);
        $user->syncRoles([$role]);

        $this->actingAs($user)->get('/master-container')
            ->assertOk()
            ->assertSee('Buat Data')
            ->assertSee('Hapus');

        // The middleware allows the request through; validation/controller
        // behavior may still return a non-403 response.
        $this->assertNotSame(403, $this->actingAs($user)->post('/master-container-submit', [])->status());
        $this->assertNotSame(403, $this->actingAs($user)->delete('/master-container-delete', ['id' => 999999])->status());
    }

    /** @test */
    public function approve_endpoints_require_the_approve_permission(): void
    {
        $user = $this->userWithoutAdm();
        $role = $this->roleWith(['menu.SI00276.view']);
        $user->syncRoles([$role]);

        $this->actingAs($user)->post('/partner-warehouse-stock-opname-finalize', ['id' => 1])
            ->assertForbidden();
    }
}
