<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBcNoToDeliveryNotesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('delivery_notes')) {
            return;
        }

        Schema::table('delivery_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_notes', 'BC_No')) {
                $table->string('BC_No', 150)->nullable()->after('Contract_No');
                $table->unique(['BC_No'], 'uniq_dn_bc_no');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('delivery_notes')) {
            return;
        }

        Schema::table('delivery_notes', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_notes', 'BC_No')) {
                $table->dropUnique('uniq_dn_bc_no');
                $table->dropColumn('BC_No');
            }
        });
    }
}
