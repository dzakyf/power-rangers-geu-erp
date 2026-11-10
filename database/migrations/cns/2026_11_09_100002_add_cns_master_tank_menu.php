<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCnsMasterTankMenu extends Migration
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

        // Add Master Tangki menu (SI00339) under Master Data CNS (SS00088)
        $exists = DB::table('menu_lists')->where('Menu_ID', 'SI00339')->exists();
        if (!$exists) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SI00339',
                'Name'        => 'Master Tangki',
                'Path_Loc'    => 'cns-master-tank',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00088',
                'Urutan'      => 5,
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
                if (!in_array('SI00339', $subMenus)) {
                    $subMenus[] = 'SI00339';
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
            DB::table('menu_lists')->where('Menu_ID', 'SI00339')->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->where('Sub_Menu', 'like', '%SI00339%')
                ->get();

            foreach ($users as $user) {
                $subMenus = array_filter(explode(',', (string) $user->Sub_Menu));
                $newSubMenus = array_filter($subMenus, function ($m) {
                    return $m !== 'SI00339';
                });
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $newSubMenus),
                ]);
            }
        }
    }
}
