<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaTicketTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ga_ticket')) {
            Schema::create('ga_ticket', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->integer('Ticket_ID')->unique();
                $table->string('Location', 50)->nullable()->index();
                $table->string('Company_ID', 50)->nullable()->index();
                $table->unsignedBigInteger('Reporter_User_Id')->nullable()->index();
                $table->string('Reporter_Name', 150)->nullable();
                $table->string('Reporter_Department', 100)->nullable();
                $table->string('Reporter_Email', 150)->nullable();
                $table->string('Reporter_Phone', 50)->nullable();
                $table->unsignedBigInteger('On_Behalf_Of_User_Id')->nullable();
                $table->unsignedBigInteger('Category_ID')->nullable()->index();
                $table->unsignedBigInteger('Subcategory_ID')->nullable()->index();
                $table->string('Priority', 20)->default('NORMAL')->index();
                $table->string('Source', 20)->default('FORM');
                $table->unsignedBigInteger('QR_Point_ID')->nullable()->index();
                $table->unsignedBigInteger('Sla_Policy_ID')->nullable()->index();
                $table->dateTime('Response_Due_At')->nullable();
                $table->dateTime('Resolve_Due_At')->nullable();
                $table->dateTime('First_Response_At')->nullable();
                $table->dateTime('Resolved_At')->nullable();
                $table->dateTime('Closed_At')->nullable();
                $table->dateTime('On_Hold_At')->nullable();
                $table->integer('Total_Hold_Minutes')->default(0);
                $table->string('Assignee_Type', 20)->nullable();
                $table->unsignedBigInteger('Assignee_Team_ID')->nullable()->index();
                $table->unsignedBigInteger('Assignee_User_Id')->nullable()->index();
                $table->unsignedBigInteger('Assignee_Supplier_ID')->nullable()->index();
                $table->text('Description')->nullable();
                $table->text('Triage_Note')->nullable();
                $table->text('Rejection_Reason')->nullable();
                $table->string('Cost_Center', 50)->nullable();
                $table->string('Department', 100)->nullable()->index();
                $table->integer('Reopen_Count')->default(0);
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->useCurrent();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();

                $table->index(['Location', 'Company_ID', 'Priority'], 'ga_ticket_scope_priority_index');
            });
        }

        if (!Schema::hasTable('ga_ticket_assignment')) {
            Schema::create('ga_ticket_assignment', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->integer('Ticket_ID')->index();
                $table->string('Assignee_Type', 20)->default('TEAM');
                $table->unsignedBigInteger('Team_ID')->nullable()->index();
                $table->unsignedBigInteger('User_Id')->nullable()->index();
                $table->unsignedBigInteger('Supplier_ID')->nullable()->index();
                $table->string('Assigned_By', 100)->nullable();
                $table->dateTime('Assigned_At');
                $table->text('Reason')->nullable();
                $table->tinyInteger('Is_Current')->default(1)->index();
                $table->timestamp('Created_At')->useCurrent();
            });
        }

        if (!Schema::hasTable('ga_ticket_comment')) {
            Schema::create('ga_ticket_comment', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->integer('Ticket_ID')->index();
                $table->text('Comment');
                $table->tinyInteger('Is_Internal')->default(0);
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->useCurrent();
            });
        }

        if (!Schema::hasTable('ga_ticket_attachment')) {
            Schema::create('ga_ticket_attachment', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->integer('Ticket_ID')->index();
                $table->string('File_Path', 255);
                $table->string('File_Name', 255);
                $table->string('Mime_Type', 100)->nullable();
                $table->unsignedBigInteger('File_Size')->nullable();
                $table->string('Uploaded_By', 100)->nullable();
                $table->timestamp('Created_At')->useCurrent();
            });
        }

        if (!Schema::hasTable('ga_ticket_status_history')) {
            Schema::create('ga_ticket_status_history', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->integer('Ticket_ID')->index();
                $table->string('From_Status', 30)->nullable();
                $table->string('To_Status', 30);
                $table->text('Note')->nullable();
                $table->string('Changed_By', 100)->nullable();
                $table->dateTime('Changed_At');
                $table->timestamp('Created_At')->useCurrent();
            });
        }

        if (!Schema::hasTable('ga_ticket_sla_event')) {
            Schema::create('ga_ticket_sla_event', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->integer('Ticket_ID')->index();
                $table->string('Event_Type', 40)->index();
                $table->dateTime('Event_At');
                $table->text('Notified_To')->nullable();
                $table->timestamp('Created_At')->useCurrent();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ga_ticket_sla_event');
        Schema::dropIfExists('ga_ticket_status_history');
        Schema::dropIfExists('ga_ticket_attachment');
        Schema::dropIfExists('ga_ticket_comment');
        Schema::dropIfExists('ga_ticket_assignment');
        Schema::dropIfExists('ga_ticket');
    }
}
