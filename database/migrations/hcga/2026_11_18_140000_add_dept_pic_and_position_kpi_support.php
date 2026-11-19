<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDeptPicAndPositionKpiSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tabel Master PIC Departemen (1 PIC per Departemen)
        if (!Schema::hasTable('performance_dept_pics')) {
            Schema::create('performance_dept_pics', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('department', 100)->unique();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('assigned_by')->nullable();
                $table->string('notes', 255)->nullable();
                $table->timestamps();

                $table->index('user_id');
            });
        }

        // 2. Tabel Log History Penunjukan / Perubahan PIC Departemen
        if (!Schema::hasTable('performance_dept_pic_logs')) {
            Schema::create('performance_dept_pic_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('department', 100);
                $table->unsignedBigInteger('previous_user_id')->nullable();
                $table->unsignedBigInteger('new_user_id')->nullable();
                $table->enum('action', ['ASSIGNED', 'CHANGED', 'REMOVED'])->default('ASSIGNED');
                $table->string('notes', 255)->nullable();
                $table->unsignedBigInteger('acted_by')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['department', 'created_at']);
            });
        }

        // 3. Alter performance_goals untuk mendukung KPI Template berbasis Posisi
        if (Schema::hasTable('performance_goals')) {
            DB::statement('ALTER TABLE performance_goals MODIFY COLUMN user_id BIGINT UNSIGNED NULL');

            Schema::table('performance_goals', function (Blueprint $table) {
                if (!Schema::hasColumn('performance_goals', 'scope_type')) {
                    $table->enum('scope_type', ['POSITION', 'INDIVIDUAL'])->default('POSITION')->after('goal_no');
                }
                if (!Schema::hasColumn('performance_goals', 'position_id')) {
                    $table->unsignedBigInteger('position_id')->nullable()->after('department');
                    $table->index(['period_id', 'position_id', 'status'], 'idx_goal_period_position_status');
                }
            });

            // Data lama yang dibuat per individu tetap ditandai INDIVIDUAL agar backward-compatible
            DB::table('performance_goals')
                ->whereNotNull('user_id')
                ->whereNull('position_id')
                ->update(['scope_type' => 'INDIVIDUAL']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('performance_goals')) {
            Schema::table('performance_goals', function (Blueprint $table) {
                if (Schema::hasColumn('performance_goals', 'position_id')) {
                    $table->dropIndex('idx_goal_period_position_status');
                    $table->dropColumn('position_id');
                }
                if (Schema::hasColumn('performance_goals', 'scope_type')) {
                    $table->dropColumn('scope_type');
                }
            });
        }

        Schema::dropIfExists('performance_dept_pic_logs');
        Schema::dropIfExists('performance_dept_pics');
    }
}
