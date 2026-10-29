<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermitLicenseMailTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('permit_license_mail_settings')) {
            Schema::create('permit_license_mail_settings', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('Subject_Template')->nullable();
                $table->text('Intro_Text')->nullable();
                $table->text('Footer_Text')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permit_license_mail_recipients')) {
            Schema::create('permit_license_mail_recipients', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('Name')->nullable();
                $table->string('Email', 150);
                $table->boolean('Is_Active')->default(1);
                $table->string('Created_By', 100)->nullable();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamps();
                $table->unique('Email', 'permit_license_mail_recipient_email_unique');
            });
        }

        if (!Schema::hasTable('permit_license_mail_log')) {
            Schema::create('permit_license_mail_log', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Permit_ID');
                $table->date('Expiry_Date');
                $table->string('Email_To', 150);
                $table->text('Subject')->nullable();
                $table->enum('Status', ['QUEUED', 'SENT', 'FAILED'])->default('QUEUED');
                $table->unsignedTinyInteger('Attempts')->default(0);
                $table->text('Error')->nullable();
                $table->timestamp('Sent_At')->nullable();
                $table->timestamp('Created_At')->useCurrent();
                $table->timestamp('Updated_At')->nullable();
                $table->unique(['Permit_ID', 'Expiry_Date', 'Email_To'], 'permit_license_mail_unique');
                $table->index('Status', 'permit_license_mail_status_index');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('permit_license_mail_log');
        Schema::dropIfExists('permit_license_mail_recipients');
        Schema::dropIfExists('permit_license_mail_settings');
    }
}