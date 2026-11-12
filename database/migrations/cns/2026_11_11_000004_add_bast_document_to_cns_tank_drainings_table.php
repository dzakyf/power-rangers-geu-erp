<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBastDocumentToCnsTankDrainingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cns_tank_drainings')) {
            return;
        }

        Schema::table('cns_tank_drainings', function (Blueprint $table) {
            if (!Schema::hasColumn('cns_tank_drainings', 'bast_document_path')) {
                $table->string('bast_document_path', 255)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('cns_tank_drainings', 'bast_document_name')) {
                $table->string('bast_document_name', 255)->nullable()->after('bast_document_path');
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
        if (!Schema::hasTable('cns_tank_drainings')) {
            return;
        }

        Schema::table('cns_tank_drainings', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('cns_tank_drainings', 'bast_document_path') ? 'bast_document_path' : null,
                Schema::hasColumn('cns_tank_drainings', 'bast_document_name') ? 'bast_document_name' : null,
            ]));

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
}
