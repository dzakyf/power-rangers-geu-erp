<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollTaxAndDeductionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Lapisan Tarif Progresif PPh 21 Pasal 17 UU HPP (Configurable)
        if (!Schema::hasTable('payroll_tax_brackets')) {
            Schema::create('payroll_tax_brackets', function (Blueprint $table) {
                $table->increments('id');
                $table->string('bracket_name', 50)->comment('Contoh: Lapisan I, Lapisan II, dst.');
                $table->decimal('min_amount', 18, 2)->default(0)->comment('Batas bawah nominal PKP tahunan');
                $table->decimal('max_amount', 18, 2)->nullable()->comment('Batas atas nominal PKP tahunan (null jika unlimited)');
                $table->decimal('rate_percent', 5, 2)->default(0)->comment('Persentase tarif, contoh 5.00, 15.00, 25.00, 30.00, 35.00');
                $table->string('legal_basis', 100)->default('UU HPP Pasal 17 ayat 1 huruf a');
                $table->unsignedSmallInteger('rule_year')->default(2024);
                $table->tinyInteger('is_active')->default(1);
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();

                $table->index(['is_active', 'rule_year'], 'idx_active_rule_year');
            });
        }

        // 2. Tarif Efektif Rata-Rata (TER) Bulanan PP 58/2023 & PMK 168/2023 (Configurable)
        if (!Schema::hasTable('payroll_tax_ter_rates')) {
            Schema::create('payroll_tax_ter_rates', function (Blueprint $table) {
                $table->increments('id');
                $table->enum('category', ['A', 'B', 'C'])->comment('Kategori TER: A, B, atau C');
                $table->decimal('min_gross', 18, 2)->default(0)->comment('Batas bawah bruto sebulan');
                $table->decimal('max_gross', 18, 2)->nullable()->comment('Batas atas bruto sebulan (null jika unlimited)');
                $table->decimal('ter_rate_percent', 5, 2)->default(0)->comment('Tarif efektif bulanan, misal 0.25 s/d 34.00');
                $table->string('legal_basis', 100)->default('PP 58/2023 & PMK 168/2023');
                $table->unsignedSmallInteger('rule_year')->default(2024);
                $table->tinyInteger('is_active')->default(1);
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();

                $table->index(['category', 'is_active', 'rule_year'], 'idx_ter_cat_active');
            });
        }

        // 3. Konfigurasi Parameter Deduction Dinamis & Batas/Cap
        if (!Schema::hasTable('payroll_deduction_configs')) {
            Schema::create('payroll_deduction_configs', function (Blueprint $table) {
                $table->increments('id');
                $table->string('config_key', 80)->unique();
                $table->string('config_name', 150);
                $table->string('category', 50)->default('DEDUCTION'); // DEDUCTION, TAX, BPJS, ATTENDANCE
                $table->string('value_type', 30)->default('NUMERIC'); // NUMERIC, PERCENT, FORMULA, JSON
                $table->text('value_data');
                $table->string('unit', 30)->nullable(); // IDR, %, DAYS, RATIO
                $table->string('description', 255)->nullable();
                $table->tinyInteger('is_active')->default(1);
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
            });
        }

        // 4. Update tabel existing payroll_tax_reduction untuk mapping TER
        if (Schema::hasTable('payroll_tax_reduction')) {
            Schema::table('payroll_tax_reduction', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_tax_reduction', 'ter_category')) {
                    $table->enum('ter_category', ['A', 'B', 'C'])->nullable()->after('Reduction_Amount');
                }
            });
        }

        // 5. Update tabel salary_main untuk Dynamic Approval Workflow
        if (Schema::hasTable('salary_main')) {
            Schema::table('salary_main', function (Blueprint $table) {
                if (!Schema::hasColumn('salary_main', 'approval_request_id')) {
                    $table->unsignedBigInteger('approval_request_id')->nullable()->after('Status');
                }
            });
        }

        // 6. Update tabel salary untuk audit metadata perhitungan
        if (Schema::hasTable('salary')) {
            Schema::table('salary', function (Blueprint $table) {
                if (!Schema::hasColumn('salary', 'calculation_metadata')) {
                    $table->longText('calculation_metadata')->nullable()->after('THP');
                }
                if (!Schema::hasColumn('salary', 'absent_days')) {
                    $table->unsignedInteger('absent_days')->default(0)->after('Deduction');
                }
                if (!Schema::hasColumn('salary', 'late_occurrences')) {
                    $table->unsignedInteger('late_occurrences')->default(0)->after('absent_days');
                }
                if (!Schema::hasColumn('salary', 'unpaid_leave_amount')) {
                    $table->decimal('unpaid_leave_amount', 18, 2)->default(0)->after('late_occurrences');
                }
                if (!Schema::hasColumn('salary', 'late_penalty_amount')) {
                    $table->decimal('late_penalty_amount', 18, 2)->default(0)->after('unpaid_leave_amount');
                }
                if (!Schema::hasColumn('salary', 'bpjs_tk_amount')) {
                    $table->decimal('bpjs_tk_amount', 18, 2)->default(0)->after('late_penalty_amount');
                }
                if (!Schema::hasColumn('salary', 'bpjs_kes_amount')) {
                    $table->decimal('bpjs_kes_amount', 18, 2)->default(0)->after('bpjs_tk_amount');
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
        if (Schema::hasTable('salary')) {
            Schema::table('salary', function (Blueprint $table) {
                $cols = ['calculation_metadata', 'absent_days', 'late_occurrences', 'unpaid_leave_amount', 'late_penalty_amount', 'bpjs_tk_amount', 'bpjs_kes_amount'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('salary', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('salary_main')) {
            Schema::table('salary_main', function (Blueprint $table) {
                if (Schema::hasColumn('salary_main', 'approval_request_id')) {
                    $table->dropColumn('approval_request_id');
                }
            });
        }

        if (Schema::hasTable('payroll_tax_reduction')) {
            Schema::table('payroll_tax_reduction', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_tax_reduction', 'ter_category')) {
                    $table->dropColumn('ter_category');
                }
            });
        }

        Schema::dropIfExists('payroll_deduction_configs');
        Schema::dropIfExists('payroll_tax_ter_rates');
        Schema::dropIfExists('payroll_tax_brackets');
    }
}

