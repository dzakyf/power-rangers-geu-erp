<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCnsOfferingLettersTableAndMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cns_offering_letters')) {
            Schema::create('cns_offering_letters', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('offer_no', 60)->unique();
                $table->date('offer_date');
                $table->string('seller_code', 10);
                $table->string('seller_name', 150);
                $table->string('place_of_issue', 50);
                $table->unsignedBigInteger('buyer_id')->index();
                $table->string('buyer_name', 200)->nullable();
                $table->string('product', 255)->nullable();
                $table->string('hs_code', 50)->nullable();
                $table->string('certificate', 100)->nullable();
                $table->string('spec_ffa', 100)->nullable();
                $table->string('spec_mni', 100)->nullable();
                $table->string('spec_unsaponifiable', 100)->nullable();
                $table->string('spec_iodine_value', 100)->nullable();
                $table->string('spec_sulphur', 100)->nullable();
                $table->string('spec_total_metals', 100)->nullable();
                $table->string('spec_phosphorus', 100)->nullable();
                $table->string('spec_total_chloride', 100)->nullable();
                $table->decimal('quantity_mt', 14, 2)->nullable();
                $table->string('quantity_note', 255)->nullable();
                $table->text('final_qty_quality')->nullable();
                $table->string('price_currency', 10)->default('USD');
                $table->decimal('price_per_mt', 14, 2)->nullable();
                $table->string('loading_period', 100);
                $table->string('delivery_term', 50)->nullable();
                $table->string('destination_port', 150)->nullable();
                $table->string('packaging', 150)->nullable();
                $table->text('payment_terms')->nullable();
                $table->string('load_port_inspector', 255)->nullable();
                $table->string('signatory_key', 30)->nullable();
                $table->string('signatory_name', 150)->nullable();
                $table->string('signatory_title', 100)->nullable();
                $table->text('signatory_signature')->nullable();
                $table->string('status', 20)->default('DRAFT')->index();
                $table->string('approved_by', 100)->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->date('valid_until')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('menu_lists')) {
            $menus = [
                'SS00093' => [
                    'Name'        => 'Document Control',
                    'Path_Loc'    => '#',
                    'Modules'     => 'CNS',
                    'Position'    => 3,
                    'Parent_Menu' => 'SM00065',
                    'Urutan'      => 2,
                ],
                'SI00345' => [
                    'Name'        => 'Offering Letter',
                    'Path_Loc'    => 'cns-offering-letter',
                    'Modules'     => 'CNS',
                    'Position'    => 4,
                    'Parent_Menu' => 'SS00093',
                    'Urutan'      => 1,
                ],
            ];

            foreach ($menus as $menuId => $menuData) {
                $exists = DB::table('menu_lists')->where('Menu_ID', $menuId)->exists();
                if (!$exists) {
                    DB::table('menu_lists')->insert(array_merge(['Menu_ID' => $menuId], $menuData));
                } else {
                    DB::table('menu_lists')->where('Menu_ID', $menuId)->update($menuData);
                }
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
                    $changed = false;
                    foreach (array_keys($menus) as $menuId) {
                        if (!in_array($menuId, $subMenus)) {
                            $subMenus[] = $menuId;
                            $changed = true;
                        }
                    }
                    if ($changed) {
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
        $menuIds = ['SI00345', 'SS00093'];

        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->whereIn('Menu_ID', $menuIds)->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->where(function ($q) {
                    $q->where('Sub_Menu', 'like', '%SI00345%')
                        ->orWhere('Sub_Menu', 'like', '%SS00093%');
                })
                ->get();

            foreach ($users as $user) {
                $subMenus = array_values(array_filter(explode(',', (string) $user->Sub_Menu), function ($id) use ($menuIds) {
                    return !in_array(trim($id), $menuIds, true);
                }));
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $subMenus),
                ]);
            }
        }

        Schema::dropIfExists('cns_offering_letters');
    }
}
