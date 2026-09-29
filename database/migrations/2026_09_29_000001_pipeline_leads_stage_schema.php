<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Model A: `Status` becomes the single ordered pipeline stage.
 * - master_leads_status gains ordering + terminal/cancel flags and the full stage list.
 * - master_leads_reason is extended with the cancel reasons.
 * - master_leads_qualification drops `Unidentified` (now derived).
 * - opportunity gains Cancel_Stage.
 */
class PipelineLeadsStageSchema extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('master_leads_status', 'urutan')) {
            Schema::table('master_leads_status', function (Blueprint $table) {
                $table->integer('urutan')->nullable()->after('status');
                $table->tinyInteger('is_terminal')->default(0)->after('urutan');
                $table->tinyInteger('is_cancel')->default(0)->after('is_terminal');
            });
        }

        $stages = [
            ['Fresh', 1, 0, 0],
            ['FU 1', 2, 0, 0],
            ['FU 2', 3, 0, 0],
            ['FU 3', 4, 0, 0],
            ['Qualified', 5, 0, 0],
            ['Meeting 1', 6, 0, 0],
            ['Meeting 2', 7, 0, 0],
            ['Warm', 8, 0, 0],
            ['Negotiation', 9, 0, 0],
            ['Deal', 10, 1, 0],
            ['Cancel', 11, 1, 1],
        ];

        foreach ($stages as $s) {
            DB::table('master_leads_status')->updateOrInsert(
                ['status' => $s[0]],
                ['urutan' => $s[1], 'is_terminal' => $s[2], 'is_cancel' => $s[3]]
            );
        }

        DB::table('master_leads_status')->where('status', 'Cold')->delete();

        foreach (['No Response', 'Unqualified', 'Other'] as $reason) {
            DB::table('master_leads_reason')->updateOrInsert(['status' => $reason], []);
        }

        DB::table('master_leads_qualification')->where('status', 'Unidentified')->delete();

        if (!Schema::hasColumn('opportunity', 'Cancel_Stage')) {
            Schema::table('opportunity', function (Blueprint $table) {
                $table->string('Cancel_Stage', 50)->nullable()->after('Status');
            });
        }
    }

    public function down()
    {
        DB::table('master_leads_status')->updateOrInsert(['status' => 'Cold'], ['urutan' => 12, 'is_terminal' => 1, 'is_cancel' => 0]);
        DB::table('master_leads_status')->whereIn('status', ['Fresh', 'FU 1', 'FU 2', 'FU 3', 'Qualified', 'Meeting 1', 'Meeting 2', 'Warm'])->delete();
        DB::table('master_leads_reason')->whereIn('status', ['No Response', 'Unqualified', 'Other'])->delete();
        DB::table('master_leads_qualification')->updateOrInsert(['status' => 'Unidentified'], []);

        if (Schema::hasColumn('opportunity', 'Cancel_Stage')) {
            Schema::table('opportunity', function (Blueprint $table) {
                $table->dropColumn('Cancel_Stage');
            });
        }
        if (Schema::hasColumn('master_leads_status', 'urutan')) {
            Schema::table('master_leads_status', function (Blueprint $table) {
                $table->dropColumn(['urutan', 'is_terminal', 'is_cancel']);
            });
        }
    }
}
