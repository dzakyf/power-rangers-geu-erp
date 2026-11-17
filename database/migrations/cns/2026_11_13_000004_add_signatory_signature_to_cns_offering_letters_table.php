<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSignatorySignatureToCnsOfferingLettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cns_offering_letters') && !Schema::hasColumn('cns_offering_letters', 'signatory_signature')) {
            Schema::table('cns_offering_letters', function (Blueprint $table) {
                $table->text('signatory_signature')->nullable()->after('signatory_title');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('cns_offering_letters') && Schema::hasColumn('cns_offering_letters', 'signatory_signature')) {
            Schema::table('cns_offering_letters', function (Blueprint $table) {
                $table->dropColumn('signatory_signature');
            });
        }
    }
}
