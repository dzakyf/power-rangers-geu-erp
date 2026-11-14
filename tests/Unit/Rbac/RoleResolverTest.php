<?php

namespace Tests\Unit\Rbac;

use App\Role;
use App\Services\Rbac\RoleResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RoleResolverTest extends TestCase
{
    use DatabaseTransactions;

    private function roleName(): string
    {
        return 'RESTEST-' . strtoupper(substr(uniqid(), -6));
    }

    private function role(string $name, ?string $department = null): Role
    {
        return Role::create([
            'name' => $name,
            'guard_name' => 'web',
            'department' => $department,
        ]);
    }

    /** @test */
    public function it_prefers_the_role_in_the_users_department()
    {
        $name = $this->roleName();
        $fin = $this->role($name, 'FIN');
        $this->role($name, null);

        $resolved = app(RoleResolver::class)->resolve($name, 'FIN');

        $this->assertNotNull($resolved);
        $this->assertSame($fin->id, $resolved->id);
    }

    /** @test */
    public function it_falls_back_to_the_global_role_when_department_role_is_missing()
    {
        $name = $this->roleName();
        $global = $this->role($name, null);
        $this->role($name, 'FIN');

        $resolved = app(RoleResolver::class)->resolve($name, 'HCGA');

        $this->assertNotNull($resolved);
        $this->assertSame($global->id, $resolved->id);
        $this->assertNull($resolved->department);
    }

    /** @test */
    public function it_returns_null_when_no_role_matches()
    {
        $this->assertNull(app(RoleResolver::class)->resolve('UNKNOWN-' . uniqid(), 'FIN'));
    }

    /** @test */
    public function it_resolves_by_id_and_formats_a_label()
    {
        $name = $this->roleName();
        $role = $this->role($name, 'FIN');
        $resolver = app(RoleResolver::class);

        $globalName = $this->roleName();
        $global = $this->role($globalName, null);

        $this->assertSame($role->id, $resolver->resolveById($role->id)->id);
        $this->assertSame($name . ' - FIN', $resolver->label($role));
        $this->assertSame($globalName, $resolver->label($global));
    }
}
