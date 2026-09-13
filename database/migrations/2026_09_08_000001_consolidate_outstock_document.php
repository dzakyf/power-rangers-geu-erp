<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConsolidateOutstockDocument extends Migration
{
    public function up()
    {
        Schema::table('outstock', function (Blueprint $table) {
            $table->decimal('Actual_Qty', 12, 2)->nullable()->after('Warehouse_Destination');
            $table->date('Handover_Date')->nullable()->after('Actual_Qty');
            $table->string('Handover_Destination')->nullable()->after('Handover_Date');
            $table->string('Handover_Photo_Path')->nullable()->after('Handover_Destination');
            $table->text('Handover_Notes')->nullable()->after('Handover_Photo_Path');
        });

        if (Schema::hasTable('outstock_handover')) {
            DB::statement("
                UPDATE outstock o
                JOIN outstock_handover h ON h.Outstock_ID = o.ID
                SET o.Actual_Qty = h.Actual_Qty,
                    o.Handover_Date = h.Date,
                    o.Handover_Destination = h.Destination,
                    o.Handover_Photo_Path = h.Photo_Path,
                    o.Handover_Notes = h.Notes
            ");
            Schema::drop('outstock_handover');
        }
    }

    public function down()
    {
        Schema::create('outstock_handover', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('Outstock_ID');
            $table->date('Date');
            $table->decimal('Actual_Qty', 12, 2)->default(0);
            $table->string('Destination')->nullable();
            $table->string('Photo_Path')->nullable();
            $table->text('Notes')->nullable();
            $table->timestamp('Created_At')->nullable();
        });

        DB::statement("
            INSERT INTO outstock_handover (Outstock_ID, Date, Actual_Qty, Destination, Photo_Path, Notes, Created_At)
            SELECT ID, Handover_Date, COALESCE(Actual_Qty, 0), Handover_Destination, Handover_Photo_Path, Handover_Notes, Updated_At
            FROM outstock
            WHERE Handover_Date IS NOT NULL
        ");

        Schema::table('outstock', function (Blueprint $table) {
            $table->dropColumn(['Actual_Qty', 'Handover_Date', 'Handover_Destination', 'Handover_Photo_Path', 'Handover_Notes']);
        });
    }
}
