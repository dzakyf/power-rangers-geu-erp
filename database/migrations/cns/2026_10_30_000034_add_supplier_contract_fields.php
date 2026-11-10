<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierContractFields extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('supplier')) {
            return;
        }

        $afterCategory = Schema::hasColumn('supplier', 'Supplier_Group_Category')
            ? 'Supplier_Group_Category'
            : null;

        if (!Schema::hasColumn('supplier', 'Supply_Category')) {
            Schema::table('supplier', function (Blueprint $table) use ($afterCategory) {
                $column = $table->string('Supply_Category', 30)->nullable();
                if ($afterCategory) {
                    $column->after($afterCategory);
                }
            });
        }

        $afterEmail = Schema::hasColumn('supplier', 'Email') ? 'Email' : null;

        if (!Schema::hasColumn('supplier', 'Emails')) {
            Schema::table('supplier', function (Blueprint $table) use ($afterEmail) {
                $column = $table->json('Emails')->nullable();
                if ($afterEmail) {
                    $column->after($afterEmail);
                }
            });
        }
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live columns).
    }
}
