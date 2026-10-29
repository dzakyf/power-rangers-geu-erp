<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermitLicenseTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('permit_license_categories')) {
            Schema::create('permit_license_categories', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('Code', 20)->unique();
                $table->string('Name', 150);
                $table->integer('Sort_Order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permit_licenses')) {
            Schema::create('permit_licenses', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Category_ID')->nullable()->index();
                $table->string('Name', 255);
                $table->string('Issuer', 255)->nullable();
                $table->string('Permit_Number', 150)->nullable();
                $table->date('Issue_Date')->nullable();
                $table->enum('Validity_Type', ['TEXT', 'END_DATE', 'PERIOD'])->default('TEXT');
                $table->text('Validity_Text')->nullable();
                $table->date('Valid_From')->nullable();
                $table->date('Valid_To')->nullable();
                $table->string('PIC_Name', 150)->nullable();
                $table->string('PIC_Contact', 150)->nullable();
                $table->unsignedBigInteger('Meta_File_ID')->nullable()->index();
                $table->unsignedSmallInteger('Reminder_Days')->default(30);
                $table->dateTime('Last_Reminder_At')->nullable();
                $table->date('Last_Reminder_Expiry')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamps();

                $table->index(['Validity_Type', 'Valid_To']);
            });
        }

        if (!Schema::hasTable('permit_license_reminder_log')) {
            Schema::create('permit_license_reminder_log', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Permit_ID')->index();
                $table->date('Expiry_Date');
                $table->unsignedSmallInteger('Reminder_Days')->default(30);
                $table->date('Reminder_Date');
                $table->timestamp('Created_At')->useCurrent();

                $table->unique(['Permit_ID', 'Expiry_Date'], 'permit_license_reminder_unique');
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
        Schema::dropIfExists('permit_license_reminder_log');
        Schema::dropIfExists('permit_licenses');
        Schema::dropIfExists('permit_license_categories');
    }
}