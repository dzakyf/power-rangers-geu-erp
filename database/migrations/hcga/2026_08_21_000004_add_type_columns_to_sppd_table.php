<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnsToSppdTable extends Migration
{
    public function up()
    {
        Schema::table('sppd', function (Blueprint $table) {
            if (!Schema::hasColumn('sppd', 'Sppd_Type')) {
                $table->string('Sppd_Type', 20)->default('NORMAL');
            }
            if (!Schema::hasColumn('sppd', 'Date_Start')) {
                $table->date('Date_Start')->nullable();
            }
            if (!Schema::hasColumn('sppd', 'Date_Finish')) {
                $table->date('Date_Finish')->nullable();
            }
            if (!Schema::hasColumn('sppd', 'Origin_Site')) {
                $table->string('Origin_Site', 150)->nullable();
            }
            if (!Schema::hasColumn('sppd', 'Target_Site')) {
                $table->string('Target_Site', 150)->nullable();
            }
            if (!Schema::hasColumn('sppd', 'Reason')) {
                $table->text('Reason')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('sppd', function (Blueprint $table) {
            foreach (['Sppd_Type', 'Date_Start', 'Date_Finish', 'Origin_Site', 'Target_Site', 'Reason'] as $column) {
                if (Schema::hasColumn('sppd', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
