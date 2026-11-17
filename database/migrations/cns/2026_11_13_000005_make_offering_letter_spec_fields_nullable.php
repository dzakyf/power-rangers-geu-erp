<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeOfferingLetterSpecFieldsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * Uses raw SQL so Laravel 6 does not require doctrine/dbal. The columns are
     * already created as nullable by 2026_11_13_000002; this keeps existing
     * databases consistent and is safe to re-run.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cns_offering_letters')) {
            return;
        }

        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            // Non-MySQL drivers get nullable columns from 2026_11_13_000002.
            return;
        }

        DB::statement("
            ALTER TABLE `cns_offering_letters`
                MODIFY `product` VARCHAR(255) NULL,
                MODIFY `hs_code` VARCHAR(50) NULL,
                MODIFY `certificate` VARCHAR(100) NULL,
                MODIFY `spec_ffa` VARCHAR(100) NULL,
                MODIFY `spec_mni` VARCHAR(100) NULL,
                MODIFY `spec_unsaponifiable` VARCHAR(100) NULL,
                MODIFY `spec_iodine_value` VARCHAR(100) NULL,
                MODIFY `spec_sulphur` VARCHAR(100) NULL,
                MODIFY `spec_total_metals` VARCHAR(100) NULL,
                MODIFY `spec_phosphorus` VARCHAR(100) NULL,
                MODIFY `spec_total_chloride` VARCHAR(100) NULL,
                MODIFY `quantity_mt` DECIMAL(14,2) NULL,
                MODIFY `quantity_note` VARCHAR(255) NULL,
                MODIFY `final_qty_quality` TEXT NULL,
                MODIFY `price_per_mt` DECIMAL(14,2) NULL,
                MODIFY `delivery_term` VARCHAR(50) NULL,
                MODIFY `packaging` VARCHAR(150) NULL,
                MODIFY `payment_terms` TEXT NULL,
                MODIFY `load_port_inspector` VARCHAR(255) NULL,
                MODIFY `signatory_key` VARCHAR(30) NULL,
                MODIFY `signatory_name` VARCHAR(150) NULL,
                MODIFY `signatory_title` VARCHAR(100) NULL
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
