<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalRequestIdToRequisition extends Migration
{
    /**
     * Menghubungkan satu baris requisition (PTK) ke satu approval_request.
     * Kolom ini sengaja nullable agar requisition lama tetap dapat dibaca
     * oleh alur approval hardcoded sampai habis.
     */
    public function up()
    {
        if (!Schema::hasColumn('requisition', 'Approval_Request_ID')) {
            Schema::table('requisition', function (Blueprint $table) {
                $table->unsignedBigInteger('Approval_Request_ID')->nullable()->index();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('requisition', 'Approval_Request_ID')) {
            Schema::table('requisition', function (Blueprint $table) {
                $table->dropColumn('Approval_Request_ID');
            });
        }
    }
}
