<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEffectiveTerminationAtToPartnerTermination extends Migration
{
    public function up()
    {
        Schema::table('partner_termination', function (Blueprint $table) {
            $table->date('Effective_Termination_At')->nullable()->after('Resolution');
        });
    }

    public function down()
    {
        Schema::table('partner_termination', function (Blueprint $table) {
            $table->dropColumn('Effective_Termination_At');
        });
    }
}
