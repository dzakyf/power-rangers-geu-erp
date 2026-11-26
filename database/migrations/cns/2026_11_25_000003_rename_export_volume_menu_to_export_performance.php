<?php

use App\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameExportVolumeMenuToExportPerformance extends Migration
{
    /**
     * Rename menu SI01150 from Export Volume to Export Performance and remove leftover SI00355 if present.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')
                ->where('Menu_ID', 'SI01150')
                ->update(['Name' => 'Export Performance']);

            DB::table('menu_lists')
                ->where('Menu_ID', 'SI00355')
                ->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->where('Sub_Menu', 'like', '%SI00355%')
                ->get();

            foreach ($users as $user) {
                $subMenus = array_values(array_filter(explode(',', (string) $user->Sub_Menu), function ($id) {
                    return trim($id) !== 'SI00355';
                }));
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $subMenus),
                ]);
            }
        }

        if (Schema::hasTable('permissions')) {
            $perm = Permission::where('name', 'cns.export_performance.view')->first();
            if ($perm) {
                $perm->roles()->detach();
                $perm->delete();
            }
        }
    }

    /**
     * Revert menu SI01150 back to Export Volume.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')
                ->where('Menu_ID', 'SI01150')
                ->update(['Name' => 'Export Volume']);
        }
    }
}
