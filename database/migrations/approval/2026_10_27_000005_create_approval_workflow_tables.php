<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalWorkflowTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('approval_request_task')) {
            Schema::create('approval_request_task', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Request_ID')->index();
                $table->integer('Stage_Order')->index();
                $table->integer('Level_Number')->index();
                $table->unsignedBigInteger('Position_ID')->index();
                $table->string('Position_Code', 50)->nullable()->index();
                $table->string('Position_Name', 150)->nullable();
                $table->string('Department', 100)->nullable()->index();
                $table->string('Approval_Mode', 20)->default('ALL');
                $table->integer('Quorum')->nullable();
                $table->string('Status', 30)->default('PENDING')->index();
                $table->unsignedBigInteger('Claimed_By')->nullable()->index();
                $table->timestamp('Claimed_At')->nullable();
                $table->unsignedBigInteger('Acted_By')->nullable()->index();
                $table->timestamp('Acted_At')->nullable();
                $table->string('Action', 20)->nullable();
                $table->text('Notes')->nullable();
                $table->tinyInteger('Is_Self_Approval')->default(0);
                $table->timestamp('Held_At')->nullable();
                $table->string('Held_Reason', 100)->nullable();
                $table->unsignedBigInteger('Released_By')->nullable();
                $table->timestamp('Released_At')->nullable();
                $table->text('Release_Reason')->nullable();
                $table->json('Snapshot')->nullable();
                $table->timestamp('Created_At')->useCurrent();
                $table->timestamp('Updated_At')->nullable();

                $table->unique(['Request_ID', 'Stage_Order', 'Position_ID'], 'approval_task_unique_position_stage');
                $table->index(['Request_ID', 'Status'], 'approval_task_request_status_index');
                $table->index(['Claimed_By', 'Status'], 'approval_task_claim_index');
            });
        }

        if (!Schema::hasTable('approval_task_candidate')) {
            Schema::create('approval_task_candidate', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Task_ID')->index();
                $table->unsignedBigInteger('User_Id')->index();
                $table->unsignedBigInteger('Position_ID')->index();
                $table->string('Candidate_Type', 20)->default('HOLDER');
                $table->unsignedBigInteger('Delegation_ID')->nullable()->index();
                $table->string('Status', 20)->default('ACTIVE')->index();
                $table->timestamp('Notified_At')->nullable();
                $table->timestamp('Created_At')->useCurrent();

                $table->unique(['Task_ID', 'User_Id', 'Candidate_Type'], 'approval_candidate_unique');
                $table->index(['User_Id', 'Status'], 'approval_candidate_user_status_index');
            });
        }

        if (!Schema::hasTable('approval_delegation')) {
            Schema::create('approval_delegation', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Delegator_Id')->index();
                $table->unsignedBigInteger('Delegate_Id')->index();
                $table->unsignedBigInteger('Position_ID')->nullable()->index();
                $table->string('Request_Type', 100)->nullable()->index();
                $table->string('Location', 50)->index();
                $table->string('Company_ID', 50)->index();
                $table->date('Start_Date')->nullable();
                $table->date('End_Date')->nullable();
                $table->text('Reason')->nullable();
                $table->string('Status', 20)->default('ACTIVE')->index();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->useCurrent();
                $table->timestamp('Updated_At')->nullable();

                $table->index(['Delegator_Id', 'Status'], 'approval_delegation_delegator_index');
                $table->index(['Delegate_Id', 'Status'], 'approval_delegation_delegate_index');
            });
        }

        if (!Schema::hasTable('approval_self_exception')) {
            Schema::create('approval_self_exception', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Request_ID')->index();
                $table->unsignedBigInteger('Task_ID')->nullable()->index();
                $table->unsignedBigInteger('User_Id')->index();
                $table->unsignedBigInteger('Granted_By')->index();
                $table->string('Permission_Used', 100);
                $table->text('Reason');
                $table->timestamp('Expires_At')->nullable();
                $table->timestamp('Revoked_At')->nullable();
                $table->timestamp('Created_At')->useCurrent();
            });
        }

        if (!Schema::hasTable('approval_held_notification')) {
            Schema::create('approval_held_notification', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Task_ID')->index();
                $table->unsignedBigInteger('User_Id')->nullable()->index();
                $table->string('Channel', 20)->default('SYSTEM');
                $table->timestamp('Notified_At')->useCurrent();
                $table->timestamp('Acknowledged_At')->nullable();
                $table->json('Metadata')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('approval_held_notification');
        Schema::dropIfExists('approval_self_exception');
        Schema::dropIfExists('approval_delegation');
        Schema::dropIfExists('approval_task_candidate');
        Schema::dropIfExists('approval_request_task');
    }
}