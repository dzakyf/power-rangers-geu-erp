<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameDoColumnsToBcInVesselSchedules extends Migration
{
    private $columns = [
        'DO_Meta_File_ID' => ['to' => 'BC_Meta_File_ID', 'mysql' => 'BIGINT UNSIGNED NULL'],
        'DO_No'           => ['to' => 'BC_No', 'mysql' => 'VARCHAR(150) NULL'],
        'DO_Status'       => ['to' => 'BC_Status', 'mysql' => 'VARCHAR(20) NULL'],
        'DO_Note'         => ['to' => 'BC_Note', 'mysql' => 'TEXT NULL'],
        'DO_Updated_By'   => ['to' => 'BC_Updated_By', 'mysql' => 'VARCHAR(150) NULL'],
        'DO_Updated_Date' => ['to' => 'BC_Updated_Date', 'mysql' => 'DATETIME NULL'],
    ];

    public function up()
    {
        if (Schema::hasTable('vessel_schedules')) {
            foreach ($this->columns as $from => $spec) {
                $to = $spec['to'];
                if (Schema::hasColumn('vessel_schedules', $from)
                    && !Schema::hasColumn('vessel_schedules', $to)) {
                    $this->renameColumn('vessel_schedules', $from, $to, $spec['mysql']);
                }
            }
        }

        if (Schema::hasTable('meta_files')) {
            DB::table('meta_files')
                ->where('Topic', 'cns-delivery-order')
                ->update([
                    'Topic' => 'cns-booking-confirmation',
                    'Description' => 'Booking Confirmation',
                ]);
        }
    }

    public function down()
    {
        if (Schema::hasTable('vessel_schedules')) {
            foreach ($this->columns as $from => $spec) {
                $to = $spec['to'];
                if (Schema::hasColumn('vessel_schedules', $to)
                    && !Schema::hasColumn('vessel_schedules', $from)) {
                    $this->renameColumn('vessel_schedules', $to, $from, $spec['mysql']);
                }
            }
        }

        if (Schema::hasTable('meta_files')) {
            DB::table('meta_files')
                ->where('Topic', 'cns-booking-confirmation')
                ->update([
                    'Topic' => 'cns-delivery-order',
                    'Description' => 'Delivery Order',
                ]);
        }
    }

    private function renameColumn($table, $from, $to, $mysqlType)
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `{$table}` CHANGE `{$from}` `{$to}` {$mysqlType}");
            return;
        }

        if ($driver === 'pgsql' || $driver === 'sqlite') {
            DB::statement("ALTER TABLE \"{$table}\" RENAME COLUMN \"{$from}\" TO \"{$to}\"");
        }
    }
}
