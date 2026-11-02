<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKodeposFieldsToRecruitmentAndEmploymentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Table recruitment
        if (Schema::hasTable('recruitment')) {
            Schema::table('recruitment', function (Blueprint $table) {
                if (!Schema::hasColumn('recruitment', 'Province')) {
                    $table->string('Province', 100)->nullable()->after('City');
                }
                if (!Schema::hasColumn('recruitment', 'District')) {
                    $table->string('District', 100)->nullable()->after('Province'); // Kecamatan
                }
                if (!Schema::hasColumn('recruitment', 'Sub_District')) {
                    $table->string('Sub_District', 100)->nullable()->after('District'); // Kelurahan
                }
                if (!Schema::hasColumn('recruitment', 'ZipCode')) {
                    $table->string('ZipCode', 10)->nullable()->after('Sub_District');
                }

                // Domisili
                if (!Schema::hasColumn('recruitment', 'Province_Living')) {
                    $table->string('Province_Living', 100)->nullable()->after('City_Living');
                }
                if (!Schema::hasColumn('recruitment', 'District_Living')) {
                    $table->string('District_Living', 100)->nullable()->after('Province_Living');
                }
                if (!Schema::hasColumn('recruitment', 'Sub_District_Living')) {
                    $table->string('Sub_District_Living', 100)->nullable()->after('District_Living');
                }
                if (!Schema::hasColumn('recruitment', 'ZipCode_Living')) {
                    $table->string('ZipCode_Living', 10)->nullable()->after('Sub_District_Living');
                }
            });
        }

        // 2. Table employment
        if (Schema::hasTable('employment')) {
            Schema::table('employment', function (Blueprint $table) {
                if (!Schema::hasColumn('employment', 'Province')) {
                    $table->string('Province', 100)->nullable()->after('City');
                }
                if (!Schema::hasColumn('employment', 'District')) {
                    $table->string('District', 100)->nullable()->after('Province');
                }
                if (!Schema::hasColumn('employment', 'Sub_District')) {
                    $table->string('Sub_District', 100)->nullable()->after('District');
                }
                if (!Schema::hasColumn('employment', 'ZipCode')) {
                    $table->string('ZipCode', 10)->nullable()->after('Sub_District');
                }

                // Domisili
                if (!Schema::hasColumn('employment', 'Province_Living')) {
                    $table->string('Province_Living', 100)->nullable()->after('City_Living');
                }
                if (!Schema::hasColumn('employment', 'District_Living')) {
                    $table->string('District_Living', 100)->nullable()->after('Province_Living');
                }
                if (!Schema::hasColumn('employment', 'Sub_District_Living')) {
                    $table->string('Sub_District_Living', 100)->nullable()->after('District_Living');
                }
                if (!Schema::hasColumn('employment', 'ZipCode_Living')) {
                    $table->string('ZipCode_Living', 10)->nullable()->after('Sub_District_Living');
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
                $cols = ['Province', 'District', 'Sub_District', 'ZipCode', 'Province_Living', 'District_Living', 'Sub_District_Living', 'ZipCode_Living'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('recruitment', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('employment')) {
            Schema::table('employment', function (Blueprint $table) {
                $cols = ['Province', 'District', 'Sub_District', 'ZipCode', 'Province_Living', 'District_Living', 'Sub_District_Living', 'ZipCode_Living'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('employment', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
