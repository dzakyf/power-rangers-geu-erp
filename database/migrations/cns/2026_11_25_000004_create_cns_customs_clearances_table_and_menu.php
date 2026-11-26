<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCnsCustomsClearancesTableAndMenu extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cns_customs_clearances')) {
            Schema::create('cns_customs_clearances', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('booking_id')->unique();
                $table->string('doc_3d_path', 500)->nullable();
                $table->string('doc_3d_name', 255)->nullable();
                $table->string('peb_path', 500)->nullable();
                $table->string('peb_name', 255)->nullable();
                $table->string('npe_path', 500)->nullable();
                $table->string('npe_name', 255)->nullable();
                $table->string('sphib_path', 500)->nullable();
                $table->string('sphib_name', 255)->nullable();
                $table->text('notes')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('menu_lists')) {
            $menuData = [
                'Name'        => 'Customs Clearance',
                'Path_Loc'    => 'cns-export-customs-clearance',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00082',
                'Urutan'      => 11,
            ];

            $exists = DB::table('menu_lists')->where('Menu_ID', 'SI00356')->exists();
            if (!$exists) {
                DB::table('menu_lists')->insert(array_merge(['Menu_ID' => 'SI00356'], $menuData));
            } else {
                DB::table('menu_lists')->where('Menu_ID', 'SI00356')->update($menuData);
            }

            if (Schema::hasTable('users')) {
                $users = DB::table('users')
                    ->where(function ($q) {
                        $q->where('Main_Menu', 'CNS')
                            ->orWhere('username', 'admin')
                            ->orWhere('Priv_ID', 'ADM')
                            ->orWhere('Sub_Menu', 'like', '%SI00350%');
                    })
                    ->get();

                foreach ($users as $user) {
                    $subMenus = array_filter(explode(',', (string) $user->Sub_Menu));
                    if (!in_array('SI00356', $subMenus, true)) {
                        $subMenus[] = 'SI00356';
                        DB::table('users')->where('id', $user->id)->update([
                            'Sub_Menu' => implode(',', $subMenus),
                        ]);
                    }
                }
            }
        }

        if (Schema::hasTable('permissions')) {
            $permConfigs = [
                'cns.customs_clearance.view' => [
                    'action' => 'view',
                    'source' => 'cns.export_checklist.view',
                ],
                'cns.customs_clearance.edit' => [
                    'action' => 'edit',
                    'source' => 'cns.export_checklist.edit',
                ],
            ];

            $adminRoles = Schema::hasTable('roles')
                ? Role::whereIn('name', ['ADM', 'super-admin'])->get()
                : collect();

            foreach ($permConfigs as $name => $cfg) {
                $permission = Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['menu_id' => 'SI00356', 'action' => $cfg['action']]
                );

                foreach ($adminRoles as $role) {
                    $role->givePermissionTo($permission);
                }

                if (Schema::hasTable('roles') && Schema::hasTable('role_has_permissions')) {
                    $sourcePermId = Permission::where('name', $cfg['source'])->value('id');
                    if ($sourcePermId) {
                        $roleIds = DB::table('role_has_permissions')
                            ->where('permission_id', $sourcePermId)
                            ->pluck('role_id')
                            ->unique();

                        foreach (Role::whereIn('id', $roleIds)->get() as $role) {
                            $role->givePermissionTo($permission);
                        }
                    }
                }
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('cns_customs_clearances');

        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->where('Menu_ID', 'SI00356')->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->where('Sub_Menu', 'like', '%SI00356%')
                ->get();

            foreach ($users as $user) {
                $subMenus = array_values(array_filter(explode(',', (string) $user->Sub_Menu), function ($id) {
                    return trim($id) !== 'SI00356';
                }));
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $subMenus),
                ]);
            }
        }

        if (Schema::hasTable('permissions')) {
            Permission::whereIn('name', [
                'cns.customs_clearance.view',
                'cns.customs_clearance.edit',
            ])->delete();
        }
    }
}
