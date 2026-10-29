<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendMasterPositionForOrganization extends Migration
{
    public function up()
    {
        Schema::table('master_position', function (Blueprint $table) {
            if (!Schema::hasColumn('master_position', 'Location')) {
                $table->string('Location', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('master_position', 'Company_ID')) {
                $table->string('Company_ID', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('master_position', 'Parent_Position_ID')) {
                $table->unsignedBigInteger('Parent_Position_ID')->nullable()->index();
            }
            if (!Schema::hasColumn('master_position', 'Position_Type')) {
                $table->string('Position_Type', 20)->default('STRUCTURAL');
            }
            if (!Schema::hasColumn('master_position', 'Status')) {
                $table->string('Status', 20)->default('ACTIVE')->index();
            }
            if (!Schema::hasColumn('master_position', 'Effective_From')) {
                $table->date('Effective_From')->nullable();
            }
            if (!Schema::hasColumn('master_position', 'Effective_To')) {
                $table->date('Effective_To')->nullable();
            }
            if (!Schema::hasColumn('master_position', 'Updated_By')) {
                $table->string('Updated_By', 100)->nullable();
            }
            if (!Schema::hasColumn('master_position', 'Updated_Date')) {
                $table->timestamp('Updated_Date')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('master_position', function (Blueprint $table) {
            foreach ([
                'Location', 'Company_ID', 'Parent_Position_ID', 'Position_Type',
                'Status', 'Effective_From', 'Effective_To', 'Updated_By', 'Updated_Date',
            ] as $column) {
                if (Schema::hasColumn('master_position', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}