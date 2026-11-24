<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinalBcFieldsToPackingLists extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('packing_lists')) {
            return;
        }

        Schema::table('packing_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('packing_lists', 'BC_No')) {
                $table->string('BC_No', 150)->nullable()->after('Order_No');
            }
            if (!Schema::hasColumn('packing_lists', 'Is_Final')) {
                $table->boolean('Is_Final')->default(false)->after('BC_No');
            }
            if (!Schema::hasColumn('packing_lists', 'Description')) {
                $table->text('Description')->nullable()->after('Remarks');
            }
            if (!Schema::hasColumn('packing_lists', 'Total_Netto_KG')) {
                $table->decimal('Total_Netto_KG', 15, 2)->nullable()->after('Description');
            }
            if (!Schema::hasColumn('packing_lists', 'Total_Gross_KG')) {
                $table->decimal('Total_Gross_KG', 15, 2)->nullable()->after('Total_Netto_KG');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('packing_lists')) {
            return;
        }

        $columns = ['BC_No', 'Is_Final', 'Description', 'Total_Netto_KG', 'Total_Gross_KG'];
        foreach ($columns as $column) {
            if (Schema::hasColumn('packing_lists', $column)) {
                Schema::table('packing_lists', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
}
