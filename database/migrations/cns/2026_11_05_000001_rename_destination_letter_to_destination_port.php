<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameDestinationLetterToDestinationPort extends Migration
{
    private $columns = [
        'shipment_instructions' => 50,
        'packing_lists' => 50,
        'annex_vii' => 100,
    ];

    public function up()
    {
        foreach ($this->columns as $table => $length) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (Schema::hasColumn($table, 'Destination_Letter')
                && !Schema::hasColumn($table, 'Destination_Port')) {
                $this->renameColumn($table, 'Destination_Letter', 'Destination_Port', $length);
            }
        }
    }

    public function down()
    {
        foreach ($this->columns as $table => $length) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (Schema::hasColumn($table, 'Destination_Port')
                && !Schema::hasColumn($table, 'Destination_Letter')) {
                $this->renameColumn($table, 'Destination_Port', 'Destination_Letter', $length);
            }
        }
    }

    private function renameColumn($table, $from, $to, $length)
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `{$table}` CHANGE `{$from}` `{$to}` VARCHAR({$length}) NULL");
            return;
        }

        if ($driver === 'pgsql' || $driver === 'sqlite') {
            DB::statement("ALTER TABLE \"{$table}\" RENAME COLUMN \"{$from}\" TO \"{$to}\"");
        }
    }
}
