<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovedByToPackingLists extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('packing_lists')) {
            return;
        }

        Schema::table('packing_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('packing_lists', 'approved_by')) {
                $table->integer('approved_by')->nullable()->after('Product_Name');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('packing_lists')) {
            return;
        }

        Schema::table('packing_lists', function (Blueprint $table) {
            if (Schema::hasColumn('packing_lists', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
        });
    }
}
