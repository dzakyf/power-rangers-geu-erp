<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSourceToStockRequest extends Migration
{
    public function up()
    {
        Schema::table('warehouse_partner_stock_request', function (Blueprint $table) {
            $table->string('source', 20)->nullable()->after('remark');
            $table->unsignedBigInteger('source_id')->nullable()->after('source');
        });

        DB::statement("UPDATE warehouse_partner_stock_request SET source = 'OUTSTOCK' WHERE reference LIKE 'OUTSTOCK:%'");
        DB::statement("
            UPDATE warehouse_partner_stock_request sr
            JOIN outstock o ON sr.reference LIKE CONCAT('OUTSTOCK: ', o.Code, ' -%')
            SET sr.source_id = o.ID
            WHERE sr.source = 'OUTSTOCK'
        ");
        DB::statement("UPDATE warehouse_partner_stock_request SET source = 'MANUAL' WHERE source IS NULL");
    }

    public function down()
    {
        Schema::table('warehouse_partner_stock_request', function (Blueprint $table) {
            $table->dropColumn(['source', 'source_id']);
        });
    }
}
