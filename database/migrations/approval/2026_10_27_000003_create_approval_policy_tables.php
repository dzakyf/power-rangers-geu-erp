<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalPolicyTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('approval_policy')) {
            Schema::create('approval_policy', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('Policy_Code', 100)->unique();
                $table->string('Policy_Name', 150);
                $table->string('Request_Type', 100)->index();
                $table->string('Location', 50)->nullable()->index();
                $table->string('Company_ID', 50)->nullable()->index();
                $table->string('Department', 100)->nullable()->index();
                $table->decimal('Min_Amount', 18, 2)->nullable();
                $table->decimal('Max_Amount', 18, 2)->nullable();
                $table->json('Conditions')->nullable();
                $table->string('Rejection_Mode', 30)->default('RETURN_TO_CREATOR');
                $table->integer('Rejection_Quorum')->nullable();
                $table->string('On_No_Higher_Level', 20)->default('AUTO_APPROVE');
                $table->string('On_Vacancy', 20)->default('FALLBACK');
                $table->tinyInteger('Allow_Self_Approval')->default(0);
                $table->tinyInteger('Allow_Delegation')->default(1);
                $table->string('Status', 20)->default('DRAFT')->index();
                $table->integer('Version')->default(1);
                $table->date('Effective_From')->nullable();
                $table->date('Effective_To')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->useCurrent();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();

                $table->index(['Request_Type', 'Status'], 'approval_policy_type_status_index');
                $table->index(['Location', 'Company_ID', 'Department'], 'approval_policy_scope_index');
            });
        }

        if (!Schema::hasTable('approval_policy_step')) {
            Schema::create('approval_policy_step', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Policy_ID')->index();
                $table->integer('Stage_Order')->index();
                $table->integer('Level_Number')->index();
                $table->unsignedBigInteger('Position_ID')->nullable()->index();
                $table->string('Position_Code', 50)->nullable()->index();
                $table->string('Department', 100)->nullable()->index();
                $table->string('Approval_Mode', 20)->default('ALL');
                $table->integer('Quorum')->nullable();
                $table->tinyInteger('Is_Required')->default(1);
                $table->string('Label', 150)->nullable();
                $table->timestamp('Created_At')->useCurrent();

                $table->index(['Policy_ID', 'Stage_Order'], 'approval_policy_step_stage_index');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('approval_policy_step');
        Schema::dropIfExists('approval_policy');
    }
}