<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOfferingFileToRecruitmentAndEmploymentDocumentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tambahkan kolom Offering_Number, Offering_File dan Offering_Signed_Date pada tabel recruitment
        if (Schema::hasTable('recruitment')) {
            Schema::table('recruitment', function (Blueprint $table) {
                if (!Schema::hasColumn('recruitment', 'Offering_Number')) {
                    $table->string('Offering_Number', 100)->nullable();
                }
                if (!Schema::hasColumn('recruitment', 'Offering_File')) {
                    $table->string('Offering_File', 255)->nullable();
                }
                if (!Schema::hasColumn('recruitment', 'Offering_Signed_Date')) {
                    $table->dateTime('Offering_Signed_Date')->nullable();
                }
            });
        }

        // 2. Tambahkan kolom Offering_Letter pada employment_document jika ada tabelnya
        if (Schema::hasTable('employment_document')) {
            Schema::table('employment_document', function (Blueprint $table) {
                if (!Schema::hasColumn('employment_document', 'Offering_Letter')) {
                    $table->string('Offering_Letter', 255)->nullable();
                }
                if (!Schema::hasColumn('employment_document', 'Offering_Letter_Desc')) {
                    $table->string('Offering_Letter_Desc', 255)->nullable();
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
        if (Schema::hasTable('recruitment')) {
            Schema::table('recruitment', function (Blueprint $table) {
                if (Schema::hasColumn('recruitment', 'Offering_Signed_Date')) {
                    $table->dropColumn('Offering_Signed_Date');
                }
                if (Schema::hasColumn('recruitment', 'Offering_File')) {
                    $table->dropColumn('Offering_File');
                }
            });
        }

        if (Schema::hasTable('employment_document')) {
            Schema::table('employment_document', function (Blueprint $table) {
                if (Schema::hasColumn('employment_document', 'Offering_Letter_Desc')) {
                    $table->dropColumn('Offering_Letter_Desc');
                }
                if (Schema::hasColumn('employment_document', 'Offering_Letter')) {
                    $table->dropColumn('Offering_Letter');
                }
            });
        }
    }
}
