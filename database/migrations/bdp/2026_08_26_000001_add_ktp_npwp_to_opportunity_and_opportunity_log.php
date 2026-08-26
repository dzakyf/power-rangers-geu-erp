<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKtpNpwpToOpportunityAndOpportunityLog extends Migration
{
    public function up()
    {
        Schema::table('opportunity', function (Blueprint $table) {
            $table->string('KTP', 100)->nullable()->after('Phone_Number');
            $table->string('NPWP', 100)->nullable()->after('KTP');
        });

        Schema::table('opportunity_log', function (Blueprint $table) {
            $table->string('KTP', 100)->nullable()->after('Classification');
            $table->string('NPWP', 100)->nullable()->after('KTP');
            $table->string('registration_token', 64)->nullable();
            $table->timestamp('token_expires_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('opportunity_log', function (Blueprint $table) {
            $table->dropColumn(['KTP', 'NPWP', 'registration_token', 'token_expires_at']);
        });

        Schema::table('opportunity', function (Blueprint $table) {
            $table->dropColumn(['KTP', 'NPWP']);
        });
    }
}
