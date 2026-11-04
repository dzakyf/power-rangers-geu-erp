<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditAndNotesColumnsToRecruitmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recruitment', function (Blueprint $table) {
            if (!Schema::hasColumn('recruitment', 'Modified_By')) {
                $table->string('Modified_By', 100)->nullable()->after('Created_By');
            }
            if (!Schema::hasColumn('recruitment', 'Modified_Date')) {
                $table->dateTime('Modified_Date')->nullable()->after('Created_Date');
            }
            if (!Schema::hasColumn('recruitment', 'Document_Note')) {
                $table->text('Document_Note')->nullable()->after('Document_Status');
            }
            if (!Schema::hasColumn('recruitment', 'Reject_Note')) {
                $table->text('Reject_Note')->nullable()->after('Status');
            }
            if (!Schema::hasColumn('recruitment', 'Reject_Date')) {
                $table->dateTime('Reject_Date')->nullable()->after('Reject_Note');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recruitment', function (Blueprint $table) {
            $colsToDrop = [];
            foreach (['Modified_By', 'Modified_Date', 'Document_Note', 'Reject_Note', 'Reject_Date'] as $col) {
                if (Schema::hasColumn('recruitment', $col)) {
                    $colsToDrop[] = $col;
                }
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
}

