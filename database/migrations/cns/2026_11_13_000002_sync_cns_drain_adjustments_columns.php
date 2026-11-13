<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SyncCnsDrainAdjustmentsColumns extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cns_drain_adjustments')) {
            return;
        }

        Schema::table('cns_drain_adjustments', function (Blueprint $table) {
            if (!Schema::hasColumn('cns_drain_adjustments', 'adjustment_code')) {
                $table->string('adjustment_code', 50)->unique()->after('id');
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'adjustment_date')) {
                $table->date('adjustment_date')->nullable();
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'tank_id')) {
                $table->unsignedBigInteger('tank_id')->index();
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'tank_name')) {
                $table->string('tank_name', 100)->default('');
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'tank_capacity')) {
                $table->decimal('tank_capacity', 14, 2)->default(0);
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'qty_sistem')) {
                $table->decimal('qty_sistem', 14, 2)->default(0);
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'qty_timbang')) {
                $table->decimal('qty_timbang', 14, 2)->default(0);
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'selisih_qty')) {
                $table->decimal('selisih_qty', 14, 2)->default(0);
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'document_path')) {
                $table->string('document_path', 255)->nullable();
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'document_name')) {
                $table->string('document_name', 255)->nullable();
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'approval_status')) {
                $table->string('approval_status', 20)->default('PENDING')->index();
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'approved_by')) {
                $table->string('approved_by', 100)->nullable();
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'created_by')) {
                $table->string('created_by', 100)->nullable();
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'updated_by')) {
                $table->string('updated_by', 100)->nullable();
            }
            if (!Schema::hasColumn('cns_drain_adjustments', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down()
    {
        // Non-destructive sync migration
    }
}
