<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegistrationTokenToOpportunityTable extends Migration
{
    public function up()
    {
        Schema::table('opportunity', function (Blueprint $table) {
            $table->string('registration_token', 64)->nullable()->unique()->after('Reason');
            $table->timestamp('token_expires_at')->nullable()->after('registration_token');
        });
    }

    public function down()
    {
        Schema::table('opportunity', function (Blueprint $table) {
            $table->dropColumn('registration_token');
            $table->dropColumn('token_expires_at');
        });
    }
}
