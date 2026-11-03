<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixClockOutStatusDefaultInAttendantLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('attendant_log')) {
            // Ubah default kolom Clock_Out_Status menjadi NULL
            DB::statement("ALTER TABLE `attendant_log` MODIFY `Clock_Out_Status` VARCHAR(50) NULL DEFAULT NULL");

            // Bersihkan data yang belum Clock Out tetapi terlanjur bernilai EARLY_LEAVE
            DB::table('attendant_log')
                ->whereNull('End_Time')
                ->where('Clock_Out_Status', 'EARLY_LEAVE')
                ->update(['Clock_Out_Status' => null]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
