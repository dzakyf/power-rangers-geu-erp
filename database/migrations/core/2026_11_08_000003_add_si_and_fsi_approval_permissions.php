<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use App\Permission;
use App\Role;

class AddSiAndFsiApprovalPermissions extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $perms = [
            [
                'name' => 'menu.SI00307.approve',
                'guard_name' => 'web',
                'menu_id' => 'SI00307',
                'action' => 'approve',
            ],
            [
                'name' => 'menu.SI00308.approve',
                'guard_name' => 'web',
                'menu_id' => 'SI00308',
                'action' => 'approve',
            ],
            [
                'name' => 'cns.shipment_instruction.approve',
                'guard_name' => 'web',
                'menu_id' => 'SI00307',
                'action' => 'approve',
            ],
            [
                'name' => 'cns.final_shipment_instruction.approve',
                'guard_name' => 'web',
                'menu_id' => 'SI00308',
                'action' => 'approve',
            ],
        ];

        $created = collect();
        foreach ($perms as $p) {
            $permission = Permission::firstOrCreate(
                ['name' => $p['name'], 'guard_name' => $p['guard_name']],
                ['menu_id' => $p['menu_id'], 'action' => $p['action']]
            );
            $created->push($permission);
        }

        if (Schema::hasTable('roles')) {
            $admRoles = Role::whereIn('name', ['ADM', 'super-admin'])->get();
            foreach ($admRoles as $role) {
                $role->givePermissionTo($created);
            }
        }
    }

    public function down()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        Permission::whereIn('name', [
            'menu.SI00307.approve',
            'menu.SI00308.approve',
            'cns.shipment_instruction.approve',
            'cns.final_shipment_instruction.approve',
        ])->delete();
    }
}
