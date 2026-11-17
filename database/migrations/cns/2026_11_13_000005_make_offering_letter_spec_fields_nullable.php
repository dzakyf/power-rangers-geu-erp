<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeOfferingLetterSpecFieldsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cns_offering_letters')) {
            Schema::table('cns_offering_letters', function (Blueprint $table) {
                $table->string('product', 255)->nullable()->change();
                $table->string('hs_code', 50)->nullable()->change();
                $table->string('certificate', 100)->nullable()->change();
                $table->string('spec_ffa', 100)->nullable()->change();
                $table->string('spec_mni', 100)->nullable()->change();
                $table->string('spec_unsaponifiable', 100)->nullable()->change();
                $table->string('spec_iodine_value', 100)->nullable()->change();
                $table->string('spec_sulphur', 100)->nullable()->change();
                $table->string('spec_total_metals', 100)->nullable()->change();
                $table->string('spec_phosphorus', 100)->nullable()->change();
                $table->string('spec_total_chloride', 100)->nullable()->change();
                $table->decimal('quantity_mt', 14, 2)->nullable()->change();
                $table->string('quantity_note', 255)->nullable()->change();
                $table->text('final_qty_quality')->nullable()->change();
                $table->decimal('price_per_mt', 14, 2)->nullable()->change();
                $table->string('delivery_term', 50)->nullable()->change();
                $table->string('packaging', 150)->nullable()->change();
                $table->text('payment_terms')->nullable()->change();
                $table->string('load_port_inspector', 255)->nullable()->change();
                $table->string('signatory_key', 30)->nullable()->change();
                $table->string('signatory_name', 150)->nullable()->change();
                $table->string('signatory_title', 100)->nullable()->change();
            });
        }
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
