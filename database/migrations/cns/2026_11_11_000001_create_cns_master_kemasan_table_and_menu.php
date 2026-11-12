<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCnsMasterKemasanTableAndMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cns_master_kemasan')) {
            Schema::create('cns_master_kemasan', function (Blueprint $table) {
                $table->bigIncrements('Kemasan_ID');
                $table->string('Name', 150);
                $table->decimal('Volume', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('menu_lists')) {
            $exists = DB::table('menu_lists')->where('Menu_ID', 'SI00342')->exists();
            $menuData = [
                'Name'        => 'Master Kemasan',
                'Path_Loc'    => 'cns-master-kemasan',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00088',
                'Urutan'      => 6,
            ];

            if (!$exists) {
                DB::table('menu_lists')->insert(array_merge(['Menu_ID' => 'SI00342'], $menuData));
            } else {
                DB::table('menu_lists')->where('Menu_ID', 'SI00342')->update($menuData);
            }

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
                    if (!in_array('SI00342', $subMenus)) {
                        $subMenus[] = 'SI00342';
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
        Schema::dropIfExists('cns_master_kemasan');

        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->where('Menu_ID', 'SI00342')->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->where('Sub_Menu', 'like', '%SI00342%')
                ->get();

            foreach ($users as $user) {
                $subMenus = array_filter(explode(',', (string) $user->Sub_Menu));
                $newSubMenus = array_filter($subMenus, function ($m) {
                    return $m !== 'SI00342';
                });
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $newSubMenus),
                ]);
            }
        }
    }
}
