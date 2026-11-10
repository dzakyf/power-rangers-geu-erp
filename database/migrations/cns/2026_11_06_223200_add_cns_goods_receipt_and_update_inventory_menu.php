<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCnsGoodsReceiptAndUpdateInventoryMenu extends Migration
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

        // 1. Add / Update Goods Receipt menu (SI00337) under Procurement CNS (SS00086)
        $existsGr = DB::table('menu_lists')->where('Menu_ID', 'SI00337')->exists();
        if (!$existsGr) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SI00337',
                'Name'        => 'Goods Receipt',
                'Path_Loc'    => 'cns-procurement-goods-receipt',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00086',
                'Urutan'      => 4,
            ]);
        }

        // 2. Update Inventory Flexi Bag (SI00336) to Inventory Non-UCO
        $existsInv = DB::table('menu_lists')->where('Menu_ID', 'SI00336')->first();
        if ($existsInv) {
            DB::table('menu_lists')->where('Menu_ID', 'SI00336')->update([
                'Name'     => 'Inventory Non-UCO',
                'Path_Loc' => 'cns-inventory-non-uco',
            ]);
        } else {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SI00336',
                'Name'        => 'Inventory Non-UCO',
                'Path_Loc'    => 'cns-inventory-non-uco',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00087',
                'Urutan'      => 5,
            ]);
        }

        // Grant permission to CNS users and admins
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
                $modified = false;
                if (!in_array('SI00337', $subMenus)) {
                    $subMenus[] = 'SI00337';
                    $modified = true;
                }
                if (!in_array('SI00336', $subMenus)) {
                    $subMenus[] = 'SI00336';
                    $modified = true;
                }
                if ($modified) {
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
            DB::table('menu_lists')->where('Menu_ID', 'SI00337')->delete();
            DB::table('menu_lists')->where('Menu_ID', 'SI00336')->update([
                'Name'     => 'Inventory Flexi Bag',
                'Path_Loc' => 'cns-inventory-flexibag',
            ]);
        }
    }
}
