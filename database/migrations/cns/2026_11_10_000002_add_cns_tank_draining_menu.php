<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCnsTankDrainingMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('menu_lists')) {
            return;
        }

        // Add Tank Draining menu (SI00340) under Inventory CNS (SS00087)
        $exists = DB::table('menu_lists')->where('Menu_ID', 'SI00340')->exists();
        if (!$exists) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SI00340',
                'Name'        => 'Tank Draining',
                'Path_Loc'    => 'cns-tank-draining',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00087',
                'Urutan'      => 6,
            ]);
        }

        // Grant permission to CNS users, admin, and ADM
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
                if (!in_array('SI00340', $subMenus)) {
                    $subMenus[] = 'SI00340';
                    DB::table('users')->where('id', $user->id)->update([
                        'Sub_Menu' => implode(',', $subMenus),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->where('Menu_ID', 'SI00340')->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->where('Sub_Menu', 'like', '%SI00340%')
                ->get();

            foreach ($users as $user) {
                $subMenus = array_filter(explode(',', (string) $user->Sub_Menu));
                $subMenus = array_diff($subMenus, ['SI00340']);
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $subMenus),
                ]);
            }
        }
    }
}
