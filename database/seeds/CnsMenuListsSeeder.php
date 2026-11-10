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

        // Ensure user admin and CNS users have all CNS menus in Sub_Menu
        if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
            $cnsMenuIds = DB::table('menu_lists')
                ->where('Modules', 'CNS')
                ->orderBy('Position')
                ->orderBy('Urutan')
                ->pluck('Menu_ID')
                ->toArray();

            $users = DB::table('users')
                ->where(function ($q) {
                    $q->where('Main_Menu', 'CNS')
                        ->orWhere('username', 'admin')
                        ->orWhere('Priv_ID', 'ADM');
                })
                ->get();

            foreach ($users as $u) {
                $existing = array_filter(explode(',', (string) $u->Sub_Menu));
                $merged = array_unique(array_merge($existing, $cnsMenuIds));
                DB::table('users')->where('id', $u->id)->update([
                    'Sub_Menu' => implode(',', $merged),
                ]);
            }
        }

        $this->command->info("CNS menu_lists seeded/updated: {$count} rows; removed {$deleted} stale CNS rows");
    }
}
