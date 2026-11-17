<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCnsCustomerKycTableAndMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cns_customer_kyc')) {
            Schema::create('cns_customer_kyc', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('kyc_no', 60)->unique();
                $table->string('filler_name', 150);
                $table->date('form_date');
                $table->unsignedBigInteger('supplier_id')->nullable()->index();

                // General Company Information
                $table->string('company_name', 255);
                $table->string('company_reg_number', 100)->nullable();
                $table->date('incorporation_date')->nullable();
                $table->text('registered_address')->nullable();
                $table->text('admin_accounting_address')->nullable();
                $table->string('legal_entity_type', 100)->nullable();
                $table->string('vat_number', 100)->nullable();
                $table->string('website', 255)->nullable();
                $table->string('trading_contact_person', 150)->nullable();
                $table->text('trading_contact_details')->nullable();
                $table->string('finance_contact_person', 150)->nullable();
                $table->text('finance_contact_details')->nullable();

                // Ownership & Management
                $table->text('business_activities')->nullable();
                $table->text('countries_of_activities')->nullable();

                // Supporting Documents
                $table->string('doc_register_extract', 255)->nullable();
                $table->string('doc_register_extract_name', 255)->nullable();
                $table->string('doc_certificate', 255)->nullable();
                $table->string('doc_certificate_name', 255)->nullable();

                // Metadata
                $table->string('location', 50)->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('menu_lists')) {
            $menus = [
                'SS00094' => [
                    'Name'        => 'Customer Compliance',
                    'Path_Loc'    => '#',
                    'Modules'     => 'CNS',
                    'Position'    => 3,
                    'Parent_Menu' => 'SM00065',
                    'Urutan'      => 3,
                ],
                'SI00346' => [
                    'Name'        => 'KYC',
                    'Path_Loc'    => 'cns-customer-compliance-kyc',
                    'Modules'     => 'CNS',
                    'Position'    => 4,
                    'Parent_Menu' => 'SS00094',
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $menuIds = ['SI00346', 'SS00094'];

        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->whereIn('Menu_ID', $menuIds)->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->where(function ($q) {
                    $q->where('Sub_Menu', 'like', '%SI00346%')
                        ->orWhere('Sub_Menu', 'like', '%SS00094%');
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

        Schema::dropIfExists('cns_customer_kyc');
    }
}
