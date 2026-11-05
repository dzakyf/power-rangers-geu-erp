<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalPriceToTraceabilityExternalPooFragments extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('traceability_external_poo_fragments')) {
            return;
        }

        if (!Schema::hasColumn('traceability_external_poo_fragments', 'total_price')) {
            $after = Schema::hasColumn('traceability_external_poo_fragments', 'price')
                ? 'price'
                : null;

            Schema::table('traceability_external_poo_fragments', function (Blueprint $table) use ($after) {
                $column = $table->decimal('total_price', 15, 2)->nullable();
                if ($after) {
                    $column->after($after);
                }
            });
        }
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live columns).
    }
}
