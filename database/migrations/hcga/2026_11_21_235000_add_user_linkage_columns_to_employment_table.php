<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserLinkageColumnsToEmploymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employment')) {
            Schema::table('employment', function (Blueprint $table) {
                $afterRecStatus = Schema::hasColumn('employment', 'Recruitment_Status');

                if (!Schema::hasColumn('employment', 'user_id')) {
                    $col = $table->unsignedBigInteger('user_id')->nullable()->index();
                    if ($afterRecStatus) {
                        $col->after('Recruitment_Status');
                    }
                }

                if (!Schema::hasColumn('employment', 'candidate_user_id')) {
                    $col = $table->unsignedBigInteger('candidate_user_id')->nullable()->index();
                    if (Schema::hasColumn('employment', 'user_id')) {
                        $col->after('user_id');
                    }
                }

                if (!Schema::hasColumn('employment', 'user_account_source')) {
                    $col = $table->string('user_account_source', 30)->nullable();
                    if (Schema::hasColumn('employment', 'candidate_user_id')) {
                        $col->after('candidate_user_id');
                    }
                }

                if (!Schema::hasColumn('employment', 'user_linked_by')) {
                    $col = $table->string('user_linked_by', 150)->nullable();
                    if (Schema::hasColumn('employment', 'user_account_source')) {
                        $col->after('user_account_source');
                    }
                }

                if (!Schema::hasColumn('employment', 'user_linked_at')) {
                    $col = $table->dateTime('user_linked_at')->nullable();
                    if (Schema::hasColumn('employment', 'user_linked_by')) {
                        $col->after('user_linked_by');
                    }
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
        if (Schema::hasTable('employment')) {
            Schema::table('employment', function (Blueprint $table) {
                foreach (['user_id', 'candidate_user_id', 'user_account_source', 'user_linked_by', 'user_linked_at'] as $col) {
                    if (Schema::hasColumn('employment', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
