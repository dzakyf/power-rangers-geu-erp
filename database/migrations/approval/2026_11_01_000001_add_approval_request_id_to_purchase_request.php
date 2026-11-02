<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalRequestIdToPurchaseRequest extends Migration
{
    /**
     * Menghubungkan satu baris purchase_request ke tabel approval_request.
     * Kolom sengaja dibuat nullable agar data purchase_request lama tetap valid.
     */
    public function up()
    {
        if (Schema::hasTable('purchase_request') && !Schema::hasColumn('purchase_request', 'Approval_Request_ID')) {
            Schema::table('purchase_request', function (Blueprint $table) {
                $table->unsignedBigInteger('Approval_Request_ID')->nullable()->after('Status')->index();
            });
        }

        if (Schema::hasTable('requisition') && !Schema::hasColumn('requisition', 'Approval_Request_ID')) {
            Schema::table('requisition', function (Blueprint $table) {
                $table->unsignedBigInteger('Approval_Request_ID')->nullable()->index();
            });
        }

        if (class_exists('CreateApprovalWorkflowTables')) {
            (new \CreateApprovalWorkflowTables())->up();
        } else {
            require_once __DIR__ . '/2026_10_27_000005_create_approval_workflow_tables.php';
            (new \CreateApprovalWorkflowTables())->up();
        }
    }

    public function down()
    {
        if (Schema::hasTable('purchase_request') && Schema::hasColumn('purchase_request', 'Approval_Request_ID')) {
            Schema::table('purchase_request', function (Blueprint $table) {
                $table->dropColumn('Approval_Request_ID');
            });
        }
    }
}

