<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalColumnsToDeliveryNotesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('delivery_notes')) {
            return;
        }

        Schema::table('delivery_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_notes', 'signatory_key')) {
                $table->unsignedBigInteger('signatory_key')->nullable()->after('Status');
            }
            if (!Schema::hasColumn('delivery_notes', 'signatory_name')) {
                $table->string('signatory_name', 150)->nullable()->after('signatory_key');
            }
            if (!Schema::hasColumn('delivery_notes', 'signatory_title')) {
                $table->string('signatory_title', 150)->nullable()->after('signatory_name');
            }
            if (!Schema::hasColumn('delivery_notes', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('signatory_title');
            }
            if (!Schema::hasColumn('delivery_notes', 'approved_at')) {
                $table->dateTime('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live columns).
    }
}
