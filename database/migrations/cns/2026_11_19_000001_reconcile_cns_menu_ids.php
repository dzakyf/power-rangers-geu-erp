<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reconcile CNS menu drift without touching other modules:
 *  - remove the duplicate SS00095 row
 *  - move Master Tangki off the shared SI00342 id to SI01074
 *  - normalise the live Good Receipt row (SI01055)
 *
 * SI00337 and SI00339 are intentionally left to CREDENTIAL.
 */
class ReconcileCnsMenuIds extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('menu_lists')) {
            return;
        }

        // 1. Remove exact duplicate SS00095 rows, keeping the lowest id.
        $keepId = DB::table('menu_lists')->where('Menu_ID', 'SS00095')->min('ID');
        if ($keepId !== null) {
            DB::table('menu_lists')
                ->where('Menu_ID', 'SS00095')
                ->where('ID', '!=', $keepId)
                ->delete();
        }

        // 2. Move the Master Tangki row off the Master Kemasan id.
        $masterTank = DB::table('menu_lists')
            ->where('Menu_ID', 'SI00342')
            ->where('Path_Loc', 'cns-master-tank')
            ->first();

        if ($masterTank && !DB::table('menu_lists')->where('Menu_ID', 'SI01074')->exists()) {
            DB::table('menu_lists')->where('ID', $masterTank->ID)->update([
                'Menu_ID'     => 'SI01074',
                'Name'        => 'Master Tangki',
                'Path_Loc'    => 'cns-master-tank',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00088',
                'Urutan'      => 4,
            ]);
        }

        // 3. Normalise the live Good Receipt row to the agreed target.
        $goodReceipt = DB::table('menu_lists')->where('Menu_ID', 'SI01055')->first();
        $goodReceiptData = [
            'Name'        => 'Good Receipt',
            'Path_Loc'    => 'cns-procurement-goods-receipt',
            'Modules'     => 'CNS',
            'Position'    => 4,
            'Parent_Menu' => 'SS00086',
            'Urutan'      => 1,
        ];

        if ($goodReceipt && $goodReceipt->Modules === 'CNS') {
            DB::table('menu_lists')->where('ID', $goodReceipt->ID)->update($goodReceiptData);
        } elseif (!$goodReceipt) {
            DB::table('menu_lists')->insert(array_merge(['Menu_ID' => 'SI01055'], $goodReceiptData));
        }

        // 4. Keep Master Tangki visible for existing CNS users.
        if (Schema::hasTable('users') && DB::table('menu_lists')->where('Menu_ID', 'SI01074')->exists()) {
            $users = DB::table('users')
                ->where(function ($q) {
                    $q->where('Main_Menu', 'CNS')
                      ->orWhere('username', 'admin')
                      ->orWhere('Priv_ID', 'ADM');
                })
                ->get();

            foreach ($users as $user) {
                $subMenus = array_values(array_filter(explode(',', (string) $user->Sub_Menu)));
                if (!in_array('SI01074', $subMenus, true)) {
                    $subMenus[] = 'SI01074';
                    DB::table('users')->where('id', $user->id)->update([
                        'Sub_Menu' => implode(',', $subMenus),
                    ]);
                }
            }
        }
    }

    public function down()
    {
        // Data repair only; restoring the previous duplicate state is unsafe.
    }
}
