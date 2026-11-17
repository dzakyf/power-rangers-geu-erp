<?php

namespace Tests\Unit\Rbac;

use App\Role;
use App\Services\HCGA\RecruitmentOnboardingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecruitmentRoleResolutionTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function privilege_mapping_returns_the_selected_role_id()
    {
        $name = 'ONBOARD-' . strtoupper(substr(uniqid(), -6));
        $role = Role::create([
            'name' => $name,
            'guard_name' => 'web',
            'department' => 'FIN',
        ]);

        $target = 'Position-' . uniqid();

        DB::table('privilege_access_mappings')->insert([
            'mapping_type' => 'POSITION',
            'target_key' => $target,
            'priv_id' => $name,
            'default_role' => $name,
            'default_role_id' => $role->id,
            'status' => 'ACTIVE',
            'created_by' => 'TEST',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resolved = app(RecruitmentOnboardingService::class)
            ->resolvePrivilegeAndRole($target, null, 'FIN');

        $this->assertSame($role->id, (int) $resolved['default_role_id']);
        $this->assertSame($name, $resolved['default_role']);
    }
}
