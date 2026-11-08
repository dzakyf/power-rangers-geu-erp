<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConvertProcurementTablesToUtf8mb4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Konversi tabel procurement legacy dari latin1_swedish_ci ke utf8mb4_unicode_ci
        // untuk mencegah error: 3988 Conversion from collation utf8mb4_general_ci into latin1_swedish_ci impossible
        $tables = [
            'purchase_request',
            'purchase_request_log',
            'purchase_summary',
            'purchase_summary_detail',
        ];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No-op rollback to avoid converting back to latin1
    }
}

