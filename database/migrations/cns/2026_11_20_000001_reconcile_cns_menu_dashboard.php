<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Permission;
use App\Role;

class ReconcileCnsMenuDashboard extends Migration
{
    private const GOODS_RECEIPT_OLD = 'SI01055';
    private const GOODS_RECEIPT_NEW = 'SI01075';
    private const STUFFING_MONITORING = 'SI00306';

    private const DASHBOARD_MENUS = [
        'SM00080' => [
            'Name'        => 'Dashboard',
            'Path_Loc'    => '#',
            'Modules'     => 'CNS',
            'Position'    => 2,
            'Parent_Menu' => 'MS00011',
            'Urutan'      => 1,
        ],
        'SS00150' => [
            'Name'        => 'Dashboard',
            'Path_Loc'    => '#',
            'Modules'     => 'CNS',
            'Position'    => 3,
            'Parent_Menu' => 'SM00080',
            'Urutan'      => 1,
        ],
        'SI01150' => [
            'Name'        => 'Export Volume',
            'Path_Loc'    => 'cns-dashboard-export-volume',
            'Modules'     => 'CNS',
            'Position'    => 4,
            'Parent_Menu' => 'SS00150',
            'Urutan'      => 1,
        ],
    ];

    public function up()
    {
        if (!Schema::hasTable('menu_lists')) {
            return;
        }

        $this->moveGoodsReceipt();
        $this->removeStuffingMonitoring();
        $this->ensureDashboardMenus();
        $this->reorderLevelTwo();
        $this->syncUsers();
        $this->syncPermissions();

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    private function moveGoodsReceipt(): void
    {
        $old = DB::table('menu_lists')
            ->where('Menu_ID', self::GOODS_RECEIPT_OLD)
            ->where('Modules', 'CNS')
            ->first();

        $new = DB::table('menu_lists')->where('Menu_ID', self::GOODS_RECEIPT_NEW)->first();
        if ($new && $new->Modules !== 'CNS' && $new->Modules !== '') {
            throw new \RuntimeException(
                'Cannot move CNS Goods Receipt: ' . self::GOODS_RECEIPT_NEW . ' is already used by module ' . $new->Modules
            );
        }

        if ($old) {
            if ($new) {
                DB::table('menu_lists')->where('ID', $old->ID)->delete();
            } else {
                DB::table('menu_lists')->where('ID', $old->ID)->update([
                    'Menu_ID' => self::GOODS_RECEIPT_NEW,
                ]);
            }
        }

        DB::table('menu_lists')->updateOrInsert(
            ['Menu_ID' => self::GOODS_RECEIPT_NEW],
            [
                'Name'        => 'Goods Receipt',
                'Path_Loc'    => 'cns-procurement-goods-receipt',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00086',
                'Urutan'      => 4,
            ]
        );
    }

    private function removeStuffingMonitoring(): void
    {
        DB::table('menu_lists')
            ->where('Menu_ID', self::STUFFING_MONITORING)
            ->where('Modules', 'CNS')
            ->delete();
    }

    private function ensureDashboardMenus(): void
    {
        foreach (self::DASHBOARD_MENUS as $menuId => $data) {
            $existing = DB::table('menu_lists')->where('Menu_ID', $menuId)->first();
            if ($existing && $existing->Modules !== 'CNS' && $existing->Modules !== '') {
                throw new \RuntimeException(
                    'Cannot add CNS Dashboard: ' . $menuId . ' is already used by module ' . $existing->Modules
                );
            }

            DB::table('menu_lists')->updateOrInsert(['Menu_ID' => $menuId], $data);
        }
    }

    private function reorderLevelTwo(): void
    {
        $orders = [
            'SM00080' => 1,
            'SM00065' => 2,
            'SM00056' => 3,
            'SM00057' => 4,
            'SM00058' => 5,
            'SM00059' => 6,
            'SM00060' => 7,
            'SM00061' => 8,
            'SM00062' => 9,
            'SM00063' => 10,
            'SM00064' => 11,
            'SM00066' => 12,
        ];

        foreach ($orders as $menuId => $order) {
            DB::table('menu_lists')
                ->where('Menu_ID', $menuId)
                ->where('Modules', 'CNS')
                ->update(['Urutan' => $order]);
        }
    }

    private function syncUsers(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $add = array_merge(array_keys(self::DASHBOARD_MENUS), [self::GOODS_RECEIPT_NEW]);
        $remove = [self::STUFFING_MONITORING];

        $users = DB::table('users')
            ->where(function ($q) {
                $q->where('Main_Menu', 'CNS')
                    ->orWhere('username', 'admin')
                    ->orWhere('Priv_ID', 'ADM');
            })
            ->get();

        foreach ($users as $user) {
            $ids = array_values(array_filter(explode(',', (string) $user->Sub_Menu)));
            $ids = array_values(array_filter($ids, function ($id) use ($remove) {
                return !in_array(trim($id), $remove, true);
            }));

            $changed = count($ids) !== count(array_filter(explode(',', (string) $user->Sub_Menu)));
            foreach ($add as $id) {
                if (!in_array($id, $ids, true)) {
                    $ids[] = $id;
                    $changed = true;
                }
            }

            if ($changed) {
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $ids),
                ]);
            }
        }
    }

    private function syncPermissions(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        foreach (['view', 'create', 'edit', 'delete', 'approve'] as $action) {
            $name = 'cns.goods_receipt.' . $action;
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['menu_id' => self::GOODS_RECEIPT_NEW, 'action' => $action]
            );

            if ((string) $permission->menu_id !== self::GOODS_RECEIPT_NEW) {
                $permission->menu_id = self::GOODS_RECEIPT_NEW;
                $permission->action = $action;
                $permission->save();
            }
        }

        $dashboard = Permission::firstOrCreate(
            ['name' => 'cns.export_volume.view', 'guard_name' => 'web'],
            ['menu_id' => 'SI01150', 'action' => 'view']
        );

        if ((string) $dashboard->menu_id !== 'SI01150') {
            $dashboard->menu_id = 'SI01150';
            $dashboard->action = 'view';
            $dashboard->save();
        }

        if (Schema::hasTable('roles')) {
            $roles = Role::whereIn('name', ['ADM', 'super-admin'])->get();
            foreach ($roles as $role) {
                $role->givePermissionTo($dashboard);
            }
        }
    }

    public function down()
    {
        // Data repair only. The previous duplicate/colliding state is unsafe to restore.
    }
}
