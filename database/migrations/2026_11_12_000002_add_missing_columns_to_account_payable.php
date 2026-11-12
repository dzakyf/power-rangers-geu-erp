<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The legacy KSPS account_payable table pre-dates the BDP create migration and
 * is missing columns that the profit-sharing flow expects (URL transfer proof,
 * opportunity link and a few auxiliary fields). Add them without recreating
 * the shared table.
 */
class AddMissingColumnsToAccountPayable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('account_payable', 'Opportunity_Id')) {
            Schema::table('account_payable', function (Blueprint $table) {
                $table->unsignedBigInteger('Opportunity_Id')->nullable()->index();
            });
        }

        if (!Schema::hasColumn('account_payable', 'Url_Transfer')) {
            Schema::table('account_payable', function (Blueprint $table) {
                $table->text('Url_Transfer')->nullable();
            });
        }

        if (!Schema::hasColumn('account_payable', 'Last_Update')) {
            Schema::table('account_payable', function (Blueprint $table) {
                $table->timestamp('Last_Update')->nullable();
            });
        }

        if (!Schema::hasColumn('account_payable', 'PR_No')) {
            Schema::table('account_payable', function (Blueprint $table) {
                $table->string('PR_No', 60)->nullable();
            });
        }

        if (!Schema::hasColumn('account_payable', 'Receive')) {
            Schema::table('account_payable', function (Blueprint $table) {
                $table->string('Receive', 20)->nullable();
            });
        }

        if (!Schema::hasColumn('account_payable', 'Priority')) {
            Schema::table('account_payable', function (Blueprint $table) {
                $table->string('Priority', 20)->nullable();
            });
        }
    }

    public function down()
    {
        foreach (['Priority', 'Receive', 'PR_No', 'Last_Update', 'Url_Transfer', 'Opportunity_Id'] as $column) {
            if (Schema::hasColumn('account_payable', $column)) {
                Schema::table('account_payable', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
}
