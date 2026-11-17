<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileAuthLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('mobile_auth_logs')) {
            Schema::create('mobile_auth_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('username', 100)->nullable()->index();
                $table->string('event', 40)->index(); // LOGIN_SUCCESS, LOGIN_FAILED, LOGOUT, TOKEN_ISSUED, TOKEN_REVOKED
                $table->string('channel', 20)->default('WEB'); // WEB, API
                $table->string('device_name', 150)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('created_at')->useCurrent();
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
        Schema::dropIfExists('mobile_auth_logs');
    }
}
