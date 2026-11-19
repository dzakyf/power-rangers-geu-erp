<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssetTicketingLifecycleColumns extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ga_ticket')) {
            Schema::table('ga_ticket', function (Blueprint $table) {
                if (!Schema::hasColumn('ga_ticket', 'Asset_No')) {
                    $table->string('Asset_No', 50)->nullable()->index('idx_ga_ticket_asset_no')->after('Location');
                }
                if (!Schema::hasColumn('ga_ticket', 'Damage_Severity')) {
                    $table->string('Damage_Severity', 30)->nullable()->after('Asset_No');
                }
            });
        }

        if (Schema::hasTable('purchase_request')) {
            Schema::table('purchase_request', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_request', 'Asset_No')) {
                    $table->string('Asset_No', 50)->nullable()->index('idx_pr_asset_no')->after('Category');
                }
                if (!Schema::hasColumn('purchase_request', 'Asset_Request_Type')) {
                    $table->string('Asset_Request_Type', 30)->nullable()->after('Asset_No');
                }
            });
        }

        if (Schema::hasTable('asset_fixing')) {
            Schema::table('asset_fixing', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_fixing', 'Asset_No')) {
                    $table->string('Asset_No', 50)->nullable()->index('idx_asset_fixing_asset_no')->after('Product_Code');
                }
                if (!Schema::hasColumn('asset_fixing', 'Approval_Request_ID')) {
                    $table->unsignedBigInteger('Approval_Request_ID')->nullable()->index('idx_asset_fixing_appr_req')->after('Status');
                }
                if (!Schema::hasColumn('asset_fixing', 'Ticket_No')) {
                    $table->string('Ticket_No', 50)->nullable()->index('idx_asset_fixing_ticket_no')->after('Approval_Request_ID');
                }
                if (!Schema::hasColumn('asset_fixing', 'Finance_Voucher_No')) {
                    $table->string('Finance_Voucher_No', 50)->nullable()->after('Ticket_No');
                }
            });
        }

        if (Schema::hasTable('moving_asset')) {
            Schema::table('moving_asset', function (Blueprint $table) {
                if (!Schema::hasColumn('moving_asset', 'Approval_Request_ID')) {
                    $table->unsignedBigInteger('Approval_Request_ID')->nullable()->index('idx_asset_moving_appr_req')->after('Status');
                }
            });
        }

        if (Schema::hasTable('asset_disposal')) {
            Schema::table('asset_disposal', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_disposal', 'Asset_No')) {
                    $table->string('Asset_No', 50)->nullable()->index('idx_asset_disposal_asset_no')->after('Asset_Name');
                }
                if (!Schema::hasColumn('asset_disposal', 'Approval_Request_ID')) {
                    $table->unsignedBigInteger('Approval_Request_ID')->nullable()->index('idx_asset_disposal_appr_req')->after('Status');
                }
                if (!Schema::hasColumn('asset_disposal', 'Book_Value')) {
                    $table->decimal('Book_Value', 18, 2)->nullable()->after('Approval_Request_ID');
                }
                if (!Schema::hasColumn('asset_disposal', 'Finance_Verified_By')) {
                    $table->string('Finance_Verified_By', 100)->nullable()->after('Book_Value');
                }
                if (!Schema::hasColumn('asset_disposal', 'Finance_Verified_At')) {
                    $table->dateTime('Finance_Verified_At')->nullable()->after('Finance_Verified_By');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('ga_ticket')) {
            Schema::table('ga_ticket', function (Blueprint $table) {
                if (Schema::hasColumn('ga_ticket', 'Damage_Severity')) {
                    $table->dropColumn('Damage_Severity');
                }
                if (Schema::hasColumn('ga_ticket', 'Asset_No')) {
                    $table->dropIndex('idx_ga_ticket_asset_no');
                    $table->dropColumn('Asset_No');
                }
            });
        }

        if (Schema::hasTable('purchase_request')) {
            Schema::table('purchase_request', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_request', 'Asset_Request_Type')) {
                    $table->dropColumn('Asset_Request_Type');
                }
                if (Schema::hasColumn('purchase_request', 'Asset_No')) {
                    $table->dropIndex('idx_pr_asset_no');
                    $table->dropColumn('Asset_No');
                }
            });
        }

        if (Schema::hasTable('asset_fixing')) {
            Schema::table('asset_fixing', function (Blueprint $table) {
                if (Schema::hasColumn('asset_fixing', 'Finance_Voucher_No')) {
                    $table->dropColumn('Finance_Voucher_No');
                }
                if (Schema::hasColumn('asset_fixing', 'Ticket_No')) {
                    $table->dropIndex('idx_asset_fixing_ticket_no');
                    $table->dropColumn('Ticket_No');
                }
                if (Schema::hasColumn('asset_fixing', 'Approval_Request_ID')) {
                    $table->dropIndex('idx_asset_fixing_appr_req');
                    $table->dropColumn('Approval_Request_ID');
                }
                if (Schema::hasColumn('asset_fixing', 'Asset_No')) {
                    $table->dropIndex('idx_asset_fixing_asset_no');
                    $table->dropColumn('Asset_No');
                }
            });
        }

        if (Schema::hasTable('moving_asset')) {
            Schema::table('moving_asset', function (Blueprint $table) {
                if (Schema::hasColumn('moving_asset', 'Approval_Request_ID')) {
                    $table->dropIndex('idx_asset_moving_appr_req');
                    $table->dropColumn('Approval_Request_ID');
                }
            });
        }

        if (Schema::hasTable('asset_disposal')) {
            Schema::table('asset_disposal', function (Blueprint $table) {
                foreach (['Finance_Verified_At', 'Finance_Verified_By', 'Book_Value'] as $col) {
                    if (Schema::hasColumn('asset_disposal', $col)) {
                        $table->dropColumn($col);
                    }
                }
                if (Schema::hasColumn('asset_disposal', 'Approval_Request_ID')) {
                    $table->dropIndex('idx_asset_disposal_appr_req');
                    $table->dropColumn('Approval_Request_ID');
                }
                if (Schema::hasColumn('asset_disposal', 'Asset_No')) {
                    $table->dropIndex('idx_asset_disposal_asset_no');
                    $table->dropColumn('Asset_No');
                }
            });
        }
    }
}
