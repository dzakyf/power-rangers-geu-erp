<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCapitalExpenditureTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('capital_expenditure')) {
            return;
        }

        Schema::create('capital_expenditure', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->text('Asset_Name')->nullable();
            $table->dateTime('Purchased_At')->nullable();
            $table->bigInteger('Cost')->nullable();
            $table->text('Vendor_Name')->nullable();
            $table->integer('Estimated_Life_Years')->nullable();
            $table->text('Department')->nullable();
            $table->text('Status')->nullable();
            $table->bigInteger('Opportunity_Id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('capital_expenditure');
    }
}
