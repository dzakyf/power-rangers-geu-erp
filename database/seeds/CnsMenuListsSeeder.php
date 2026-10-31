<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed ONLY the CNS rows of menu_lists (standalone; does not truncate and
 * does not touch other modules). Idempotent via Menu_ID upserts.
 */
class CnsMenuListsSeeder extends Seeder
{
    public function run()
    {
        $rows = require __DIR__ . '/data/menu_lists_cns.php';
        $count = 0;

        foreach ($rows as $menuId => [$name, $path, $module, $position, $parent, $urutan]) {
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
            $count++;
        }

        // The unified procurement entry replaces the old separate Non-UCO menu.
        DB::table('menu_lists')->where('Menu_ID', 'SI00336')->delete();

        $this->command->info("CNS menu_lists seeded/updated: {$count} rows");
    }
}
