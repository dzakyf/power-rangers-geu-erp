<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Permission;
use App\Role;

class RenameShipmentToShippingInstructions extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipment_instructions') && !Schema::hasTable('shipping_instructions')) {
            Schema::rename('shipment_instructions', 'shipping_instructions');
        }

        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->where('Menu_ID', 'SI00307')->update([
                'Name' => 'Shipping Instructions',
                'Path_Loc' => 'cns-export-shipping-instruction',
            ]);
            DB::table('menu_lists')->where('Menu_ID', 'SI00308')->update([
                'Name' => 'Final Shipping Instructions',
                'Path_Loc' => 'cns-export-final-shipping-instruction',
            ]);
        }

        if (Schema::hasTable('permissions')) {
            $perms = [
                [
                    'name' => 'cns.shipping_instruction.approve',
                    'guard_name' => 'web',
                    'menu_id' => 'SI00307',
                    'action' => 'approve',
                ],
                [
                    'name' => 'cns.final_shipping_instruction.approve',
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
                $roles = Role::whereIn('name', ['ADM', 'super-admin'])->get();
                foreach ($roles as $role) {
                    $role->givePermissionTo($created);
                }

                if (Schema::hasTable('role_has_permissions')) {
                    $legacyPermIds = Permission::whereIn('name', [
                        'cns.shipment_instruction.approve',
                        'cns.final_shipment_instruction.approve',
                    ])->pluck('id');

                    $roleIds = DB::table('role_has_permissions')
                        ->whereIn('permission_id', $legacyPermIds)
                        ->pluck('role_id')
                        ->unique();

                    $rolesWithLegacy = Role::whereIn('id', $roleIds)->get();
                    foreach ($rolesWithLegacy as $role) {
                        $role->givePermissionTo($created);
                    }
                }
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('shipping_instructions') && !Schema::hasTable('shipment_instructions')) {
            Schema::rename('shipping_instructions', 'shipment_instructions');
        }

        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->where('Menu_ID', 'SI00307')->update([
                'Name' => 'Shipping Instructions',
                'Path_Loc' => 'cns-export-shipping-instruction',
            ]);
            DB::table('menu_lists')->where('Menu_ID', 'SI00308')->update([
                'Name' => 'Final Shipping Instructions',
                'Path_Loc' => 'cns-export-final-shipping-instruction',
            ]);
        }

        if (Schema::hasTable('permissions')) {
            Permission::whereIn('name', [
                'cns.shipping_instruction.approve',
                'cns.final_shipping_instruction.approve',
            ])->delete();
        }
    }
}
