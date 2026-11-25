<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCnsSalesForecastsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cns_sales_forecasts')) {
            Schema::create('cns_sales_forecasts', function (Blueprint $table) {
                $table->increments('id');
                $table->string('period_month', 7)->unique(); // YYYY-MM
                $table->decimal('planned_mt', 15, 3)->default(0);
                $table->string('notes', 255)->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('menu_lists')) {
            $menuData = [
                'Name'        => 'Sales Forecast & Pipeline Tracking',
                'Path_Loc'    => 'cns-sales-forecast',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00084',
                'Urutan'      => 7,
            ];

            $exists = DB::table('menu_lists')->where('Menu_ID', 'SI00354')->exists();
            if (!$exists) {
                DB::table('menu_lists')->insert(array_merge(['Menu_ID' => 'SI00354'], $menuData));
            } else {
                DB::table('menu_lists')->where('Menu_ID', 'SI00354')->update($menuData);
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
                    if (!in_array('SI00354', $subMenus, true)) {
                        $subMenus[] = 'SI00354';
                        DB::table('users')->where('id', $user->id)->update([
                            'Sub_Menu' => implode(',', $subMenus),
                        ]);
                    }
                }
            }
        }

        if (Schema::hasTable('permissions')) {
            $perms = [
                'cns.sales_forecast.view'   => 'view',
                'cns.sales_forecast.create' => 'create',
                'cns.sales_forecast.edit'   => 'edit',
                'cns.sales_forecast.delete' => 'delete',
            ];

            $roles = Schema::hasTable('roles')
                ? Role::whereIn('name', ['ADM', 'super-admin'])->get()
                : collect();

            foreach ($perms as $name => $action) {
                $permission = Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['menu_id' => 'SI00354', 'action' => $action]
                );

                foreach ($roles as $role) {
                    $role->givePermissionTo($permission);
                }
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('cns_sales_forecasts');

        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->where('Menu_ID', 'SI00354')->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->where('Sub_Menu', 'like', '%SI00354%')
                ->get();

            foreach ($users as $user) {
                $subMenus = array_values(array_filter(explode(',', (string) $user->Sub_Menu), function ($id) {
                    return trim($id) !== 'SI00354';
                }));
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $subMenus),
                ]);
            }
        }

        if (Schema::hasTable('permissions')) {
            $permNames = [
                'cns.sales_forecast.view',
                'cns.sales_forecast.create',
                'cns.sales_forecast.edit',
                'cns.sales_forecast.delete',
            ];
            Permission::whereIn('name', $permNames)->delete();
        }
    }
}
