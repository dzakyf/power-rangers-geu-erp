<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCnsProductAllocationsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cns_product_allocations')) {
            Schema::create('cns_product_allocations', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('contract_id')->index();
                $table->string('product_name', 100);
                $table->string('period_month', 7)->index(); // YYYY-MM
                $table->decimal('planned_mt', 15, 3)->default(0);
                $table->integer('planned_containers')->default(0);
                $table->string('notes', 255)->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('menu_lists')) {
            $menuData = [
                'Name'        => 'Product Allocation & Volume Planning',
                'Path_Loc'    => 'cns-product-allocation',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00084',
                'Urutan'      => 6,
            ];

            $exists = DB::table('menu_lists')->where('Menu_ID', 'SI00352')->exists();
            if (!$exists) {
                DB::table('menu_lists')->insert(array_merge(['Menu_ID' => 'SI00352'], $menuData));
            } else {
                DB::table('menu_lists')->where('Menu_ID', 'SI00352')->update($menuData);
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
                    if (!in_array('SI00352', $subMenus, true)) {
                        $subMenus[] = 'SI00352';
                        DB::table('users')->where('id', $user->id)->update([
                            'Sub_Menu' => implode(',', $subMenus),
                        ]);
                    }
                }
            }
        }

        if (Schema::hasTable('permissions')) {
            $perms = [
                'cns.product_allocation.view'   => 'view',
                'cns.product_allocation.create' => 'create',
                'cns.product_allocation.edit'   => 'edit',
                'cns.product_allocation.delete' => 'delete',
            ];

            $roles = Schema::hasTable('roles')
                ? Role::whereIn('name', ['ADM', 'super-admin'])->get()
                : collect();

            foreach ($perms as $name => $action) {
                $permission = Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['menu_id' => 'SI00352', 'action' => $action]
                );

                foreach ($roles as $role) {
                    $role->givePermissionTo($permission);
                }
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('cns_product_allocations');

        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->where('Menu_ID', 'SI00352')->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->where('Sub_Menu', 'like', '%SI00352%')
                ->get();

            foreach ($users as $user) {
                $subMenus = array_values(array_filter(explode(',', (string) $user->Sub_Menu), function ($id) {
                    return trim($id) !== 'SI00352';
                }));
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $subMenus),
                ]);
            }
        }

        if (Schema::hasTable('permissions')) {
            $permNames = [
                'cns.product_allocation.view',
                'cns.product_allocation.create',
                'cns.product_allocation.edit',
                'cns.product_allocation.delete',
            ];
            $perms = Permission::whereIn('name', $permNames)->get();
            foreach ($perms as $perm) {
                $perm->roles()->detach();
                $perm->delete();
            }
        }
    }
}
