<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateExportInvoicingRequestsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('export_invoicing_requests')) {
            Schema::create('export_invoicing_requests', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('vessel_schedule_id')->unique();
                $table->string('order_no', 100)->index();
                $table->string('stuffing_no', 100)->nullable();
                $table->string('contract_no', 100)->nullable();
                $table->string('packing_list_no', 100)->nullable();
                $table->string('buyer_name', 255)->nullable();
                $table->string('status', 30)->default('pending FAT')->index();
                $table->string('contract_file_path', 500);
                $table->string('contract_file_name', 255);
                $table->string('packing_list_file_path', 500);
                $table->string('packing_list_file_name', 255);
                $table->text('cns_notes')->nullable();
                $table->string('submitted_by', 100)->nullable();
                $table->dateTime('submitted_at')->nullable();
                $table->string('reviewed_by', 100)->nullable();
                $table->dateTime('reviewed_at')->nullable();
                $table->text('finance_notes')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('menu_lists')) {
            $menus = [
                'SM00069' => [
                    'Name' => 'Finance & Accounting',
                    'Path_Loc' => '#',
                    'Modules' => 'CNS',
                    'Position' => 2,
                    'Parent_Menu' => 'MS00011',
                    'Urutan' => 12,
                ],
                'SS00099' => [
                    'Name' => 'Finance & Accounting',
                    'Path_Loc' => '#',
                    'Modules' => 'CNS',
                    'Position' => 3,
                    'Parent_Menu' => 'SM00069',
                    'Urutan' => 1,
                ],
                'SI00353' => [
                    'Name' => 'Export Invoicing',
                    'Path_Loc' => 'finance-export-invoicing',
                    'Modules' => 'CNS',
                    'Position' => 4,
                    'Parent_Menu' => 'SS00099',
                    'Urutan' => 1,
                ],
            ];

            foreach ($menus as $menuId => $data) {
                $exists = DB::table('menu_lists')->where('Menu_ID', $menuId)->exists();
                if (!$exists) {
                    DB::table('menu_lists')->insert(array_merge(['Menu_ID' => $menuId], $data));
                } else {
                    DB::table('menu_lists')->where('Menu_ID', $menuId)->update($data);
                }
            }

            if (Schema::hasTable('users')) {
                $users = DB::table('users')
                    ->whereIn('Main_Menu', ['FINANCE', 'CNS'])
                    ->orWhere('Sub_Menu', 'like', '%MS00003%')
                    ->orWhere('Sub_Menu', 'like', '%MS00011%')
                    ->orWhere('username', 'admin')
                    ->orWhere('Priv_ID', 'ADM')
                    ->get();

                foreach ($users as $user) {
                    $subMenus = array_filter(explode(',', (string) $user->Sub_Menu));
                    $changed = false;
                    foreach (['MS00003', 'SM00069', 'SS00099', 'SI00353'] as $menuId) {
                        if (!in_array($menuId, $subMenus, true)) {
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

    public function down()
    {
        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->whereIn('Menu_ID', ['SI00353', 'SS00099', 'SM00069'])->delete();
        }
        Schema::dropIfExists('export_invoicing_requests');
    }
}
