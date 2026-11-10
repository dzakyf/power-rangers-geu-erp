<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShipperToDeliveryNotes extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('delivery_notes')) {
            return;
        }

        Schema::table('delivery_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_notes', 'Shipper')) {
                $table->string('Shipper', 100)->nullable()->after('DN_No');
            }
            if (!Schema::hasColumn('delivery_notes', 'Consignee_Address')) {
                $table->text('Consignee_Address')->nullable()->after('Consignee');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('delivery_notes')) {
            return;
        }

        Schema::table('delivery_notes', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_notes', 'Shipper')) {
                $table->dropColumn('Shipper');
            }
            if (Schema::hasColumn('delivery_notes', 'Consignee_Address')) {
                $table->dropColumn('Consignee_Address');
            }
        });
    }
}

