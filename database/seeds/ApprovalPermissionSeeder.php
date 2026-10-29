<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Permission;
use App\Role;

class ApprovalPermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'approval.claim.release',
            'approval.self-approval.grant',
            'approval.policy.manage',
            'approval.task.act',
            'approval.task.assign',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        $adminRoles = Role::whereRaw('LOWER(name) LIKE ?', ['%admin%'])
            ->orWhereRaw('UPPER(name) = ?', ['ADM'])
            ->get();

        foreach ($adminRoles as $role) {
            $role->givePermissionTo($permissions);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Approval permissions seeded for ' . $adminRoles->count() . ' admin role(s).');
    }
}