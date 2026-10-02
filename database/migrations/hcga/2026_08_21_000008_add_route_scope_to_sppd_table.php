<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRouteScopeToSppdTable extends Migration
{
    public function up()
    {
        Schema::table('sppd', function (Blueprint $table) {
            if (!Schema::hasColumn('sppd', 'Distance_Km')) {
                $table->decimal('Distance_Km', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('sppd', 'Duration_Min')) {
                $table->integer('Duration_Min')->nullable();
            }
            if (!Schema::hasColumn('sppd', 'Entitlement_Scope')) {
                $table->string('Entitlement_Scope', 20)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('sppd', function (Blueprint $table) {
            foreach (['Distance_Km', 'Duration_Min', 'Entitlement_Scope'] as $column) {
                if (Schema::hasColumn('sppd', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
