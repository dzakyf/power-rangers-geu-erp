<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repair CNS menu rows that drifted from the intended hierarchy:
 *  - Tank Draining (SI00340) overwritten by another module
 *  - Inventory Non-UCO (SI00332) attached to the wrong subheader
 *  - Inventory Kemasan (SI00341) / Inbound Movement (SI00317) hierarchy
 *
 * Uses the existing CNS ids; it intentionally does NOT touch SI00338
 * (Credential "Role & Permissions") or SI00336 (Candidate menu).
 * The legacy Master User (SI00334) was replaced by the CNS access-control
 * menus (SI00347/SI00348) in 2026_11_14_000003.
 */
class RepairCnsMenuStructure extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('menu_lists')) {
            return;
        }

        $rows = [
            'SS00087' => ['Inventory UCO', '#', 'CNS', 3, 'SM00063', 1],
            'SS00091' => ['Inventory Non UCO', '#', 'CNS', 3, 'SM00063', 2],
            'SI00317' => ['Inbound Movement', 'cns-inventory-tracking-index', 'CNS', 4, 'SS00087', 1],
            'SI00340' => ['Tank Draining', 'cns-tank-draining', 'CNS', 4, 'SS00087', 2],
            'SI00341' => ['Inventory Kemasan', 'cns-inventory-kemasan', 'CNS', 4, 'SS00091', 1],
            'SI00332' => ['Inventory Non-UCO', 'cns-inventory-non-uco', 'CNS', 4, 'SS00091', 2],
        ];

        foreach ($rows as $menuId => $data) {
            [$name, $path, $module, $position, $parent, $urutan] = $data;

            DB::table('menu_lists')->updateOrInsert(
                ['Menu_ID' => $menuId],
                [
                    'Name'        => $name,
                    'Path_Loc'    => $path,
                    'Modules'     => $module,
                    'Position'    => $position,
                    'Parent_Menu' => $parent,
                    'Urutan'      => $urutan,
                ]
            );
        }

        if (!Schema::hasTable('users')) {
            return;
        }

        $menuIds = array_keys($rows);
        $users = DB::table('users')
            ->where(function ($q) {
                $q->where('Main_Menu', 'CNS')
                    ->orWhere('username', 'admin')
                    ->orWhere('Priv_ID', 'ADM');
            })
            ->get();

        foreach ($users as $user) {
            $subMenus = array_filter(explode(',', (string) $user->Sub_Menu));
            $modified = false;

            foreach ($menuIds as $menuId) {
                if (!in_array($menuId, $subMenus, true)) {
                    $subMenus[] = $menuId;
                    $modified = true;
                }
            }

            if ($modified) {
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $subMenus),
                ]);
            }
        }
    }

    public function down()
    {
        // The previous state was inconsistent data drift; nothing safe to restore.
    }
}
