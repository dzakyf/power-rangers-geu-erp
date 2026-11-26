<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransferProofToExportInvoicingRequestsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('export_invoicing_requests')) {
            Schema::table('export_invoicing_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('export_invoicing_requests', 'transfer_proof_path')) {
                    $table->string('transfer_proof_path', 500)->nullable()->after('packing_list_file_name');
                }
                if (!Schema::hasColumn('export_invoicing_requests', 'transfer_proof_name')) {
                    $table->string('transfer_proof_name', 255)->nullable()->after('transfer_proof_path');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('export_invoicing_requests')) {
            Schema::table('export_invoicing_requests', function (Blueprint $table) {
                $cols = array_filter(['transfer_proof_path', 'transfer_proof_name'], function ($col) {
                    return Schema::hasColumn('export_invoicing_requests', $col);
                });
                if (!empty($cols)) {
                    $table->dropColumn(array_values($cols));
                }
            });
        }
    }
}
