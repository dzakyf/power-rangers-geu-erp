<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DecouplePayrollThrStandaloneAndApproval extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_thr_batches')) {
            // Perlebar kolom status agar menampung status dinamis approval (misal STAGE_1_MHCGA, APPROVED, REJECTED)
            DB::statement("ALTER TABLE `payroll_thr_batches` MODIFY COLUMN `status` VARCHAR(40) NOT NULL DEFAULT 'DRAFT'");

            Schema::table('payroll_thr_batches', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_thr_batches', 'tax_mode')) {
                    $table->string('tax_mode', 30)->default('NONE')->after('fallback_thr_scheme');
                }
                if (!Schema::hasColumn('payroll_thr_batches', 'total_pph21')) {
                    $table->decimal('total_pph21', 15, 2)->default(0)->after('total_amount');
                }
                if (!Schema::hasColumn('payroll_thr_batches', 'total_tax_allowance')) {
                    $table->decimal('total_tax_allowance', 15, 2)->default(0)->after('total_pph21');
                }
                if (!Schema::hasColumn('payroll_thr_batches', 'total_net_amount')) {
                    $table->decimal('total_net_amount', 15, 2)->default(0)->after('total_tax_allowance');
                }
                if (!Schema::hasColumn('payroll_thr_batches', 'approval_request_id')) {
                    $table->unsignedInteger('approval_request_id')->nullable()->after('total_net_amount');
                    $table->index('approval_request_id', 'idx_thr_batches_approval_req');
                }
                if (!Schema::hasColumn('payroll_thr_batches', 'submitted_by')) {
                    $table->string('submitted_by', 100)->nullable()->after('locked_at');
                }
                if (!Schema::hasColumn('payroll_thr_batches', 'submitted_at')) {
                    $table->dateTime('submitted_at')->nullable()->after('submitted_by');
                }
                if (!Schema::hasColumn('payroll_thr_batches', 'approved_by')) {
                    $table->string('approved_by', 100)->nullable()->after('submitted_at');
                }
                if (!Schema::hasColumn('payroll_thr_batches', 'approved_at')) {
                    $table->dateTime('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('payroll_thr_batches', 'rejected_by')) {
                    $table->string('rejected_by', 100)->nullable()->after('approved_at');
                }
                if (!Schema::hasColumn('payroll_thr_batches', 'rejected_at')) {
                    $table->dateTime('rejected_at')->nullable()->after('rejected_by');
                }
                if (!Schema::hasColumn('payroll_thr_batches', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('rejected_at');
                }
            });
        }

        if (Schema::hasTable('payroll_thr_items')) {
            Schema::table('payroll_thr_items', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_thr_items', 'ptkp_status')) {
                    $table->string('ptkp_status', 20)->nullable()->after('thr_final_amount');
                }
                if (!Schema::hasColumn('payroll_thr_items', 'is_gross_up')) {
                    $table->tinyInteger('is_gross_up')->default(0)->after('ptkp_status');
                }
                if (!Schema::hasColumn('payroll_thr_items', 'tax_ter_category')) {
                    $table->string('tax_ter_category', 10)->nullable()->after('is_gross_up');
                }
                if (!Schema::hasColumn('payroll_thr_items', 'tax_rate_percent')) {
                    $table->decimal('tax_rate_percent', 6, 2)->default(0)->after('tax_ter_category');
                }
                if (!Schema::hasColumn('payroll_thr_items', 'thr_tax_allowance')) {
                    $table->decimal('thr_tax_allowance', 15, 2)->default(0)->after('tax_rate_percent');
                }
                if (!Schema::hasColumn('payroll_thr_items', 'thr_pph21_amount')) {
                    $table->decimal('thr_pph21_amount', 15, 2)->default(0)->after('thr_tax_allowance');
                }
                if (!Schema::hasColumn('payroll_thr_items', 'thr_net_amount')) {
                    $table->decimal('thr_net_amount', 15, 2)->default(0)->after('thr_pph21_amount');
                }
                if (!Schema::hasColumn('payroll_thr_items', 'tax_metadata')) {
                    $table->text('tax_metadata')->nullable()->after('thr_net_amount');
                }
            });
        }

        if (Schema::hasTable('payroll_payslip_email_logs')) {
            Schema::table('payroll_payslip_email_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_payslip_email_logs', 'slip_type')) {
                    $table->string('slip_type', 20)->default('SALARY')->after('task_id');
                    $table->index('slip_type', 'idx_payslip_email_logs_slip_type');
                }
            });
        }

        // Backfill data eksisting agar tetap konsisten dan batch LOCKED lama langsung terbit sebagai APPROVED
        if (Schema::hasTable('payroll_thr_items') && Schema::hasColumn('payroll_thr_items', 'thr_net_amount')) {
            DB::table('payroll_thr_items')
                ->where('thr_net_amount', 0)
                ->where('thr_final_amount', '>', 0)
                ->update([
                    'thr_net_amount'    => DB::raw('thr_final_amount'),
                    'thr_pph21_amount'  => 0,
                    'thr_tax_allowance' => 0,
                ]);
        }

        if (Schema::hasTable('payroll_thr_batches')) {
            if (Schema::hasColumn('payroll_thr_batches', 'total_net_amount')) {
                DB::table('payroll_thr_batches')
                    ->where('total_net_amount', 0)
                    ->where('total_amount', '>', 0)
                    ->update([
                        'tax_mode'            => 'NONE',
                        'total_pph21'         => 0,
                        'total_tax_allowance' => 0,
                        'total_net_amount'    => DB::raw('total_amount'),
                    ]);
            }

            DB::table('payroll_thr_batches')
                ->whereIn('status', ['LOCKED', 'POSTED'])
                ->update([
                    'status'      => 'APPROVED',
                    'approved_by' => DB::raw("COALESCE(locked_by, created_by, 'SYSTEM')"),
                    'approved_at' => DB::raw('COALESCE(locked_at, updated_at, NOW())'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('payroll_payslip_email_logs') && Schema::hasColumn('payroll_payslip_email_logs', 'slip_type')) {
            Schema::table('payroll_payslip_email_logs', function (Blueprint $table) {
                $table->dropIndex('idx_payslip_email_logs_slip_type');
                $table->dropColumn('slip_type');
            });
        }

        if (Schema::hasTable('payroll_thr_items')) {
            Schema::table('payroll_thr_items', function (Blueprint $table) {
                $cols = [
                    'ptkp_status',
                    'is_gross_up',
                    'tax_ter_category',
                    'tax_rate_percent',
                    'thr_tax_allowance',
                    'thr_pph21_amount',
                    'thr_net_amount',
                    'tax_metadata',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('payroll_thr_items', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('payroll_thr_batches')) {
            Schema::table('payroll_thr_batches', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_thr_batches', 'approval_request_id')) {
                    $table->dropIndex('idx_thr_batches_approval_req');
                }
                $cols = [
                    'tax_mode',
                    'total_pph21',
                    'total_tax_allowance',
                    'total_net_amount',
                    'approval_request_id',
                    'submitted_by',
                    'submitted_at',
                    'approved_by',
                    'approved_at',
                    'rejected_by',
                    'rejected_at',
                    'rejection_reason',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('payroll_thr_batches', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
