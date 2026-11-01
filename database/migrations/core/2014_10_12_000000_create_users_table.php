<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->integer('id', true); // signed int auto-increment (matches remote)
            $table->string('username', 225);
            $table->string('Emp_ID', 30)->nullable();
            $table->string('Name', 100);
            $table->char('password', 100);
            $table->string('Location', 20);
            $table->string('Position', 100)->nullable();
            $table->string('Contact', 30)->nullable();
            $table->string('email', 40)->nullable();
            $table->string('Company_ID', 20)->nullable();
            $table->string('Department', 40)->nullable();
            $table->string('City_ID', 20)->nullable();
            $table->string('Priv_ID', 20)->nullable();
            $table->string('Area', 20)->nullable();
            $table->string('Class', 15)->nullable();
            $table->string('Expired', 24)->nullable();
            $table->integer('ChangePwd')->nullable();
            $table->string('Status', 12)->default('ACTIVE');
            $table->string('DOP_Class', 15)->default('NONE');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->text('Note')->nullable();
            $table->string('LastLogin', 20)->nullable();
            $table->integer('COD_Login')->nullable();
            $table->text('MainPriv')->nullable();
            $table->string('Alternatif_Pass', 50)->nullable();
            $table->string('mobile_id', 50)->nullable();
            $table->dateTime('update_at')->nullable();
            $table->integer('email_confirmed')->default(1);
            $table->string('remember_token', 100)->nullable();
            $table->string('PR_Approval', 100)->default('');
            $table->string('WH_ID', 30)->nullable();
            $table->text('Main_Menu')->nullable();
            $table->text('Sub_Menu')->nullable();
            $table->boolean('BP_Approval')->default(false);
            $table->integer('Blasting')->default(0);
            $table->string('Blasting_Status', 150)->nullable();
            $table->date('Blasting_Date')->nullable();
            $table->boolean('Admin_Chat')->default(false);
            $table->text('Address')->nullable();
            $table->string('Bank_Account_No', 100)->nullable();
            $table->string('Bank_Name', 100)->nullable();
            $table->string('City', 100)->nullable();
            $table->string('Bank_Account_Name', 100)->nullable();
            $table->integer('Training_Approval')->default(0);
            $table->string('TokenAuth', 255)->nullable();

            $table->unique('username', 'UserID');
            $table->unique('Emp_ID', 'Emp_ID');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
