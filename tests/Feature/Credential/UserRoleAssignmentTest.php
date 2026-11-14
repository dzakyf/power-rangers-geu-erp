<?php

namespace Tests\Feature\Credential;

use App\Role;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserRoleAssignmentTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('username', 'admin')->firstOrFail();
    }

    private function unique(string $prefix): string
    {
        return $prefix . '-' . strtoupper(substr(uniqid(), -6));
    }

    private function role(string $name, ?string $department): Role
    {
        return Role::create([
            'name' => $name,
            'guard_name' => 'web',
            'department' => $department,
        ]);
    }

    /** @test */
    public function creating_a_user_assigns_role_by_id_and_syncs_department()
    {
        $roleName = $this->unique('MANAGER');
        $role = $this->role($roleName, 'FIN');
        $email = strtolower($this->unique('user')) . '@example.test';

        $response = $this->actingAs($this->admin())->post('/credential-create-user-submit', [
            'email' => $email,
            'Name' => 'Department Role Test',
            'password' => '12345678',
            'Location' => 'GEU-HO',
            'Position' => 'IGNORED',
            'role_id' => $role->id,
        ]);

        $response->assertOk();
        $this->assertSame('success', $response->getContent());

        $user = User::where('email', $email)->firstOrFail();
        $this->assertSame($roleName, $user->Priv_ID);
        $this->assertSame('FIN', $user->Department);
        $this->assertTrue(
            DB::table('model_has_roles')->where('role_id', $role->id)->where('model_id', $user->id)->exists()
        );
    }

    /** @test */
    public function updating_a_user_assigns_primary_and_additional_roles_by_id()
    {
        $primaryName = $this->unique('MANAGER');
        $additionalName = $this->unique('STAFF');
        $primary = $this->role($primaryName, 'FIN');
        $additional = $this->role($additionalName, 'FIN');

        $userId = DB::table('users')->insertGetId([
            'username' => $this->unique('user'),
            'Name' => 'Role Update Test',
            'email' => strtolower($this->unique('mail')) . '@example.test',
            'password' => bcrypt('12345678'),
            'Location' => 'GEU-HO',
            'Status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->admin())->post('/master-credential-user-update', [
            'user_id' => $userId,
            'name' => 'Role Update Test',
            'email' => 'role-update-' . uniqid() . '@example.test',
            'location' => 'GEU-HO',
            'status' => 'ACTIVE',
            'primary_role_id' => $primary->id,
            'additional_role_ids' => [$additional->id],
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $user = User::findOrFail($userId);
        $this->assertSame($primaryName, $user->Priv_ID);
        $this->assertSame('FIN', $user->Department);

        $roleIds = DB::table('model_has_roles')
            ->where('model_id', $userId)
            ->where('model_type', User::class)
            ->pluck('role_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertContains($primary->id, $roleIds);
        $this->assertContains($additional->id, $roleIds);
    }
}
