<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCnsMasterUserMenu extends Migration
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

        // Add Master User menu (SI00338) under Master Data CNS (SS00088)
        $exists = DB::table('menu_lists')->where('Menu_ID', 'SI00338')->exists();
        if (!$exists) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SI00338',
                'Name'        => 'Master User',
                'Path_Loc'    => 'credential-user-management',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00088',
                'Urutan'      => 4,
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
                if (!in_array('SI00338', $subMenus)) {
                    $subMenus[] = 'SI00338';
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
            DB::table('menu_lists')->where('Menu_ID', 'SI00338')->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->where('Sub_Menu', 'like', '%SI00338%')
                ->get();

            foreach ($users as $user) {
                $subMenus = array_filter(explode(',', (string) $user->Sub_Menu));
                $filtered = array_values(array_filter($subMenus, function ($m) {
                    return $m !== 'SI00338';
                }));
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $filtered),
                ]);
            }
        }
    }
}
