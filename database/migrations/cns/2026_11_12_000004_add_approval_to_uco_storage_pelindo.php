<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalToUcoStoragePelindo extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('uco_storage_pelindo')) {
            return;
        }

        Schema::table('uco_storage_pelindo', function (Blueprint $table) {
            if (!Schema::hasColumn('uco_storage_pelindo', 'Approval_Status')) {
                $table->string('Approval_Status', 20)->nullable()->after('BA_Path');
            }
            if (!Schema::hasColumn('uco_storage_pelindo', 'Approved_By')) {
                $table->string('Approved_By', 100)->nullable()->after('Approval_Status');
            }
            if (!Schema::hasColumn('uco_storage_pelindo', 'Approved_At')) {
                $table->timestamp('Approved_At')->nullable()->after('Approved_By');
            }
            if (!Schema::hasColumn('uco_storage_pelindo', 'Rejection_Reason')) {
                $table->text('Rejection_Reason')->nullable()->after('Approved_At');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('uco_storage_pelindo')) {
            return;
        }

        Schema::table('uco_storage_pelindo', function (Blueprint $table) {
            foreach (['Rejection_Reason', 'Approved_At', 'Approved_By', 'Approval_Status'] as $col) {
                if (Schema::hasColumn('uco_storage_pelindo', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
