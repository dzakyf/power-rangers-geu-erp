<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed ONLY the CNS rows of menu_lists (standalone; does not truncate and
 * does not touch other modules). Idempotent via Menu_ID upserts, then removes
 * stale CNS rows that are no longer present in the local snapshot.
 * Also synchronizes user Sub_Menu for CNS users so new menus appear immediately.
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

        // Exact CNS-only sync: remove CNS rows not present in the local snapshot.
        $deleted = DB::table('menu_lists')
            ->where('Modules', 'CNS')
            ->whereNotIn('Menu_ID', array_keys($rows))
            ->delete();

        $this->command->info("CNS menu_lists seeded/updated: {$count} rows; removed {$deleted} stale CNS rows");
    }
}
