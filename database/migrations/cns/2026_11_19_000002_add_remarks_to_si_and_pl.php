<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarksToSiAndPl extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipping_instructions')) {
            Schema::table('shipping_instructions', function (Blueprint $table) {
                if (!Schema::hasColumn('shipping_instructions', 'Remarks')) {
                    $table->text('Remarks')->nullable()->after('Product_Name');
                }
            });
        }

        if (Schema::hasTable('packing_lists')) {
            Schema::table('packing_lists', function (Blueprint $table) {
                if (!Schema::hasColumn('packing_lists', 'Remarks')) {
                    $table->text('Remarks')->nullable()->after('Product_Name');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('shipping_instructions') && Schema::hasColumn('shipping_instructions', 'Remarks')) {
            Schema::table('shipping_instructions', function (Blueprint $table) {
                $table->dropColumn('Remarks');
            });
        }

        if (Schema::hasTable('packing_lists') && Schema::hasColumn('packing_lists', 'Remarks')) {
            Schema::table('packing_lists', function (Blueprint $table) {
                $table->dropColumn('Remarks');
            });
        }
    }
}
