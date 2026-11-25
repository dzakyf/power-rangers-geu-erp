<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendGaTicketingForItSupportAndVoucher extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ga_ticket')) {
            Schema::table('ga_ticket', function (Blueprint $table) {
                if (!Schema::hasColumn('ga_ticket', 'Domain_Type')) {
                    $table->string('Domain_Type', 30)->default('GA')->index('ga_ticket_domain_type_idx')->after('Company_ID');
                }
                if (!Schema::hasColumn('ga_ticket', 'Workflow_Mode')) {
                    $table->string('Workflow_Mode', 40)->default('OPERATIONAL')->index('ga_ticket_workflow_mode_idx')->after('Domain_Type');
                }
                if (!Schema::hasColumn('ga_ticket', 'Action_Type')) {
                    $table->string('Action_Type', 20)->default('CORRECTIVE')->index('ga_ticket_action_type_idx')->after('Workflow_Mode');
                }

                // IT Support tracking & preventive/corrective fields
                if (!Schema::hasColumn('ga_ticket', 'Diagnosis_Note')) {
                    $table->text('Diagnosis_Note')->nullable()->after('Triage_Note');
                }
                if (!Schema::hasColumn('ga_ticket', 'Root_Cause')) {
                    $table->text('Root_Cause')->nullable()->after('Diagnosis_Note');
                }
                if (!Schema::hasColumn('ga_ticket', 'Recovery_Note')) {
                    $table->text('Recovery_Note')->nullable()->after('Root_Cause');
                }
                if (!Schema::hasColumn('ga_ticket', 'Testing_Status')) {
                    $table->string('Testing_Status', 20)->nullable()->after('Recovery_Note');
                }
                if (!Schema::hasColumn('ga_ticket', 'Testing_Note')) {
                    $table->text('Testing_Note')->nullable()->after('Testing_Status');
                }
                if (!Schema::hasColumn('ga_ticket', 'Testing_At')) {
                    $table->dateTime('Testing_At')->nullable()->after('Testing_Note');
                }
                if (!Schema::hasColumn('ga_ticket', 'Escalated_Flag')) {
                    $table->tinyInteger('Escalated_Flag')->default(0)->after('Testing_At');
                }
                if (!Schema::hasColumn('ga_ticket', 'Escalation_Note')) {
                    $table->text('Escalation_Note')->nullable()->after('Escalated_Flag');
                }
                if (!Schema::hasColumn('ga_ticket', 'Escalated_To_Ticket_ID')) {
                    $table->integer('Escalated_To_Ticket_ID')->nullable()->index('ga_ticket_escalated_to_idx')->after('Escalation_Note');
                }
                if (!Schema::hasColumn('ga_ticket', 'Device_Spec')) {
                    $table->text('Device_Spec')->nullable()->after('Escalated_To_Ticket_ID');
                }
                if (!Schema::hasColumn('ga_ticket', 'Device_Asset_No')) {
                    $table->string('Device_Asset_No', 50)->nullable()->index('ga_ticket_device_asset_idx')->after('Device_Spec');
                }
                if (!Schema::hasColumn('ga_ticket', 'Device_Assigned_User_Id')) {
                    $table->unsignedBigInteger('Device_Assigned_User_Id')->nullable()->after('Device_Asset_No');
                }
                if (!Schema::hasColumn('ga_ticket', 'Handover_Note')) {
                    $table->text('Handover_Note')->nullable()->after('Device_Assigned_User_Id');
                }
                if (!Schema::hasColumn('ga_ticket', 'Handover_At')) {
                    $table->dateTime('Handover_At')->nullable()->after('Handover_Note');
                }
                if (!Schema::hasColumn('ga_ticket', 'PR_No')) {
                    $table->string('PR_No', 60)->nullable()->index('ga_ticket_pr_no_idx')->after('Handover_At');
                }
                if (!Schema::hasColumn('ga_ticket', 'Kb_Flag')) {
                    $table->tinyInteger('Kb_Flag')->default(0)->index('ga_ticket_kb_flag_idx')->after('PR_No');
                }
                if (!Schema::hasColumn('ga_ticket', 'Kb_Title')) {
                    $table->string('Kb_Title', 200)->nullable()->after('Kb_Flag');
                }
                if (!Schema::hasColumn('ga_ticket', 'Kb_Solution')) {
                    $table->text('Kb_Solution')->nullable()->after('Kb_Title');
                }
                if (!Schema::hasColumn('ga_ticket', 'User_Confirmed_At')) {
                    $table->dateTime('User_Confirmed_At')->nullable()->after('Kb_Solution');
                }
                if (!Schema::hasColumn('ga_ticket', 'User_Confirmed_By')) {
                    $table->string('User_Confirmed_By', 100)->nullable()->after('User_Confirmed_At');
                }
                if (!Schema::hasColumn('ga_ticket', 'User_Rating')) {
                    $table->tinyInteger('User_Rating')->nullable()->after('User_Confirmed_By');
                }
                if (!Schema::hasColumn('ga_ticket', 'User_Feedback')) {
                    $table->text('User_Feedback')->nullable()->after('User_Rating');
                }
                if (!Schema::hasColumn('ga_ticket', 'Monitoring_Note')) {
                    $table->text('Monitoring_Note')->nullable()->after('User_Feedback');
                }
                if (!Schema::hasColumn('ga_ticket', 'Backup_Verified')) {
                    $table->tinyInteger('Backup_Verified')->default(0)->after('Monitoring_Note');
                }
                if (!Schema::hasColumn('ga_ticket', 'Backup_Verified_At')) {
                    $table->dateTime('Backup_Verified_At')->nullable()->after('Backup_Verified');
                }
                if (!Schema::hasColumn('ga_ticket', 'Backup_Note')) {
                    $table->text('Backup_Note')->nullable()->after('Backup_Verified_At');
                }

                // 2-Phase Approval & GA Order Pekerjaan Voucher fields
                if (!Schema::hasColumn('ga_ticket', 'Dept_Approval_Request_ID')) {
                    $table->unsignedBigInteger('Dept_Approval_Request_ID')->nullable()->index('ga_ticket_dept_appr_idx')->after('Backup_Note');
                }
                if (!Schema::hasColumn('ga_ticket', 'Budget_Approval_Request_ID')) {
                    $table->unsignedBigInteger('Budget_Approval_Request_ID')->nullable()->index('ga_ticket_budget_appr_idx')->after('Dept_Approval_Request_ID');
                }
                if (!Schema::hasColumn('ga_ticket', 'Approval_Revision_Count')) {
                    $table->integer('Approval_Revision_Count')->default(0)->after('Budget_Approval_Request_ID');
                }
                if (!Schema::hasColumn('ga_ticket', 'Estimated_Amount')) {
                    $table->decimal('Estimated_Amount', 18, 2)->default(0)->after('Approval_Revision_Count');
                }
                if (!Schema::hasColumn('ga_ticket', 'Verified_Amount')) {
                    $table->decimal('Verified_Amount', 18, 2)->nullable()->after('Estimated_Amount');
                }
                if (!Schema::hasColumn('ga_ticket', 'GA_Verified_By')) {
                    $table->string('GA_Verified_By', 100)->nullable()->after('Verified_Amount');
                }
                if (!Schema::hasColumn('ga_ticket', 'GA_Verified_At')) {
                    $table->dateTime('GA_Verified_At')->nullable()->after('GA_Verified_By');
                }
                if (!Schema::hasColumn('ga_ticket', 'GA_Verification_Note')) {
                    $table->text('GA_Verification_Note')->nullable()->after('GA_Verified_At');
                }
                if (!Schema::hasColumn('ga_ticket', 'Preferred_Vendor_ID')) {
                    $table->unsignedBigInteger('Preferred_Vendor_ID')->nullable()->index('ga_ticket_pref_vendor_idx')->after('GA_Verification_Note');
                }
                if (!Schema::hasColumn('ga_ticket', 'Payment_Voucher_ID')) {
                    $table->unsignedBigInteger('Payment_Voucher_ID')->nullable()->index('ga_ticket_voucher_id_idx')->after('Preferred_Vendor_ID');
                }
            });

            try {
                Schema::table('ga_ticket', function (Blueprint $table) {
                    $table->index(['Domain_Type', 'Workflow_Mode', 'Action_Type'], 'ga_ticket_domain_mode_action_idx');
                });
            } catch (\Throwable $e) {
                // Index already exists on re-run.
            }
        }

        if (!Schema::hasTable('ga_payment_voucher')) {
            Schema::create('ga_payment_voucher', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('Voucher_No', 60)->unique();
                $table->integer('Ticket_ID')->index('ga_pv_ticket_id_idx');
                $table->unsignedBigInteger('Work_Order_ID')->nullable()->index('ga_pv_wo_id_idx');
                $table->string('Location', 50)->nullable()->index('ga_pv_location_idx');
                $table->string('Company_ID', 50)->nullable()->index('ga_pv_company_idx');
                $table->string('Department', 100)->nullable();
                $table->string('Cost_Center', 50)->nullable();
                $table->string('Payee_Type', 20)->default('VENDOR');
                $table->unsignedBigInteger('Supplier_ID')->nullable()->index('ga_pv_supplier_idx');
                $table->string('Payee_Name', 150);
                $table->string('Bank_Name', 100)->nullable();
                $table->string('Bank_Account_No', 80)->nullable();
                $table->string('Bank_Account_Name', 150)->nullable();
                $table->string('Payment_Method', 30)->default('TRANSFER');
                $table->decimal('Amount', 18, 2)->default(0);
                $table->date('Due_Date')->nullable();
                $table->text('Description')->nullable();
                $table->string('Attachment_Path', 255)->nullable();
                $table->string('Status', 30)->default('FINANCE_SUBMITTED')->index('ga_pv_status_idx');
                $table->date('Paid_Date')->nullable();
                $table->string('Payment_Ref', 150)->nullable();
                $table->string('Payment_Proof_Path', 255)->nullable();
                $table->string('Paid_By', 100)->nullable();
                $table->dateTime('Paid_At')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->useCurrent();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ga_payment_voucher');

        if (Schema::hasTable('ga_ticket')) {
            try {
                Schema::table('ga_ticket', function (Blueprint $table) {
                    $table->dropIndex('ga_ticket_domain_mode_action_idx');
                });
            } catch (\Throwable $e) {
                // Ignore missing composite index.
            }

            $indexes = [
                'ga_ticket_domain_type_idx',
                'ga_ticket_workflow_mode_idx',
                'ga_ticket_action_type_idx',
                'ga_ticket_escalated_to_idx',
                'ga_ticket_device_asset_idx',
                'ga_ticket_pr_no_idx',
                'ga_ticket_kb_flag_idx',
                'ga_ticket_dept_appr_idx',
                'ga_ticket_budget_appr_idx',
                'ga_ticket_pref_vendor_idx',
                'ga_ticket_voucher_id_idx',
            ];
            foreach ($indexes as $indexName) {
                try {
                    Schema::table('ga_ticket', function (Blueprint $table) use ($indexName) {
                        $table->dropIndex($indexName);
                    });
                } catch (\Throwable $e) {
                    // Ignore missing index.
                }
            }

            $columns = [
                'Domain_Type',
                'Workflow_Mode',
                'Action_Type',
                'Diagnosis_Note',
                'Root_Cause',
                'Recovery_Note',
                'Testing_Status',
                'Testing_Note',
                'Testing_At',
                'Escalated_Flag',
                'Escalation_Note',
                'Escalated_To_Ticket_ID',
                'Device_Spec',
                'Device_Asset_No',
                'Device_Assigned_User_Id',
                'Handover_Note',
                'Handover_At',
                'PR_No',
                'Kb_Flag',
                'Kb_Title',
                'Kb_Solution',
                'User_Confirmed_At',
                'User_Confirmed_By',
                'User_Rating',
                'User_Feedback',
                'Monitoring_Note',
                'Backup_Verified',
                'Backup_Verified_At',
                'Backup_Note',
                'Dept_Approval_Request_ID',
                'Budget_Approval_Request_ID',
                'Approval_Revision_Count',
                'Estimated_Amount',
                'Verified_Amount',
                'GA_Verified_By',
                'GA_Verified_At',
                'GA_Verification_Note',
                'Preferred_Vendor_ID',
                'Payment_Voucher_ID',
            ];

            $toDrop = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('ga_ticket', $column)) {
                    $toDrop[] = $column;
                }
            }
            if (!empty($toDrop)) {
                Schema::table('ga_ticket', function (Blueprint $table) use ($toDrop) {
                    $table->dropColumn($toDrop);
                });
            }
        }
    }
}
