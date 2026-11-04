<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LinkWorkCalendarsAndRosterPatternsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tambahkan calendar_id pada roster_patterns jika belum ada
        if (Schema::hasTable('roster_patterns')) {
            Schema::table('roster_patterns', function (Blueprint $table) {
                if (!Schema::hasColumn('roster_patterns', 'calendar_id')) {
                    $table->unsignedInteger('calendar_id')->nullable()->after('id');
                    $table->index('calendar_id', 'idx_roster_calendar_id');
                }
            });
        }

        // 2. Hubungkan pola roster sampel ke kalender shift yang ada (Calendar_ID = 2 / Shifting Calendar)
        $shiftingCal = DB::table('work_calendars')->where('Employee_Type', 'SHIFT')->first();
        if ($shiftingCal) {
            DB::table('roster_patterns')->whereNull('calendar_id')->update([
                'calendar_id' => $shiftingCal->ID,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('roster_patterns') && Schema::hasColumn('roster_patterns', 'calendar_id')) {
            Schema::table('roster_patterns', function (Blueprint $table) {
                $table->dropColumn('calendar_id');
            });
        }
    }
}

