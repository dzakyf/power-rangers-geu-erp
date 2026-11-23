<?php

use App\Support\Modules;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Menus are core, but MenuListsSeeder filters its rows to the enabled
        // modules so BDP does not seed CNS/HCGA entries. Production can import
        // live menu_lists instead by setting SEED_MENUS=false.
        if (filter_var(env('SEED_MENUS', true), FILTER_VALIDATE_BOOLEAN)) {
            $this->call(MenuListsSeeder::class);
        }

        // Only bootstrap users on a fresh database; never touch existing users
        // during an upgrade.
        if (DB::table('users')->count() === 0) {
            $this->call(UserSeeder::class);
        }

        if (Modules::isEnabled('cns')) {
            $this->call(CnsMenuListsSeeder::class);
        }

        $this->call(RbacSeeder::class);

        if (Modules::isEnabled('bp')) {
            $this->call(BpSurveyMasterAccessSeeder::class);
            $this->call(SurveyConfigurationSeeder::class);
        }

        if (Modules::isEnabled('finance')) {
            $this->call(FinanceHumanResourcesMenuSeeder::class);
        }

        if (Modules::isEnabled('hcga')) {
            $this->call(HcgaPermissionSeeder::class);
        }

        // Reference data is normally imported from the live database in
        // production. Keep seeders for empty/dev databases; set
        // SEED_REFERENCE_DATA=false when importing live master data.
        if (filter_var(env('SEED_REFERENCE_DATA', true), FILTER_VALIDATE_BOOLEAN)) {
            $this->call(MasterBankSeeder::class);
            $this->call(ApprovalLevelSeeder::class);
            $this->call(ConfigSystemSeeder::class);
            $this->call(PrivilegeAccessMappingSeeder::class);
        }

        if (Modules::isEnabled('cns')) {
            $this->call(CnsMenuSampleDataSeeder::class);
            $this->call(CnsQualityControlSeeder::class);
            $this->call(CnsFlowSeeder::class);
        }

        // Demo/seed data is intentionally excluded from production seeding.
    }
}
