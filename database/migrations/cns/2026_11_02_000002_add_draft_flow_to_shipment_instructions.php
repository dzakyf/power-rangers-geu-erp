<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDraftFlowToShipmentInstructions extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('shipment_instructions')) {
            return;
        }

        Schema::table('shipment_instructions', function (Blueprint $table) {
            if (!Schema::hasColumn('shipment_instructions', 'Contract_ID')) {
                $table->unsignedBigInteger('Contract_ID')->nullable();
                $table->index('Contract_ID');
            }
        });

        foreach ([
            'Order_No' => 'VARCHAR(150)',
            'ETD' => 'DATE',
            'ETA' => 'DATE',
            'Stuffing' => 'DATE',
            'PEB_Date' => 'DATE',
        ] as $column => $type) {
            if (Schema::hasColumn('shipment_instructions', $column)) {
                DB::statement("ALTER TABLE `shipment_instructions` MODIFY `{$column}` {$type} NULL");
            }
        }
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
