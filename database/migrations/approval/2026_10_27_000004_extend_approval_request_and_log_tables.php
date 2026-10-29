<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendApprovalRequestAndLogTables extends Migration
{
    public function up()
    {
        Schema::table('approval_request', function (Blueprint $table) {
            if (!Schema::hasColumn('approval_request', 'Request_No')) {
                $table->string('Request_No', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('approval_request', 'Revision_No')) {
                $table->integer('Revision_No')->default(1);
            }
            if (!Schema::hasColumn('approval_request', 'Parent_Request_ID')) {
                $table->unsignedBigInteger('Parent_Request_ID')->nullable()->index();
            }
            if (!Schema::hasColumn('approval_request', 'Policy_ID')) {
                $table->unsignedBigInteger('Policy_ID')->nullable()->index();
            }
            if (!Schema::hasColumn('approval_request', 'Policy_Version')) {
                $table->integer('Policy_Version')->nullable();
            }
            if (!Schema::hasColumn('approval_request', 'Policy_Snapshot')) {
                $table->json('Policy_Snapshot')->nullable();
            }
            if (!Schema::hasColumn('approval_request', 'Context')) {
                $table->json('Context')->nullable();
            }
            if (!Schema::hasColumn('approval_request', 'Location')) {
                $table->string('Location', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('approval_request', 'Company_ID')) {
                $table->string('Company_ID', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('approval_request', 'Department')) {
                $table->string('Department', 100)->nullable()->index();
            }
            if (!Schema::hasColumn('approval_request', 'Amount')) {
                $table->decimal('Amount', 18, 2)->nullable();
            }
            if (!Schema::hasColumn('approval_request', 'Current_Stage')) {
                $table->integer('Current_Stage')->default(0);
            }
            if (!Schema::hasColumn('approval_request', 'Returned_At')) {
                $table->timestamp('Returned_At')->nullable();
            }
            if (!Schema::hasColumn('approval_request', 'Completed_At')) {
                $table->timestamp('Completed_At')->nullable();
            }
            if (!Schema::hasColumn('approval_request', 'Cancelled_At')) {
                $table->timestamp('Cancelled_At')->nullable();
            }
            if (!Schema::hasColumn('approval_request', 'Updated_At')) {
                $table->timestamp('Updated_At')->nullable();
            }
        });

        Schema::table('approval_log', function (Blueprint $table) {
            if (!Schema::hasColumn('approval_log', 'Task_ID')) {
                $table->unsignedBigInteger('Task_ID')->nullable()->index();
            }
            if (!Schema::hasColumn('approval_log', 'Stage_Order')) {
                $table->integer('Stage_Order')->nullable();
            }
            if (!Schema::hasColumn('approval_log', 'Position_ID')) {
                $table->unsignedBigInteger('Position_ID')->nullable()->index();
            }
            if (!Schema::hasColumn('approval_log', 'Position_Code')) {
                $table->string('Position_Code', 50)->nullable();
            }
            if (!Schema::hasColumn('approval_log', 'Metadata')) {
                $table->json('Metadata')->nullable();
            }
            if (!Schema::hasColumn('approval_log', 'Is_System')) {
                $table->tinyInteger('Is_System')->default(0);
            }
            if (!Schema::hasColumn('approval_log', 'On_Behalf_Of')) {
                $table->unsignedBigInteger('On_Behalf_Of')->nullable()->index();
            }
        });
    }

    public function down()
    {
        Schema::table('approval_log', function (Blueprint $table) {
            foreach (['Task_ID', 'Stage_Order', 'Position_ID', 'Position_Code', 'Metadata', 'Is_System', 'On_Behalf_Of'] as $column) {
                if (Schema::hasColumn('approval_log', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('approval_request', function (Blueprint $table) {
            foreach ([
                'Request_No', 'Revision_No', 'Parent_Request_ID', 'Policy_ID', 'Policy_Version',
                'Policy_Snapshot', 'Context', 'Location', 'Company_ID', 'Department', 'Amount',
                'Current_Stage', 'Returned_At', 'Completed_At', 'Cancelled_At', 'Updated_At',
            ] as $column) {
                if (Schema::hasColumn('approval_request', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}