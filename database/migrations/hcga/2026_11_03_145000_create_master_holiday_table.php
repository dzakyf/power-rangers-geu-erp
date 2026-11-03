<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMasterHolidayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('master_holiday')) {
            Schema::create('master_holiday', function (Blueprint $table) {
                $table->increments('ID');
                $table->integer('Year')->default(date('Y'));
                $table->string('Day_Name', 50)->nullable();
                $table->date('Holiday_Date')->unique();
                $table->enum('Is_Holiday', ['YES', 'NO'])->default('YES');
                $table->string('Description', 255)->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->timestamps();
            });

            // Sinkronisasi data dari tabel legacy `libur` jika ada
            if (Schema::hasTable('libur')) {
                $legacyData = DB::table('libur')->get();
                foreach ($legacyData as $item) {
                    $date = $item->libur_tgl;
                    if ($date) {
                        DB::table('master_holiday')->insertOrIgnore([
                            'Year'         => (int) date('Y', strtotime($date)),
                            'Day_Name'     => date('l', strtotime($date)),
                            'Holiday_Date' => $date,
                            'Is_Holiday'   => 'YES',
                            'Description'  => $item->libur_keterangan ?? 'Libur Nasional',
                            'Created_By'   => 'system.migration',
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_holiday');
    }
}
