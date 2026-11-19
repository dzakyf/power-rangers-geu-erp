<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInventoryColumnsAndUserHistoryTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('asset_list') && !Schema::hasColumn('asset_list', 'Kondisi_Aset')) {
            Schema::table('asset_list', function (Blueprint $table) {
                $table->string('Kondisi_Aset', 20)->default('BAIK')->after('Status');
            });
        }

        if (!Schema::hasTable('asset_user_history')) {
            Schema::create('asset_user_history', function (Blueprint $table) {
                $table->increments('ID');
                $table->string('Asset_No', 50)->index('idx_asset_user_history_asset_no');
                $table->string('From_User', 100)->nullable();
                $table->string('To_User', 100);
                $table->string('Source_Type', 30)->default('MANUAL_UPDATE');
                $table->string('Reference_No', 60)->nullable();
                $table->date('Effective_Date');
                $table->text('Note')->nullable();
                $table->string('Created_By', 55);
                $table->timestamp('Created_At')->useCurrent();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('asset_user_history');

        if (Schema::hasTable('asset_list') && Schema::hasColumn('asset_list', 'Kondisi_Aset')) {
            Schema::table('asset_list', function (Blueprint $table) {
                $table->dropColumn('Kondisi_Aset');
            });
        }
    }
}
