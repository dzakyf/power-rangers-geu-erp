<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Permission;
use App\Role;

class CreateCnsExportChecklistMenuAndColumns extends Migration
{
    public function up()
    {
        if (Schema::hasTable('menu_lists')) {
            $menuData = [
                'Name'        => 'Export Checklist',
                'Path_Loc'    => 'cns-export-checklist',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00082',
                'Urutan'      => 10,
            ];

            $exists = DB::table('menu_lists')->where('Menu_ID', 'SI00350')->exists();
            if (!$exists) {
                DB::table('menu_lists')->insert(array_merge(['Menu_ID' => 'SI00350'], $menuData));
            } else {
                DB::table('menu_lists')->where('Menu_ID', 'SI00350')->update($menuData);
            }

            if (Schema::hasTable('users')) {
                $users = DB::table('users')
                    ->where(function ($q) {
                        $q->where('Main_Menu', 'CNS')
                            ->orWhere('username', 'admin')
                            ->orWhere('Priv_ID', 'ADM');
                    })
                    ->get();

                foreach ($users as $user) {
                    $subMenus = array_filter(explode(',', (string) $user->Sub_Menu));
                    if (!in_array('SI00350', $subMenus, true)) {
                        $subMenus[] = 'SI00350';
                        DB::table('users')->where('id', $user->id)->update([
                            'Sub_Menu' => implode(',', $subMenus),
                        ]);
                    }
                }
            }
        }

        if (Schema::hasTable('permissions')) {
            $perms = [
                'cns.export_checklist.view' => 'view',
                'cns.export_checklist.edit' => 'edit',
            ];

            $roles = Schema::hasTable('roles')
                ? Role::whereIn('name', ['ADM', 'super-admin'])->get()
                : collect();

            foreach ($perms as $name => $action) {
                $permission = Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['menu_id' => 'SI00350', 'action' => $action]
                );

                foreach ($roles as $role) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        if (Schema::hasTable('cns_buyer_contract_bookings')) {
            Schema::table('cns_buyer_contract_bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('cns_buyer_contract_bookings', 'Checklist_Verified')) {
                    $table->boolean('Checklist_Verified')->default(false);
                }
                if (!Schema::hasColumn('cns_buyer_contract_bookings', 'Checklist_Verified_By')) {
                    $table->string('Checklist_Verified_By', 100)->nullable();
                }
                if (!Schema::hasColumn('cns_buyer_contract_bookings', 'Checklist_Verified_At')) {
                    $table->dateTime('Checklist_Verified_At')->nullable();
                }
                if (!Schema::hasColumn('cns_buyer_contract_bookings', 'Checklist_Notes')) {
                    $table->text('Checklist_Notes')->nullable();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('cns_buyer_contract_bookings')) {
            Schema::table('cns_buyer_contract_bookings', function (Blueprint $table) {
                $cols = array_filter([
                    'Checklist_Verified',
                    'Checklist_Verified_By',
                    'Checklist_Verified_At',
                    'Checklist_Notes',
                ], function ($col) {
                    return Schema::hasColumn('cns_buyer_contract_bookings', $col);
                });

                if (!empty($cols)) {
                    $table->dropColumn(array_values($cols));
                }
            });
        }

        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->where('Menu_ID', 'SI00350')->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->where('Sub_Menu', 'like', '%SI00350%')
                ->get();

            foreach ($users as $user) {
                $subMenus = array_values(array_filter(explode(',', (string) $user->Sub_Menu), function ($id) {
                    return trim($id) !== 'SI00350';
                }));
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $subMenus),
                ]);
            }
        }

        if (Schema::hasTable('permissions')) {
            Permission::whereIn('name', [
                'cns.export_checklist.view',
                'cns.export_checklist.edit',
            ])->delete();
        }
    }
}
