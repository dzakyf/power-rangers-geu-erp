<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePerformanceKpiTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Master Periode Performance Management
        if (!Schema::hasTable('performance_periods')) {
            Schema::create('performance_periods', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('period_code', 50)->unique();
                $table->string('name', 150);
                $table->enum('period_type', ['ANNUAL', 'SEMESTER', 'QUARTERLY', 'MONTHLY'])->default('ANNUAL');
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('status', ['DRAFT', 'ACTIVE', 'EVALUATION', 'CLOSED'])->default('DRAFT');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['status', 'start_date', 'end_date']);
            });
        }

        // 2. Goal Sheet / Penetapan KPI & OKR Pegawai
        if (!Schema::hasTable('performance_goals')) {
            Schema::create('performance_goals', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('goal_no', 50)->unique();
                $table->unsignedBigInteger('period_id');
                $table->unsignedBigInteger('user_id'); // Pegawai
                $table->string('emp_id', 50)->nullable();
                $table->string('department', 100)->nullable();
                $table->string('position', 100)->nullable();
                $table->enum('goal_type', ['HYBRID', 'KPI', 'OKR'])->default('HYBRID');
                $table->enum('level', ['INDIVIDUAL', 'TEAM', 'COMPANY'])->default('INDIVIDUAL');
                $table->decimal('total_kpi_weight', 5, 2)->default(0.00); // Harus 100% untuk KPI
                $table->decimal('overall_progress_percent', 5, 2)->default(0.00); // Agregasi kemajuan
                $table->decimal('final_score', 8, 2)->nullable();
                $table->enum('status', [
                    'DRAFT',
                    'WAITING_APPROVAL',
                    'APPROVED',
                    'REJECTED',
                    'EVALUATION',
                    'COMPLETED',
                    'CANCELLED'
                ])->default('DRAFT');
                $table->unsignedBigInteger('approval_request_id')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('period_id')->references('id')->on('performance_periods')->onDelete('cascade');
                $table->index(['period_id', 'user_id', 'status']);
                $table->index('approval_request_id');
            });
        }

        // 3. Detail Item Goal (Target KPI / Objective & Key Result)
        if (!Schema::hasTable('performance_goal_items')) {
            Schema::create('performance_goal_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('goal_id');
                $table->unsignedBigInteger('parent_item_id')->nullable(); // Untuk Key Result di bawah Objective
                $table->enum('item_type', ['KPI', 'OBJECTIVE', 'KEY_RESULT'])->default('KPI');
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->string('kpi_unit', 50)->nullable(); // e.g. %, Qty, IDR, Menit
                $table->decimal('target_value', 15, 2)->default(0.00);
                $table->decimal('actual_value', 15, 2)->default(0.00);
                $table->decimal('weight_percent', 5, 2)->default(0.00); // Bobot % KPI
                $table->decimal('progress_percent', 5, 2)->default(0.00);
                $table->decimal('score', 8, 2)->default(0.00);
                $table->enum('scoring_type', ['HIGHER_BETTER', 'LOWER_BETTER', 'MILESTONE'])->default('HIGHER_BETTER');
                $table->date('due_date')->nullable();
                $table->integer('sort_order')->default(1);
                $table->timestamps();

                $table->foreign('goal_id')->references('id')->on('performance_goals')->onDelete('cascade');
                $table->index(['goal_id', 'item_type']);
            });
        }

        // 4. Progress Tracking & Check-in Log
        if (!Schema::hasTable('performance_goal_progress')) {
            Schema::create('performance_goal_progress', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('goal_item_id');
                $table->date('check_in_date');
                $table->decimal('previous_value', 15, 2)->default(0.00);
                $table->decimal('new_value', 15, 2)->default(0.00);
                $table->decimal('progress_percent', 5, 2)->default(0.00);
                $table->text('comment')->nullable();
                $table->string('attachment_path', 255)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('goal_item_id')->references('id')->on('performance_goal_items')->onDelete('cascade');
                $table->index(['goal_item_id', 'check_in_date']);
            });
        }

        // 5. Tambah Menu List ke menu_lists (Modul Performance Management)
        $parentExists = DB::table('menu_lists')->where('Menu_ID', 'SM00070')->exists();
        if (!$parentExists) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SM00070',
                'Name'        => 'Performance',
                'Path_Loc'    => '#',
                'Position'    => 2,
                'Parent_Menu' => 'MS00004', // Human Resources
                'Urutan'      => 13,
                'Modules'     => 'HRD',
            ]);
        }

        $subExists = DB::table('menu_lists')->where('Menu_ID', 'SS00106')->exists();
        if (!$subExists) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SS00106',
                'Name'        => 'Performance Management',
                'Path_Loc'    => '#',
                'Position'    => 3,
                'Parent_Menu' => 'SM00070',
                'Urutan'      => 1,
                'Modules'     => 'HRD',
            ]);
        }

        $itemExists = DB::table('menu_lists')->where('Menu_ID', 'SI01038')->exists();
        if (!$itemExists) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SI01038',
                'Name'        => 'KPI & OKR Goals',
                'Path_Loc'    => 'hrd-performance-kpi-index',
                'Position'    => 4,
                'Parent_Menu' => 'SS00106',
                'Urutan'      => 1,
                'Modules'     => 'HRD',
            ]);
        }

        $periodItemExists = DB::table('menu_lists')->where('Menu_ID', 'SI01039')->exists();
        if (!$periodItemExists) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SI01039',
                'Name'        => 'Master Periode KPI',
                'Path_Loc'    => 'hrd-performance-period-index',
                'Position'    => 4,
                'Parent_Menu' => 'SS00106',
                'Urutan'      => 2,
                'Modules'     => 'HRD',
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
        DB::table('menu_lists')->whereIn('Menu_ID', ['SI01038', 'SI01039', 'SS00106', 'SM00070'])->delete();
        Schema::dropIfExists('performance_goal_progress');
        Schema::dropIfExists('performance_goal_items');
        Schema::dropIfExists('performance_goals');
        Schema::dropIfExists('performance_periods');
    }
}

