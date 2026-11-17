<?php

namespace Tests\Feature\Credential;

use App\Role;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PrivilegeMappingRoleIdTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('username', 'admin')->firstOrFail();
    }

    /** @test */
    public function mapping_stores_the_selected_role_id()
    {
        $name = 'MAPROLE-' . strtoupper(substr(uniqid(), -6));
        $role = Role::create([
            'name' => $name,
            'guard_name' => 'web',
            'department' => 'FIN',
        ]);

        $target = 'Target-' . uniqid();

        $response = $this->actingAs($this->admin())->post('/credential-privilege-mapping-store', [
            'mapping_type' => 'POSITION',
            'target_key' => $target,
            'priv_id' => $name,
            'default_role_id' => $role->id,
            'description' => 'Test mapping role id',
            'status' => 'ACTIVE',
        ]);

        $response->assertOk()->assertJson(['status' => 'success']);

        $row = DB::table('privilege_access_mappings')->where('target_key', $target)->first();
        $this->assertNotNull($row);
        $this->assertSame($role->id, (int) $row->default_role_id);
        $this->assertSame($name, $row->default_role);
    }
}
