<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalFieldsToReimbursementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('reimbursement')) {
            Schema::table('reimbursement', function (Blueprint $table) {
                if (!Schema::hasColumn('reimbursement', 'Approval_Request_ID')) {
                    $table->unsignedBigInteger('Approval_Request_ID')->nullable()->after('Status')->index();
                }
                if (!Schema::hasColumn('reimbursement', 'Approver_ID')) {
                    $table->unsignedBigInteger('Approver_ID')->nullable()->after('Approval_Request_ID');
                }
                if (!Schema::hasColumn('reimbursement', 'Approver_Name')) {
                    $table->string('Approver_Name', 150)->nullable()->after('Approver_ID');
                }
                if (!Schema::hasColumn('reimbursement', 'Approval_Note')) {
                    $table->text('Approval_Note')->nullable()->after('Approver_Name');
                }
                if (!Schema::hasColumn('reimbursement', 'Approved_At')) {
                    $table->dateTime('Approved_At')->nullable()->after('Approval_Note');
                }
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
        if (Schema::hasTable('reimbursement')) {
            Schema::table('reimbursement', function (Blueprint $table) {
                $columns = ['Approval_Request_ID', 'Approver_ID', 'Approver_Name', 'Approval_Note', 'Approved_At'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('reimbursement', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}

