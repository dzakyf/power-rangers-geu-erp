<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCalendarIdAndScheduleFieldsToShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('shifts', 'Calendar_ID')) {
                $table->unsignedInteger('Calendar_ID')->nullable()->after('ID');
                $table->index('Calendar_ID', 'idx_shifts_calendar_id');
            }
            if (!Schema::hasColumn('shifts', 'Day_Of_Week')) {
                $table->string('Day_Of_Week', 20)->nullable()->after('Calendar_ID');
            }
            if (!Schema::hasColumn('shifts', 'Is_Working')) {
                $table->tinyInteger('Is_Working')->default(1)->after('Day_Of_Week');
            }
            if (!Schema::hasColumn('shifts', 'Default_Start')) {
                $table->time('Default_Start')->nullable()->after('Is_Working');
            }
            if (!Schema::hasColumn('shifts', 'Default_End')) {
                $table->time('Default_End')->nullable()->after('Default_Start');
            }
            if (!Schema::hasColumn('shifts', 'Updated_By')) {
                $table->string('Updated_By', 150)->nullable()->after('Created_By');
            }
            if (!Schema::hasColumn('shifts', 'Updated_At')) {
                $table->dateTime('Updated_At')->nullable()->after('Updated_By');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('shifts', 'Calendar_ID')) $cols[] = 'Calendar_ID';
            if (Schema::hasColumn('shifts', 'Day_Of_Week')) $cols[] = 'Day_Of_Week';
            if (Schema::hasColumn('shifts', 'Is_Working')) $cols[] = 'Is_Working';
            if (Schema::hasColumn('shifts', 'Default_Start')) $cols[] = 'Default_Start';
            if (Schema::hasColumn('shifts', 'Default_End')) $cols[] = 'Default_End';
            if (Schema::hasColumn('shifts', 'Updated_By')) $cols[] = 'Updated_By';
            if (Schema::hasColumn('shifts', 'Updated_At')) $cols[] = 'Updated_At';

            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
}
