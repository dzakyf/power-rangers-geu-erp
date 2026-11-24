<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AddApprovalToCnsCustomerKycTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cns_customer_kyc')) {
            Schema::table('cns_customer_kyc', function (Blueprint $table) {
                if (!Schema::hasColumn('cns_customer_kyc', 'approval_status')) {
                    $table->string('approval_status', 20)->default('PENDING')->index()->after('location');
                }
                if (!Schema::hasColumn('cns_customer_kyc', 'approved_by')) {
                    $table->string('approved_by', 100)->nullable()->after('approval_status');
                }
                if (!Schema::hasColumn('cns_customer_kyc', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('cns_customer_kyc', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('approved_at');
                }
            });
        }

        if (Schema::hasTable('permissions')) {
            Permission::firstOrCreate([
                'name'       => 'cns.customer_compliance_kyc.approve',
                'guard_name' => 'web',
            ]);

            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    public function down()
    {
        if (Schema::hasTable('cns_customer_kyc')) {
            Schema::table('cns_customer_kyc', function (Blueprint $table) {
                $cols = array_filter(
                    ['approval_status', 'approved_by', 'approved_at', 'rejection_reason'],
                    function ($col) {
                        return Schema::hasColumn('cns_customer_kyc', $col);
                    }
                );
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('permissions')) {
            Permission::where('name', 'cns.customer_compliance_kyc.approve')
                ->where('guard_name', 'web')
                ->delete();

            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }
}
