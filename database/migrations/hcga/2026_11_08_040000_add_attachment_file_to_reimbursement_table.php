<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttachmentFileToReimbursementTable extends Migration
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
                if (!Schema::hasColumn('reimbursement', 'Attachment_File')) {
                    $table->unsignedBigInteger('Attachment_File')->nullable()->after('Reference_No')->index();
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
                if (Schema::hasColumn('reimbursement', 'Attachment_File')) {
                    $table->dropColumn('Attachment_File');
                }
            });
        }
    }
}

