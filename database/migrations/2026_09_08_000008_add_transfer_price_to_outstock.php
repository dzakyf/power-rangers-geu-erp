<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransferPriceToOutstock extends Migration
{
    public function up()
    {
        Schema::table('outstock', function (Blueprint $table) {
            $table->decimal('Transfer_Price', 15, 2)->nullable()->after('Actual_Qty');
            $table->string('Transfer_Document')->nullable()->after('Transfer_Price');
        });
    }

    public function down()
    {
        Schema::table('outstock', function (Blueprint $table) {
            $table->dropColumn(['Transfer_Price', 'Transfer_Document']);
        });
    }
}
