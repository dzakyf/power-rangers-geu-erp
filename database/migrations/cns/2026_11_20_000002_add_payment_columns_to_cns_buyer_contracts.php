<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentColumnsToCnsBuyerContracts extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cns_buyer_contract_bookings')) {
            Schema::table('cns_buyer_contract_bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('cns_buyer_contract_bookings', 'Finance_Payment_Verified')) {
                    $table->boolean('Finance_Payment_Verified')->default(false);
                }
                if (!Schema::hasColumn('cns_buyer_contract_bookings', 'Finance_Payment_Ref')) {
                    $table->string('Finance_Payment_Ref', 100)->nullable();
                }
                if (!Schema::hasColumn('cns_buyer_contract_bookings', 'Finance_Payment_Date')) {
                    $table->dateTime('Finance_Payment_Date')->nullable();
                }
                if (!Schema::hasColumn('cns_buyer_contract_bookings', 'Finance_Payment_By')) {
                    $table->string('Finance_Payment_By', 100)->nullable();
                }
                if (!Schema::hasColumn('cns_buyer_contract_bookings', 'Payment_Approved')) {
                    $table->boolean('Payment_Approved')->default(false);
                }
                if (!Schema::hasColumn('cns_buyer_contract_bookings', 'Payment_Approved_By')) {
                    $table->string('Payment_Approved_By', 100)->nullable();
                }
                if (!Schema::hasColumn('cns_buyer_contract_bookings', 'Payment_Approved_At')) {
                    $table->dateTime('Payment_Approved_At')->nullable();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('cns_buyer_contract_bookings')) {
            Schema::table('cns_buyer_contract_bookings', function (Blueprint $table) {
                $cols = array_filter([
                    'Finance_Payment_Verified',
                    'Finance_Payment_Ref',
                    'Finance_Payment_Date',
                    'Finance_Payment_By',
                    'Payment_Approved',
                    'Payment_Approved_By',
                    'Payment_Approved_At',
                ], function ($col) {
                    return Schema::hasColumn('cns_buyer_contract_bookings', $col);
                });

                if (!empty($cols)) {
                    $table->dropColumn(array_values($cols));
                }
            });
        }
    }
}
