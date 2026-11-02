<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhotoColumnsToGaCleaningTask extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ga_cleaning_task')) {
            Schema::table('ga_cleaning_task', function (Blueprint $table) {
                if (!Schema::hasColumn('ga_cleaning_task', 'Photo_Before')) {
                    $table->string('Photo_Before', 255)->nullable()->after('Qr_Code_Scanned');
                }
                if (!Schema::hasColumn('ga_cleaning_task', 'Photo_After')) {
                    $table->string('Photo_After', 255)->nullable()->after('Photo_Before');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('ga_cleaning_task')) {
            Schema::table('ga_cleaning_task', function (Blueprint $table) {
                if (Schema::hasColumn('ga_cleaning_task', 'Photo_Before')) {
                    $table->dropColumn('Photo_Before');
                }
                if (Schema::hasColumn('ga_cleaning_task', 'Photo_After')) {
                    $table->dropColumn('Photo_After');
                }
            });
        }
    }
}
