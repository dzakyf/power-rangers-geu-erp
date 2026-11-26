<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalColumnsToSdAndAnnexVii extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sustainability_declarations')) {
            Schema::table('sustainability_declarations', function (Blueprint $table) {
                if (!Schema::hasColumn('sustainability_declarations', 'Status')) {
                    $table->string('Status', 50)->default('CREATED');
                }
                if (!Schema::hasColumn('sustainability_declarations', 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable();
                }
                if (!Schema::hasColumn('sustainability_declarations', 'approved_at')) {
                    $table->dateTime('approved_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('annex_vii')) {
            Schema::table('annex_vii', function (Blueprint $table) {
                if (!Schema::hasColumn('annex_vii', 'Status')) {
                    $table->string('Status', 30)->nullable()->default('DRAFT');
                }
                if (!Schema::hasColumn('annex_vii', 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable();
                }
                if (!Schema::hasColumn('annex_vii', 'approved_at')) {
                    $table->dateTime('approved_at')->nullable();
                }
            });
        }
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live columns).
    }
}
