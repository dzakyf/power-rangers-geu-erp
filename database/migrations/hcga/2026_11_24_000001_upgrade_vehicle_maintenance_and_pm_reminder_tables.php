<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpgradeVehicleMaintenanceAndPmReminderTables extends Migration
{
    public function up()
    {
        if (Schema::hasTable('vehicle_maintenance')) {
            Schema::table('vehicle_maintenance', function (Blueprint $table) {
                if (!Schema::hasColumn('vehicle_maintenance', 'Approval_Request_ID')) {
                    $table->unsignedBigInteger('Approval_Request_ID')->nullable()->after('Status');
                    $table->index('Approval_Request_ID', 'idx_vm_approval_req_id');
                }
                if (!Schema::hasColumn('vehicle_maintenance', 'Nama_Pengaju')) {
                    $table->string('Nama_Pengaju', 120)->nullable()->after('User_Trx');
                }
                if (!Schema::hasColumn('vehicle_maintenance', 'Jabatan')) {
                    $table->string('Jabatan', 120)->nullable()->after('Nama_Pengaju');
                }
                if (!Schema::hasColumn('vehicle_maintenance', 'Tanggal_Pengajuan')) {
                    $table->date('Tanggal_Pengajuan')->nullable()->after('Jabatan');
                }
                if (!Schema::hasColumn('vehicle_maintenance', 'Nomor_Formulir')) {
                    $table->string('Nomor_Formulir', 80)->nullable()->after('Tanggal_Pengajuan');
                }
                if (!Schema::hasColumn('vehicle_maintenance', 'Biaya_Aktual')) {
                    $table->decimal('Biaya_Aktual', 15, 2)->nullable()->after('Estimasi_Biaya');
                }
                if (!Schema::hasColumn('vehicle_maintenance', 'Tanggal_Mulai_Service')) {
                    $table->date('Tanggal_Mulai_Service')->nullable()->after('Biaya_Aktual');
                }
                if (!Schema::hasColumn('vehicle_maintenance', 'Tanggal_Selesai_Service')) {
                    $table->date('Tanggal_Selesai_Service')->nullable()->after('Tanggal_Mulai_Service');
                }
                if (!Schema::hasColumn('vehicle_maintenance', 'Kilometer_Selesai')) {
                    $table->unsignedInteger('Kilometer_Selesai')->nullable()->after('Kilometer_Saat_Ini');
                }
                if (!Schema::hasColumn('vehicle_maintenance', 'Bukti_Nota_Path')) {
                    $table->string('Bukti_Nota_Path', 255)->nullable()->after('Tanggal_Selesai_Service');
                }
                if (!Schema::hasColumn('vehicle_maintenance', 'Catatan_Penyelesaian')) {
                    $table->text('Catatan_Penyelesaian')->nullable()->after('Bukti_Nota_Path');
                }
                if (!Schema::hasColumn('vehicle_maintenance', 'Started_By')) {
                    $table->string('Started_By', 100)->nullable()->after('Catatan_Penyelesaian');
                }
                if (!Schema::hasColumn('vehicle_maintenance', 'Completed_By')) {
                    $table->string('Completed_By', 100)->nullable()->after('Started_By');
                }
            });
        }

        if (Schema::hasTable('asset_list')) {
            Schema::table('asset_list', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_list', 'Interval_KM_PM')) {
                    $table->integer('Interval_KM_PM')->nullable()->after('Interval_PM');
                }
            });
        }

        if (!Schema::hasTable('vehicle_pm_reminder_log')) {
            Schema::create('vehicle_pm_reminder_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('Asset_No', 60)->index();
                $table->string('Trigger_Type', 15);
                $table->string('Threshold_Key', 30);
                $table->string('Due_Reference', 50);
                $table->string('Channel', 15);
                $table->string('Recipient', 150)->nullable();
                $table->timestamp('Sent_At')->nullable();
                $table->timestamps();

                $table->index(
                    ['Asset_No', 'Trigger_Type', 'Threshold_Key', 'Due_Reference', 'Channel'],
                    'idx_vpm_rem_idempotent'
                );
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('vehicle_pm_reminder_log')) {
            Schema::dropIfExists('vehicle_pm_reminder_log');
        }

        if (Schema::hasTable('asset_list') && Schema::hasColumn('asset_list', 'Interval_KM_PM')) {
            Schema::table('asset_list', function (Blueprint $table) {
                $table->dropColumn('Interval_KM_PM');
            });
        }

        if (Schema::hasTable('vehicle_maintenance')) {
            $cols = [
                'Approval_Request_ID',
                'Nama_Pengaju',
                'Jabatan',
                'Tanggal_Pengajuan',
                'Nomor_Formulir',
                'Biaya_Aktual',
                'Tanggal_Mulai_Service',
                'Tanggal_Selesai_Service',
                'Kilometer_Selesai',
                'Bukti_Nota_Path',
                'Catatan_Penyelesaian',
                'Started_By',
                'Completed_By',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('vehicle_maintenance', $col)) {
                    Schema::table('vehicle_maintenance', function (Blueprint $table) use ($col) {
                        $table->dropColumn($col);
                    });
                }
            }
        }
    }
}
