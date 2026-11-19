<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetMaintenanceScheduleAndFleetTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('asset_maintenance_schedule')) {
            Schema::create('asset_maintenance_schedule', function (Blueprint $table) {
                $table->increments('ID');
                $table->string('Schedule_No', 60)->unique();
                $table->string('Asset_No', 50)->index('idx_ams_asset_no');
                $table->string('Jenis_Perawatan', 40);
                $table->string('Frekuensi', 20);
                $table->date('Next_Schedule_Date');
                $table->date('Last_Executed_Date')->nullable();
                $table->string('PIC_Internal', 80)->nullable();
                $table->tinyInteger('Is_External_Vendor')->default(0);
                $table->string('Supplier_ID', 60)->nullable();
                $table->decimal('Estimasi_Biaya', 15, 2)->default(0);
                $table->string('Status', 30)->default('APPROVAL_HCGA');
                $table->string('Voucher_No', 60)->nullable();
                $table->string('Previous_Doc', 255)->nullable();
                $table->text('Approval_Note')->nullable();
                $table->string('Created_By', 55);
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }

        if (!Schema::hasTable('vehicle_tax_request')) {
            Schema::create('vehicle_tax_request', function (Blueprint $table) {
                $table->increments('ID');
                $table->string('Tax_Request_No', 60)->unique();
                $table->string('Asset_No', 50)->index('idx_vtr_asset_no');
                $table->string('Jenis_Pajak', 30);
                $table->date('Tanggal_Jatuh_Tempo')->nullable();
                $table->decimal('Estimasi_Biaya', 15, 2)->default(0);
                $table->string('Voucher_No', 60)->nullable();
                $table->string('Status', 30)->default('APPROVAL_HCGA');
                $table->date('Tanggal_Bayar')->nullable();
                $table->string('Bukti_Bayar', 255)->nullable();
                $table->text('Approval_Note')->nullable();
                $table->string('Created_By', 55);
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }

        if (Schema::hasTable('asset_list')) {
            Schema::table('asset_list', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_list', 'Vehicle_Usage_Status')) {
                    $table->string('Vehicle_Usage_Status', 20)->nullable()->after('Status');
                }
                if (!Schema::hasColumn('asset_list', 'Nomor_STNK')) {
                    $table->string('Nomor_STNK', 60)->nullable()->after('BPKB_No');
                }
                if (!Schema::hasColumn('asset_list', 'Tanggal_Berlaku_Pajak')) {
                    $table->date('Tanggal_Berlaku_Pajak')->nullable()->after('STNK_Tahunan');
                }
                if (!Schema::hasColumn('asset_list', 'Fleet_Approval_Status')) {
                    $table->string('Fleet_Approval_Status', 30)->default('APPROVED')->after('Vehicle_Usage_Status');
                }
            });
        }

        if (Schema::hasTable('vehicle_logbook')) {
            Schema::table('vehicle_logbook', function (Blueprint $table) {
                if (!Schema::hasColumn('vehicle_logbook', 'Approval_Status')) {
                    $table->string('Approval_Status', 20)->default('PENDING');
                }
                if (!Schema::hasColumn('vehicle_logbook', 'Approved_By')) {
                    $table->string('Approved_By', 55)->nullable();
                }
                if (!Schema::hasColumn('vehicle_logbook', 'Approved_At')) {
                    $table->dateTime('Approved_At')->nullable();
                }
                if (!Schema::hasColumn('vehicle_logbook', 'Approval_Note')) {
                    $table->string('Approval_Note', 255)->nullable();
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('asset_maintenance_schedule');
        Schema::dropIfExists('vehicle_tax_request');
    }
}
