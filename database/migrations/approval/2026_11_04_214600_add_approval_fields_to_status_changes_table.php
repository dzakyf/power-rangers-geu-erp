<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalFieldsToStatusChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('status_changes')) {
            Schema::table('status_changes', function (Blueprint $table) {
                if (!Schema::hasColumn('status_changes', 'status')) {
                    $table->string('status', 30)->default('DRAFT')->after('jenis')->index();
                }
                if (!Schema::hasColumn('status_changes', 'approval_request_id')) {
                    $table->unsignedBigInteger('approval_request_id')->nullable()->after('status')->index();
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
        if (Schema::hasTable('status_changes')) {
            Schema::table('status_changes', function (Blueprint $table) {
                if (Schema::hasColumn('status_changes', 'approval_request_id')) {
                    $table->dropColumn('approval_request_id');
                }
                if (Schema::hasColumn('status_changes', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
}
