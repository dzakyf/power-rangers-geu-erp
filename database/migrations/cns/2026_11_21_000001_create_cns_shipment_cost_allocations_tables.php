<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCnsShipmentCostAllocationsTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cns_shipment_cost_allocations')) {
            Schema::create('cns_shipment_cost_allocations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('allocation_no', 50)->unique();
                $table->unsignedInteger('si_id')->nullable()->index();
                $table->string('si_no', 150)->index();
                $table->string('final_si_no', 150)->nullable();
                $table->string('order_no', 150)->nullable();
                $table->decimal('default_exchange_rate', 15, 4)->default(16000);
                $table->decimal('total_freight_usd', 18, 4)->default(0);
                $table->decimal('total_trucking_usd', 18, 4)->default(0);
                $table->decimal('total_surveyor_usd', 18, 4)->default(0);
                $table->decimal('total_port_usd', 18, 4)->default(0);
                $table->decimal('total_cost_usd', 18, 4)->default(0);
                $table->decimal('total_cost_idr', 20, 2)->default(0);
                $table->decimal('volume_mt', 18, 4)->default(0);
                $table->integer('container_count')->default(0);
                $table->decimal('cost_per_mt_usd', 18, 4)->default(0);
                $table->decimal('cost_per_container_usd', 18, 4)->default(0);
                $table->string('status', 20)->default('DRAFT')->index();
                $table->text('notes')->nullable();
                $table->string('finalized_by', 100)->nullable();
                $table->dateTime('finalized_at')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cns_shipment_cost_allocation_items')) {
            Schema::create('cns_shipment_cost_allocation_items', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('allocation_id')->index();
                $table->string('category', 20)->index(); // freight, trucking, surveyor, port
                $table->string('description', 255);
                $table->string('vendor_name', 150)->nullable();
                $table->string('reference_no', 100)->nullable();
                $table->string('currency', 10)->default('USD'); // USD, IDR
                $table->decimal('amount', 18, 4)->default(0);
                $table->decimal('exchange_rate', 15, 4)->default(16000);
                $table->decimal('amount_usd', 18, 4)->default(0);
                $table->decimal('amount_idr', 20, 2)->default(0);
                $table->string('notes', 255)->nullable();
                $table->timestamps();

                $table->foreign('allocation_id')
                    ->references('id')
                    ->on('cns_shipment_cost_allocations')
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasTable('menu_lists')) {
            $menus = [
                'SS00097' => [
                    'Name'        => 'Cost Control',
                    'Path_Loc'    => '#',
                    'Modules'     => 'CNS',
                    'Position'    => 3,
                    'Parent_Menu' => 'SM00058',
                    'Urutan'      => 2,
                ],
                'SI00351' => [
                    'Name'        => 'Cost Allocation per Shipment',
                    'Path_Loc'    => 'cns-export-cost-allocation',
                    'Modules'     => 'CNS',
                    'Position'    => 4,
                    'Parent_Menu' => 'SS00097',
                    'Urutan'      => 1,
                ],
            ];

            foreach ($menus as $menuId => $menuData) {
                $exists = DB::table('menu_lists')->where('Menu_ID', $menuId)->exists();
                if (!$exists) {
                    DB::table('menu_lists')->insert(array_merge(['Menu_ID' => $menuId], $menuData));
                } else {
                    DB::table('menu_lists')->where('Menu_ID', $menuId)->update($menuData);
                }
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
                    $changed = false;
                    foreach (['SS00097', 'SI00351'] as $menuId) {
                        if (!in_array($menuId, $subMenus, true)) {
                            $subMenus[] = $menuId;
                            $changed = true;
                        }
                    }
                    if ($changed) {
                        DB::table('users')->where('id', $user->id)->update([
                            'Sub_Menu' => implode(',', $subMenus),
                        ]);
                    }
                }
            }
        }

        if (Schema::hasTable('permissions')) {
            $perms = [
                'cns.cost_allocation_shipment.view'   => 'view',
                'cns.cost_allocation_shipment.create' => 'create',
                'cns.cost_allocation_shipment.edit'   => 'edit',
                'cns.cost_allocation_shipment.delete' => 'delete',
            ];

            $roles = Schema::hasTable('roles')
                ? Role::whereIn('name', ['ADM', 'super-admin'])->get()
                : collect();

            foreach ($perms as $name => $action) {
                $permission = Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['menu_id' => 'SI00351', 'action' => $action]
                );

                foreach ($roles as $role) {
                    $role->givePermissionTo($permission);
                }
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('cns_shipment_cost_allocation_items');
        Schema::dropIfExists('cns_shipment_cost_allocations');

        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->whereIn('Menu_ID', ['SI00351', 'SS00097'])->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->where(function ($q) {
                    $q->where('Sub_Menu', 'like', '%SI00351%')
                        ->orWhere('Sub_Menu', 'like', '%SS00097%');
                })
                ->get();

            foreach ($users as $user) {
                $subMenus = array_values(array_filter(explode(',', (string) $user->Sub_Menu), function ($id) {
                    return !in_array(trim($id), ['SI00351', 'SS00097'], true);
                }));
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $subMenus),
                ]);
            }
        }

        if (Schema::hasTable('permissions')) {
            $permNames = [
                'cns.cost_allocation_shipment.view',
                'cns.cost_allocation_shipment.create',
                'cns.cost_allocation_shipment.edit',
                'cns.cost_allocation_shipment.delete',
            ];
            $perms = Permission::whereIn('name', $permNames)->get();
            foreach ($perms as $perm) {
                $perm->roles()->detach();
                $perm->delete();
            }
        }
    }
}
