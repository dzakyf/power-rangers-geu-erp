<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlignOrganizationStructureForApproval extends Migration
{
    /**
     * Selaraskan organization_structure dengan bagan organisasi baru dan
     * hubungkan setiap node bagan ke master_position melalui Position_ID.
     *
     * Migrasi ini idempotent: kolom yang sudah ada tidak dibuat ulang.
     */
    public function up()
    {
        if (!Schema::hasTable('organization_structure')) {
            return;
        }

        Schema::table('organization_structure', function (Blueprint $table) {
            if (!Schema::hasColumn('organization_structure', 'Position_ID')) {
                $table->unsignedBigInteger('Position_ID')->nullable()->index();
            }
            if (!Schema::hasColumn('organization_structure', 'Position_Code')) {
                $table->string('Position_Code', 30)->nullable()->index();
            }
            if (!Schema::hasColumn('organization_structure', 'Position')) {
                $table->string('Position', 100)->nullable();
            }
            if (!Schema::hasColumn('organization_structure', 'Reports_To_Position_ID')) {
                $table->unsignedBigInteger('Reports_To_Position_ID')->nullable()->index();
            }
            if (!Schema::hasColumn('organization_structure', 'Level')) {
                $table->integer('Level')->default(4);
            }
            if (!Schema::hasColumn('organization_structure', 'Dept_Name')) {
                $table->string('Dept_Name', 100)->nullable();
            }
            if (!Schema::hasColumn('organization_structure', 'Directorat')) {
                $table->string('Directorat', 50)->nullable();
            }
            if (!Schema::hasColumn('organization_structure', 'Is_System')) {
                $table->tinyInteger('Is_System')->default(0);
            }
            if (!Schema::hasColumn('organization_structure', 'Sort_Order')) {
                $table->integer('Sort_Order')->default(0);
            }
        });

        // Hubungkan node bagan ke master_position dengan nama posisi +
        // departemen. Kombinasi ini unik pada data existing (50/50 match).
        if (Schema::hasTable('master_position')
            && Schema::hasColumn('organization_structure', 'Position')
            && Schema::hasColumn('organization_structure', 'Department')
            && Schema::hasColumn('organization_structure', 'Position_ID')) {
            DB::statement("
                UPDATE organization_structure o
                JOIN master_position m
                  ON m.Position = o.Position
                 AND m.Department = o.Department
                SET o.Position_ID = m.ID
                WHERE o.Position_ID IS NULL
                  AND o.Position IS NOT NULL
                  AND o.Position <> ''
            ");
        }

        // Turunkan hierarki bagan ke master_position.Parent_Position_ID agar
        // fallback approval memakai atasan yang sama dengan bagan.
        if (Schema::hasColumn('master_position', 'Parent_Position_ID')
            && Schema::hasColumn('organization_structure', 'Position_ID')
            && Schema::hasColumn('organization_structure', 'Reports_To_Position_ID')) {
            DB::statement("
                UPDATE master_position m
                JOIN organization_structure o
                  ON o.Position_ID = m.ID
                JOIN organization_structure p
                  ON p.ID = o.Reports_To_Position_ID
                JOIN master_position mp
                  ON mp.ID = p.Position_ID
                SET m.Parent_Position_ID = mp.ID,
                    m.Updated_By = 'organization-chart',
                    m.Updated_Date = NOW()
                WHERE o.Reports_To_Position_ID IS NOT NULL
            ");

            DB::statement("
                UPDATE master_position m
                JOIN organization_structure o
                  ON o.Position_ID = m.ID
                SET m.Parent_Position_ID = NULL,
                    m.Updated_By = 'organization-chart',
                    m.Updated_Date = NOW()
                WHERE o.Reports_To_Position_ID IS NULL
                  AND m.Parent_Position_ID IS NOT NULL
            ");
        }
    }

    public function down()
    {
        if (Schema::hasTable('organization_structure')
            && Schema::hasColumn('organization_structure', 'Position_ID')) {
            Schema::table('organization_structure', function (Blueprint $table) {
                $table->dropColumn('Position_ID');
            });
        }
    }
}