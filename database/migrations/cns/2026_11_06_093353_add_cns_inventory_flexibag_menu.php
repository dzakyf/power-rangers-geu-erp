<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCnsInventoryFlexibagMenu extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('menu_lists')) {
            return;
        }

        $exists = DB::table('menu_lists')->where('Menu_ID', 'SI00336')->exists();
        if (!$exists) {
            DB::table('menu_lists')->insert([
                'Menu_ID' => 'SI00336',
                'Name' => 'Inventory Flexi Bag',
                'Path_Loc' => 'cns-inventory-flexibag',
                'Modules' => 'CNS',
                'Position' => 4,
                'Parent_Menu' => 'SS00087',
                'Urutan' => 5,
            ]);
        }

        // Grant permission to users with CNS access or admin
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
                if (!in_array('SI00336', $subMenus)) {
                    $subMenus[] = 'SI00336';
                    DB::table('users')->where('id', $user->id)->update([
                        'Sub_Menu' => implode(',', $subMenus),
                    ]);
                }
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->where('Menu_ID', 'SI00336')->delete();
        }
    }
}
