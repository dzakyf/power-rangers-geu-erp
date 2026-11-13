<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCnsDrainAdjustmentsTableAndMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cns_drain_adjustments')) {
            Schema::create('cns_drain_adjustments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('adjustment_code', 50)->unique();
                $table->date('adjustment_date');
                $table->unsignedBigInteger('tank_id')->index();
                $table->string('tank_name', 100);
                $table->decimal('tank_capacity', 14, 2)->default(0);
                $table->decimal('qty_sistem', 14, 2)->default(0);
                $table->decimal('qty_timbang', 14, 2)->default(0);
                $table->decimal('selisih_qty', 14, 2)->default(0);
                $table->text('notes')->nullable();
                $table->string('document_path', 255)->nullable();
                $table->string('document_name', 255)->nullable();
                $table->string('approval_status', 20)->default('PENDING')->index();
                $table->string('approved_by', 100)->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('menu_lists')) {
            $exists = DB::table('menu_lists')->where('Menu_ID', 'SI00344')->exists();
            $menuData = [
                'Name'        => 'Drain Adjustment',
                'Path_Loc'    => 'cns-drain-adjustment',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00087',
                'Urutan'      => 4,
            ];

            if (!$exists) {
                DB::table('menu_lists')->insert(array_merge(['Menu_ID' => 'SI00344'], $menuData));
            } else {
                DB::table('menu_lists')->where('Menu_ID', 'SI00344')->update($menuData);
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
                    if (!in_array('SI00344', $subMenus)) {
                        $subMenus[] = 'SI00344';
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
        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->where('Menu_ID', 'SI00344')->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->where('Sub_Menu', 'like', '%SI00344%')
                ->get();

            foreach ($users as $user) {
                $subMenus = array_values(array_filter(explode(',', (string) $user->Sub_Menu), function ($id) {
                    return trim($id) !== 'SI00344';
                }));
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $subMenus),
                ]);
            }
        }

        Schema::dropIfExists('cns_drain_adjustments');
    }
}
