<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThreeLayerApprovalToSalesOrderCnsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sales_order_cns')) {
            return;
        }

        Schema::table('sales_order_cns', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_order_cns', 'gudang_approved_by')) {
                $table->string('gudang_approved_by', 150)->nullable();
            }
            if (!Schema::hasColumn('sales_order_cns', 'gudang_approved_date')) {
                $table->dateTime('gudang_approved_date')->nullable();
            }
            if (!Schema::hasColumn('sales_order_cns', 'buyer_signed_by')) {
                $table->string('buyer_signed_by', 150)->nullable();
            }
            if (!Schema::hasColumn('sales_order_cns', 'buyer_signed_date')) {
                $table->dateTime('buyer_signed_date')->nullable();
            }
            if (!Schema::hasColumn('sales_order_cns', 'buyer_signature_path')) {
                $table->string('buyer_signature_path', 255)->nullable();
            }
            if (!Schema::hasColumn('sales_order_cns', 'surat_jalan_no')) {
                $table->string('surat_jalan_no', 100)->nullable()->index();
            }
            if (!Schema::hasColumn('sales_order_cns', 'surat_jalan_date')) {
                $table->date('surat_jalan_date')->nullable();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('sales_order_cns')) {
            return;
        }

        Schema::table('sales_order_cns', function (Blueprint $table) {
            $columns = [
                'gudang_approved_by',
                'gudang_approved_date',
                'buyer_signed_by',
                'buyer_signed_date',
                'buyer_signature_path',
                'surat_jalan_no',
                'surat_jalan_date',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('sales_order_cns', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
