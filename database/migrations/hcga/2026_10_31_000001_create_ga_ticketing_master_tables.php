<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaTicketingMasterTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ga_ticket_category')) {
            Schema::create('ga_ticket_category', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('Location', 50)->nullable()->index();
                $table->string('Company_ID', 50)->nullable()->index();
                $table->unsignedBigInteger('Parent_ID')->nullable()->index();
                $table->string('Code', 50);
                $table->string('Name', 150);
                $table->string('Default_Priority', 20)->default('NORMAL');
                $table->unsignedBigInteger('Default_Team_ID')->nullable();
                $table->unsignedBigInteger('Sla_Policy_ID')->nullable();
                $table->string('Status', 20)->default('ACTIVE')->index();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->useCurrent();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();
                $table->unique(['Location', 'Company_ID', 'Code'], 'ga_ticket_category_scope_code_unique');
            });
        }

        if (!Schema::hasTable('ga_sla_policy')) {
            Schema::create('ga_sla_policy', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('Location', 50)->nullable()->index();
                $table->string('Company_ID', 50)->nullable()->index();
                $table->string('Code', 50);
                $table->string('Name', 150);
                $table->unsignedBigInteger('Category_ID')->nullable()->index();
                $table->string('Priority', 20)->nullable()->index();
                $table->integer('Response_Minutes')->default(60);
                $table->integer('Resolve_Minutes')->default(480);
                $table->string('Status', 20)->default('ACTIVE')->index();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->useCurrent();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();
                $table->unique(['Location', 'Company_ID', 'Code'], 'ga_sla_policy_scope_code_unique');
            });
        }

        if (!Schema::hasTable('ga_team')) {
            Schema::create('ga_team', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('Location', 50)->nullable()->index();
                $table->string('Company_ID', 50)->nullable()->index();
                $table->string('Code', 50);
                $table->string('Name', 150);
                $table->string('Team_Type', 20)->default('INTERNAL')->index();
                $table->unsignedBigInteger('Supplier_ID')->nullable()->index();
                $table->string('Department', 100)->nullable();
                $table->string('Status', 20)->default('ACTIVE')->index();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->useCurrent();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();
                $table->unique(['Location', 'Company_ID', 'Code'], 'ga_team_scope_code_unique');
            });
        }

        if (!Schema::hasTable('ga_team_member')) {
            Schema::create('ga_team_member', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Team_ID')->index();
                $table->unsignedBigInteger('User_Id')->nullable()->index();
                $table->unsignedBigInteger('Supplier_ID')->nullable()->index();
                $table->string('Role_In_Team', 50)->nullable();
                $table->string('Status', 20)->default('ACTIVE')->index();
                $table->timestamp('Created_At')->useCurrent();
                $table->unique(['Team_ID', 'User_Id'], 'ga_team_member_unique');
            });
        }

        if (!Schema::hasTable('ga_routing_rule')) {
            Schema::create('ga_routing_rule', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('Location', 50)->nullable()->index();
                $table->string('Company_ID', 50)->nullable()->index();
                $table->unsignedBigInteger('Category_ID')->nullable()->index();
                $table->unsignedBigInteger('Subcategory_ID')->nullable()->index();
                $table->string('Priority', 20)->nullable()->index();
                $table->unsignedBigInteger('Team_ID')->index();
                $table->integer('Sort_Order')->default(0);
                $table->tinyInteger('Is_Active')->default(1)->index();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->useCurrent();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }

        if (!Schema::hasTable('ga_qr_point')) {
            Schema::create('ga_qr_point', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('Location', 50)->nullable()->index();
                $table->string('Company_ID', 50)->nullable()->index();
                $table->string('QR_Code', 100)->unique();
                $table->string('Point_Type', 20)->default('LOCATION')->index();
                $table->string('Reference_Type', 30)->nullable();
                $table->unsignedBigInteger('Reference_ID')->nullable();
                $table->string('Name', 150);
                $table->string('Building', 100)->nullable();
                $table->string('Floor', 50)->nullable();
                $table->string('Room', 100)->nullable();
                $table->string('Status', 20)->default('ACTIVE')->index();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->useCurrent();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ga_qr_point');
        Schema::dropIfExists('ga_routing_rule');
        Schema::dropIfExists('ga_team_member');
        Schema::dropIfExists('ga_team');
        Schema::dropIfExists('ga_sla_policy');
        Schema::dropIfExists('ga_ticket_category');
    }
}
