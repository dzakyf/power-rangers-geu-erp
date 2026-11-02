<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaReusableItemTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ga_reusable_unit')) {
            Schema::create('ga_reusable_unit', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Unit_Code', 60)->unique();
                $table->string('Product_Code', 60)->index();
                $table->string('Serial_Number', 100)->nullable();
                $table->string('Asset_No', 60)->nullable()->index();
                $table->string('Category', 50)->nullable();
                $table->string('Condition_Status', 30)->default('GOOD'); // GOOD, FAIR, DAMAGED, LOST
                $table->string('Loan_Status', 30)->default('AVAILABLE')->index(); // AVAILABLE, ON_LOAN, MAINTENANCE, RETIRED
                $table->string('Current_Location', 50)->default('');
                $table->string('Current_WH_ID', 50)->default('');
                $table->string('Current_Holder_User_Id', 100)->nullable();
                $table->string('Current_Holder_Name', 150)->nullable();
                $table->string('Current_Holder_Department', 100)->nullable();
                $table->text('Notes')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }

        if (!Schema::hasTable('ga_reusable_loan')) {
            Schema::create('ga_reusable_loan', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Loan_No', 60)->unique();
                $table->unsignedInteger('Unit_ID')->index();
                $table->string('Borrower_User_Id', 100)->nullable()->index();
                $table->string('Borrower_Name', 150);
                $table->string('Borrower_Department', 100)->nullable();
                $table->string('Borrower_Contact', 50)->nullable();
                $table->string('Location', 50)->default('');
                $table->date('Date_Out');
                $table->date('Due_Date');
                $table->dateTime('Returned_At')->nullable();
                $table->string('Received_By', 100)->nullable();
                $table->string('Status', 30)->default('ACTIVE')->index(); // ACTIVE, RETURNED, OVERDUE, LOST
                $table->string('Condition_Out', 30)->default('GOOD');
                $table->string('Condition_In', 30)->nullable();
                $table->text('Purpose')->nullable();
                $table->text('Return_Notes')->nullable();
                $table->unsignedBigInteger('Ticket_ID')->nullable()->index();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }

        if (!Schema::hasTable('ga_reusable_unit_history')) {
            Schema::create('ga_reusable_unit_history', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->unsignedInteger('Unit_ID')->index();
                $table->string('Action_Type', 50); // REGISTER, LOAN, RETURN, CONDITION_CHANGE, LOCATION_CHANGE, RETIRED
                $table->string('Reference_No', 100)->nullable();
                $table->string('From_Status', 30)->nullable();
                $table->string('To_Status', 30)->nullable();
                $table->string('From_Location', 50)->nullable();
                $table->string('To_Location', 50)->nullable();
                $table->string('Holder_Name', 150)->nullable();
                $table->text('Remarks')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ga_reusable_unit_history');
        Schema::dropIfExists('ga_reusable_loan');
        Schema::dropIfExists('ga_reusable_unit');
    }
}
