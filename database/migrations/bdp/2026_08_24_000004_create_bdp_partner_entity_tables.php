<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBdpPartnerEntityTables extends Migration
{
    public function up()
    {
        Schema::create('opportunity_partner', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('id');
            $table->string('lead_id', 255)->nullable();
            $table->string('register_as', 50)->nullable();
            $table->string('full_name', 255)->nullable();
            $table->string('contact', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('address')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('highest_education', 50)->nullable();
            $table->text('source_channels')->nullable();
            $table->string('pic', 100)->nullable();
            $table->string('employment_status', 50)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('business_field', 255)->nullable();
            $table->string('length_of_work', 50)->nullable();
            $table->string('length_of_work_unit', 50)->nullable();
            $table->string('position', 255)->nullable();
            $table->text('company_address')->nullable();
            $table->string('gross_monthly', 100)->nullable();
            $table->string('gross_annual', 100)->nullable();
            $table->text('sources_of_funds')->nullable();
            $table->string('warehouse_count', 50)->nullable();
            $table->text('motivation')->nullable();
            $table->string('province', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('location_status', 50)->nullable();
            $table->string('q1', 10)->nullable();
            $table->string('q2', 10)->nullable();
            $table->string('q3', 10)->nullable();
            $table->string('q4', 10)->nullable();
            $table->string('q5', 10)->nullable();
            $table->string('q6', 10)->nullable();
            $table->string('package_option', 50)->nullable();
            $table->text('supporting_documents')->nullable();
            $table->string('company_official_name', 255)->nullable();
            $table->text('legal_forms')->nullable();
            $table->string('primary_business', 255)->nullable();
            $table->string('year_established', 50)->nullable();
            $table->text('head_office_address')->nullable();
            $table->string('company_website', 255)->nullable();
            $table->string('company_tax_id', 100)->nullable();
            $table->string('company_nib', 100)->nullable();
            $table->string('pic_name', 255)->nullable();
            $table->string('pic_position', 255)->nullable();
            $table->string('pic_phone', 100)->nullable();
            $table->string('pic_email', 255)->nullable();
            $table->string('company_scale', 50)->nullable();
            $table->BigInteger('estimated_revenue')->nullable()->default(0);
            $table->text('company_funds')->nullable();
            $table->string('company_warehouse_count', 50)->nullable();
            $table->text('company_experience')->nullable();
            $table->string('company_has_location', 10)->nullable();
            $table->text('location_types')->nullable();
            $table->string('c_q1', 10)->nullable();
            $table->string('c_q2', 10)->nullable();
            $table->string('c_q3', 10)->nullable();
            $table->string('c_q4', 10)->nullable();
            $table->string('c_q5', 10)->nullable();
            $table->string('c_q6', 10)->nullable();
            $table->string('company_package', 50)->nullable();
            $table->text('company_documents')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('c_p6q1', 100)->nullable();
            $table->string('c_p6q2', 100)->nullable();
            $table->string('c_p6q3', 100)->nullable();
            $table->string('c_p6q4', 100)->nullable();
            $table->text('c_p6q1a1')->nullable();
            $table->text('c_p6q3a1')->nullable();
            $table->string('pi_rt', 100)->nullable();
            $table->string('pi_rw', 100)->nullable();
            $table->string('pi_ward', 100)->nullable();
            $table->string('pi_subdistrict', 100)->nullable();
            $table->string('pi_city', 100)->nullable();
            $table->string('pi_province', 100)->nullable();
            $table->string('pi_postal_code', 100)->nullable();
            $table->string('ktp', 100)->nullable();
            $table->string('npwp', 100)->nullable();
            $table->Integer('is_recommended_to_be_approved')->default(3);
            $table->string('status', 100)->nullable();
            $table->string('onboarding_stage', 50)->nullable();
            $table->timestamp('onboarding_stage_updated_at')->nullable();
            $table->unsignedBigInteger('onboarding_stage_updated_by')->nullable();
            $table->dateTime('contract_start_at')->nullable();
            $table->dateTime('contract_end_at')->nullable();
            $table->BigInteger('user_row_id')->nullable();
            $table->string('latitude', 100)->nullable();
            $table->string('longitude', 100)->nullable();
        });
        DB::statement("ALTER TABLE `opportunity_partner` MODIFY `id` bigint NOT NULL AUTO_INCREMENT");
        Schema::create('opportunity_partner_facility', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->Integer('Partner_ID');
            $table->string('warehouse_task_id', 150)->nullable();
            $table->Integer('Facility_ID');
            $table->boolean('Checked_Sales');
            $table->date('Send_Date')->nullable();
            $table->string('Checked_Sales_By', 150);
            $table->dateTime('Checked_Sales_Date')->nullable();
            $table->boolean('Checked_Partner');
            $table->date('Received_Date')->nullable();
            $table->string('Checked_Partner_By', 150);
            $table->dateTime('Checked_Partner_Date')->nullable();
            $table->string('Created_By', 150);
            $table->dateTime('Created_Date')->nullable()->useCurrent();
        });
        DB::statement("ALTER TABLE `opportunity_partner_facility` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
        Schema::create('opportunity_partner_facility_dtl', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->Integer('Partner_ID');
            $table->string('warehouse_task_id', 150)->nullable();
            $table->Integer('Opportunity_Facility_ID');
            $table->Integer('Facility_ID')->nullable();
            $table->double('Qty', 30, 2)->default(0);
            $table->double('Price', 30, 2)->default(0);
            $table->double('Total', 30, 2)->default(0);
            $table->date('Order_Date');
            $table->string('Created_By', 150);
            $table->dateTime('Created_Date')->useCurrent();
            $table->text('Description')->nullable();
        });
        DB::statement("ALTER TABLE `opportunity_partner_facility_dtl` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
        Schema::create('opportunity_partner_file', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('id');
            $table->string('lead_id', 150);
            $table->string('file_type', 50);
            $table->text('file_path');
            $table->string('type', 150);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
            $table->string('created_by', 150);
            $table->Integer('opportunity_id')->nullable();
        });
        DB::statement("ALTER TABLE `opportunity_partner_file` MODIFY `id` int NOT NULL AUTO_INCREMENT");
    }

    public function down()
    {
        Schema::dropIfExists('opportunity_partner_file');
        Schema::dropIfExists('opportunity_partner_facility_dtl');
        Schema::dropIfExists('opportunity_partner_facility');
        Schema::dropIfExists('opportunity_partner');
    }
}
