<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJenisToStatusChangesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('status_changes') && !Schema::hasColumn('status_changes', 'jenis')) {
            Schema::table('status_changes', function (Blueprint $table) {
                $table->string('jenis', 20)->default('MUTATION')->index()->after('form_number');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('status_changes') && Schema::hasColumn('status_changes', 'jenis')) {
            Schema::table('status_changes', function (Blueprint $table) {
                $table->dropColumn('jenis');
            });
        }
    }
}
