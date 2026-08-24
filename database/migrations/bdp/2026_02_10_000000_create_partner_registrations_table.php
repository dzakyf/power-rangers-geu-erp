<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerRegistrationsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('partner_registrations')) {
            return;
        }

        Schema::create('partner_registrations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lead_id')->nullable();
            $table->string('register_as')->nullable();

            // Individual fields
            $table->string('full_name')->nullable();
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('gender')->nullable();
            $table->string('highest_education')->nullable();
            $table->json('source_channels')->nullable();
            $table->string('pic')->nullable();

            // Employment
            $table->string('employment_status')->nullable();
            $table->string('company_name')->nullable();
            $table->string('business_field')->nullable();
            $table->string('length_of_work')->nullable();
            $table->string('length_of_work_unit')->nullable();
            $table->string('position')->nullable();
            $table->text('company_address')->nullable();

            // Financial
            $table->string('gross_monthly')->nullable();
            $table->string('gross_annual')->nullable();
            $table->json('sources_of_funds')->nullable();

            // Partnership
            $table->string('warehouse_count')->nullable();
            $table->text('motivation')->nullable();

            // Location
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('location_status')->nullable();

            // Questions
            $table->string('q1')->nullable();
            $table->string('q2')->nullable();
            $table->string('q3')->nullable();
            $table->string('q4')->nullable();
            $table->string('q5')->nullable();
            $table->string('q6')->nullable();

            $table->string('package_option')->nullable();
            $table->json('supporting_documents')->nullable();

            // Company-specific
            $table->string('company_official_name')->nullable();
            $table->json('legal_forms')->nullable();
            $table->string('primary_business')->nullable();
            $table->string('year_established')->nullable();
            $table->text('head_office_address')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company_tax_id')->nullable();
            $table->string('company_nib')->nullable();

            // Company PIC
            $table->string('pic_name')->nullable();
            $table->string('pic_position')->nullable();
            $table->string('pic_phone')->nullable();
            $table->string('pic_email')->nullable();

            // Company financials
            $table->string('company_scale')->nullable();
            $table->string('estimated_revenue')->nullable();
            $table->json('company_funds')->nullable();

            $table->string('company_warehouse_count')->nullable();
            $table->text('company_experience')->nullable();
            $table->string('company_has_location')->nullable();
            $table->json('location_types')->nullable();

            $table->string('c_q1')->nullable();
            $table->string('c_q2')->nullable();
            $table->string('c_q3')->nullable();
            $table->string('c_q4')->nullable();
            $table->string('c_q5')->nullable();
            $table->string('c_q6')->nullable();

            $table->string('company_package')->nullable();
            $table->json('company_documents')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner_registrations');
    }
}
