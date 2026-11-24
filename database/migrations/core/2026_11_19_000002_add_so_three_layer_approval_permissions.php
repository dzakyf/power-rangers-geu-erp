<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddSoThreeLayerApprovalPermissions extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $perms = [
            [
                'name'       => 'cns.sales_order.approve_gudang',
                'guard_name' => 'web',
                'menu_id'    => 'SI00318',
                'action'     => 'approve_gudang',
            ],
            [
                'name'       => 'cns.sales_order.approve_buyer',
                'guard_name' => 'web',
                'menu_id'    => 'SI00318',
                'action'     => 'approve_buyer',
            ],
            [
                'name'       => 'cns.sales_order.approve_manager',
                'guard_name' => 'web',
                'menu_id'    => 'SI00318',
                'action'     => 'approve_manager',
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
            'cns.sales_order.approve_gudang',
            'cns.sales_order.approve_buyer',
            'cns.sales_order.approve_manager',
        ])->delete();
    }
}
