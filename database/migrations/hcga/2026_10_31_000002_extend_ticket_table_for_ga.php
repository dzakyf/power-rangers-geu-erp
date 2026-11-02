<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendTicketTableForGa extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ticket')) {
            return;
        }

        if (!Schema::hasColumn('ticket', 'Module')) {
            Schema::table('ticket', function (Blueprint $table) {
                $table->string('Module', 20)->default('MITRA')->index();
            });
        }

        if (!Schema::hasColumn('ticket', 'Location')) {
            Schema::table('ticket', function (Blueprint $table) {
                $table->string('Location', 50)->nullable()->index();
            });
        }

        if (!Schema::hasColumn('ticket', 'Company_ID')) {
            Schema::table('ticket', function (Blueprint $table) {
                $table->string('Company_ID', 50)->nullable()->index();
            });
        }

        if (!Schema::hasColumn('ticket', 'Source')) {
            Schema::table('ticket', function (Blueprint $table) {
                $table->string('Source', 20)->nullable();
            });
        }

        if (!Schema::hasColumn('ticket', 'Priority')) {
            Schema::table('ticket', function (Blueprint $table) {
                $table->string('Priority', 20)->nullable()->index();
            });
        }

        if (!Schema::hasColumn('ticket', 'Reporter')) {
            Schema::table('ticket', function (Blueprint $table) {
                $table->string('Reporter', 150)->nullable();
            });
        }

        if (!Schema::hasColumn('ticket', 'Department')) {
            Schema::table('ticket', function (Blueprint $table) {
                $table->string('Department', 100)->nullable();
            });
        }

        // Index for GA lists. Guarded because MySQL has no "add index if not exists".
        try {
            Schema::table('ticket', function (Blueprint $table) {
                $table->index(['Module', 'Location', 'Company_ID', 'Ticket_Status'], 'ticket_ga_scope_status_index');
            });
        } catch (\Throwable $e) {
            // Index already exists on an environment where the migration was re-run.
        }
    }

    public function down()
    {
        if (!Schema::hasTable('ticket')) {
            return;
        }

        try {
            Schema::table('ticket', function (Blueprint $table) {
                $table->dropIndex('ticket_ga_scope_status_index');
            });
        } catch (\Throwable $e) {
            // Ignore missing index.
        }

        foreach (['Module', 'Location', 'Company_ID', 'Source', 'Priority', 'Reporter', 'Department'] as $column) {
            if (Schema::hasColumn('ticket', $column)) {
                Schema::table('ticket', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
}
