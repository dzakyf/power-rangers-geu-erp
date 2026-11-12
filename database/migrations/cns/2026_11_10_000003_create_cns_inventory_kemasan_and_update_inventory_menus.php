<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCnsInventoryKemasanAndUpdateInventoryMenus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Create table cns_inventory_kemasan
        if (!Schema::hasTable('cns_inventory_kemasan')) {
            Schema::create('cns_inventory_kemasan', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->date('transaction_date');
                $table->unsignedBigInteger('packaging_id')->nullable()->index();
                $table->string('item_code', 50)->nullable()->index();
                $table->string('item_name', 255)->index();
                $table->string('unit', 50)->default('PCS');
                $table->string('reference_no', 100)->nullable()->index();
                $table->enum('type', ['IN', 'OUT'])->default('IN')->index();
                $table->decimal('qty_in', 12, 2)->default(0);
                $table->decimal('qty_out', 12, 2)->default(0);
                $table->decimal('current_balance', 12, 2)->default(0);
                $table->string('notes', 255)->nullable();
                $table->string('created_by', 150)->nullable();
                $table->timestamps();
            });
        }

        // 2. Reorganize menus in menu_lists
        if (Schema::hasTable('menu_lists')) {
            // Rename SS00087 to 'Inventory UCO'
            DB::table('menu_lists')->where('Menu_ID', 'SS00087')->update([
                'Name'   => 'Inventory UCO',
                'Urutan' => 1,
            ]);

            // Create SS00091 'Inventory Non UCO' under SM00063
            $existsSs91 = DB::table('menu_lists')->where('Menu_ID', 'SS00091')->exists();
            if (!$existsSs91) {
                DB::table('menu_lists')->insert([
                    'Menu_ID'     => 'SS00091',
                    'Name'        => 'Inventory Non UCO',
                    'Path_Loc'    => '#',
                    'Modules'     => 'CNS',
                    'Position'    => 3,
                    'Parent_Menu' => 'SM00063',
                    'Urutan'      => 2,
                ]);
            } else {
                DB::table('menu_lists')->where('Menu_ID', 'SS00091')->update([
                    'Name'        => 'Inventory Non UCO',
                    'Path_Loc'    => '#',
                    'Modules'     => 'CNS',
                    'Position'    => 3,
                    'Parent_Menu' => 'SM00063',
                    'Urutan'      => 2,
                ]);
            }

            // Rename SI00317 to 'Inbound Movement' under SS00087
            DB::table('menu_lists')->where('Menu_ID', 'SI00317')->update([
                'Name'        => 'Inbound Movement',
                'Parent_Menu' => 'SS00087',
                'Urutan'      => 1,
            ]);

            // Ensure SI00340 'Tank Draining' is under SS00087 with Urutan 2
            DB::table('menu_lists')->where('Menu_ID', 'SI00340')->update([
                'Parent_Menu' => 'SS00087',
                'Urutan'      => 2,
            ]);

            // Create SI00341 'Inventory Kemasan' under SS00091
            $existsSi341 = DB::table('menu_lists')->where('Menu_ID', 'SI00341')->exists();
            if (!$existsSi341) {
                DB::table('menu_lists')->insert([
                    'Menu_ID'     => 'SI00341',
                    'Name'        => 'Inventory Kemasan',
                    'Path_Loc'    => 'cns-inventory-kemasan',
                    'Modules'     => 'CNS',
                    'Position'    => 4,
                    'Parent_Menu' => 'SS00091',
                    'Urutan'      => 1,
                ]);
            } else {
                DB::table('menu_lists')->where('Menu_ID', 'SI00341')->update([
                    'Name'        => 'Inventory Kemasan',
                    'Path_Loc'    => 'cns-inventory-kemasan',
                    'Modules'     => 'CNS',
                    'Position'    => 4,
                    'Parent_Menu' => 'SS00091',
                    'Urutan'      => 1,
                ]);
            }

            // Move SI00336 'Inventory Non-UCO' to SS00091 with Urutan 2
            DB::table('menu_lists')->where('Menu_ID', 'SI00336')->update([
                'Parent_Menu' => 'SS00091',
                'Urutan'      => 2,
            ]);

            // Sync user permissions for SS00091 and SI00341
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
                    foreach (['SS00091', 'SI00341'] as $menuId) {
                        if (!in_array($menuId, $subMenus)) {
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
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cns_inventory_kemasan');

        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->where('Menu_ID', 'SI00341')->delete();
            DB::table('menu_lists')->where('Menu_ID', 'SS00091')->delete();

            DB::table('menu_lists')->where('Menu_ID', 'SS00087')->update([
                'Name' => 'Inventory Pelindo ',
            ]);
            DB::table('menu_lists')->where('Menu_ID', 'SI00317')->update([
                'Name' => 'Inventory Pelindo',
            ]);
            DB::table('menu_lists')->where('Menu_ID', 'SI00336')->update([
                'Parent_Menu' => 'SS00087',
                'Urutan'      => 5,
            ]);
            DB::table('menu_lists')->where('Menu_ID', 'SI00340')->update([
                'Parent_Menu' => 'SS00087',
                'Urutan'      => 6,
            ]);
        }
    }
}
