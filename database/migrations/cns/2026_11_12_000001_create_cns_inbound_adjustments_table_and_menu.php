<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCnsInboundAdjustmentsTableAndMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cns_inbound_adjustments')) {
            Schema::create('cns_inbound_adjustments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('adjustment_code', 50)->unique();
                $table->date('adjustment_date');
                $table->string('movement_id', 100)->nullable();
                $table->string('movement_kode', 100)->index();
                $table->string('asal_warehouse', 150)->nullable();
                $table->unsignedBigInteger('tank_id')->nullable();
                $table->string('tank_name', 100);
                $table->decimal('original_qty', 14, 2)->default(0);
                $table->decimal('tank_capacity', 14, 2)->default(0);
                $table->decimal('tank_current_stock', 14, 2)->default(0);
                $table->decimal('tank_remaining', 14, 2)->default(0);
                $table->decimal('adjusted_qty', 14, 2)->default(0);
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
            $exists = DB::table('menu_lists')->where('Menu_ID', 'SI00343')->exists();
            $menuData = [
                'Name'        => 'Inbound Adjustment',
                'Path_Loc'    => 'cns-inbound-adjustment',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00087',
                'Urutan'      => 3,
            ];

            if (!$exists) {
                DB::table('menu_lists')->insert(array_merge(['Menu_ID' => 'SI00343'], $menuData));
            } else {
                DB::table('menu_lists')->where('Menu_ID', 'SI00343')->update($menuData);
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
                    if (!in_array('SI00343', $subMenus)) {
                        $subMenus[] = 'SI00343';
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
            DB::table('menu_lists')->where('Menu_ID', 'SI00343')->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->where('Sub_Menu', 'like', '%SI00343%')
                ->get();

            foreach ($users as $user) {
                $subMenus = array_filter(explode(',', (string) $user->Sub_Menu));
                $subMenus = array_values(array_filter($subMenus, function ($m) {
                    return $m !== 'SI00343';
                }));
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $subMenus),
                ]);
            }
        }

        Schema::dropIfExists('cns_inbound_adjustments');
    }
}
