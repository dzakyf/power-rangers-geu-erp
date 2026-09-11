<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarColumnsToSppdTable extends Migration
{
    public function up()
    {
        Schema::table('sppd', function (Blueprint $table) {
            if (!Schema::hasColumn('sppd', 'Need_Car')) {
                $table->tinyInteger('Need_Car')->default(0);
            }
            if (!Schema::hasColumn('sppd', 'Car_Jenis_Mobil')) {
                $table->string('Car_Jenis_Mobil', 100)->nullable();
            }
            if (!Schema::hasColumn('sppd', 'Car_Tgl_Pinjam')) {
                $table->date('Car_Tgl_Pinjam')->nullable();
            }
            if (!Schema::hasColumn('sppd', 'Car_Jam_Pinjam')) {
                $table->string('Car_Jam_Pinjam', 20)->nullable();
            }
            if (!Schema::hasColumn('sppd', 'Car_Tgl_Kembali')) {
                $table->date('Car_Tgl_Kembali')->nullable();
            }
            if (!Schema::hasColumn('sppd', 'Car_Jam_Kembali')) {
                $table->string('Car_Jam_Kembali', 20)->nullable();
            }
            if (!Schema::hasColumn('sppd', 'Car_Rent_No')) {
                $table->string('Car_Rent_No', 100)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('sppd', function (Blueprint $table) {
            foreach ([
                'Need_Car',
                'Car_Jenis_Mobil',
                'Car_Tgl_Pinjam',
                'Car_Jam_Pinjam',
                'Car_Tgl_Kembali',
                'Car_Jam_Kembali',
                'Car_Rent_No',
            ] as $column) {
                if (Schema::hasColumn('sppd', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
