<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovedByToShipmentInstructions extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('shipment_instructions')) {
            return;
        }

        Schema::table('shipment_instructions', function (Blueprint $table) {
            if (!Schema::hasColumn('shipment_instructions', 'approved_by')) {
                $table->integer('approved_by')->nullable()->after('Product_Name');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('shipment_instructions')) {
            return;
        }

        Schema::table('shipment_instructions', function (Blueprint $table) {
            if (Schema::hasColumn('shipment_instructions', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
        });
    }
}
