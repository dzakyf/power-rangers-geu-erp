<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTankIdToCnsQualityControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cns_quality_controls')) {
            Schema::table('cns_quality_controls', function (Blueprint $table) {
                if (!Schema::hasColumn('cns_quality_controls', 'Tank_ID')) {
                    $table->unsignedBigInteger('Tank_ID')->nullable()->after('Booking_ID');
                    $table->index('Tank_ID');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('cns_quality_controls')) {
            Schema::table('cns_quality_controls', function (Blueprint $table) {
                if (Schema::hasColumn('cns_quality_controls', 'Tank_ID')) {
                    $table->dropIndex(['Tank_ID']);
                    $table->dropColumn('Tank_ID');
                }
            });
        }
    }
}
