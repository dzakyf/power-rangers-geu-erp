<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressesToShipmentInstructions extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('shipment_instructions')) {
            return;
        }

        Schema::table('shipment_instructions', function (Blueprint $table) {
            if (!Schema::hasColumn('shipment_instructions', 'Shipper_Address')) {
                $table->text('Shipper_Address')->nullable();
            }
            if (!Schema::hasColumn('shipment_instructions', 'Consignee_Address')) {
                $table->text('Consignee_Address')->nullable();
            }
            if (!Schema::hasColumn('shipment_instructions', 'Notify_Address')) {
                $table->text('Notify_Address')->nullable();
            }
            if (!Schema::hasColumn('shipment_instructions', 'Destination_Letter_Address')) {
                $table->text('Destination_Letter_Address')->nullable();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('shipment_instructions')) {
            return;
        }

        Schema::table('shipment_instructions', function (Blueprint $table) {
            if (Schema::hasColumn('shipment_instructions', 'Shipper_Address')) {
                $table->dropColumn('Shipper_Address');
            }
            if (Schema::hasColumn('shipment_instructions', 'Consignee_Address')) {
                $table->dropColumn('Consignee_Address');
            }
            if (Schema::hasColumn('shipment_instructions', 'Notify_Address')) {
                $table->dropColumn('Notify_Address');
            }
            if (Schema::hasColumn('shipment_instructions', 'Destination_Letter_Address')) {
                $table->dropColumn('Destination_Letter_Address');
            }
        });
    }
}

