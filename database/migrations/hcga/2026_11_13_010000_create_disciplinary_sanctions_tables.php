<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisciplinarySanctionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Master Policy Sanksi Disiplin
        if (!Schema::hasTable('disciplinary_sanction_policies')) {
            Schema::create('disciplinary_sanction_policies', function (Blueprint $table) {
                $table->increments('id');
                $table->string('Violation_Category', 50)->comment('RINGAN, SEDANG, BERAT');
                $table->string('Sanction_Level', 50)->comment('LISAN, SP1, SP2, SP3');
                $table->unsignedInteger('Default_Duration_Months')->default(6);
                $table->text('Description')->nullable();
                $table->boolean('Requires_Meeting')->default(false);
                $table->string('Status', 20)->default('ACTIVE');
                $table->timestamps();
            });
        }

        // 2. Transaksi Utama Surat Peringatan (SP / Sanksi Disiplin)
        if (!Schema::hasTable('disciplinary_sanctions')) {
            Schema::create('disciplinary_sanctions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('No_Trx', 50)->unique()->comment('SP/YYYY/MM/XXXX');
                $table->string('Doc_ID', 50)->default('FR/HCGA/10-00');
                $table->unsignedInteger('User_ID')->comment('Karyawan yang melanggar');
                $table->string('Nama', 150);
                $table->string('NIK', 50)->nullable();
                $table->string('Jabatan', 100)->nullable();
                $table->string('Divisi', 100)->nullable();
                $table->string('Departemen', 100)->nullable();
                $table->unsignedInteger('Reporter_User_ID')->comment('Atasan / Pihak yang melaporkan');
                $table->string('Reporter_Nama', 150);
                $table->string('Reporter_Jabatan', 100)->nullable();
                $table->text('Violation_Points')->comment('Uraian poin pelanggaran 1, 2, 3');
                $table->string('Evidence_Basis', 100)->comment('Laporan, Data, Kesaksian');
                $table->text('Evidence_Description')->nullable();
                $table->string('Violation_Severity', 50)->default('RINGAN')->comment('RINGAN, SEDANG, BERAT');
                $table->string('Sanction_Level', 50)->default('SP1')->comment('LISAN, SP1, SP2, SP3');
                $table->unsignedInteger('Duration_Months')->default(6);
                $table->date('Valid_From')->nullable();
                $table->date('Valid_Until')->nullable();
                $table->string('Meeting_Type', 50)->nullable()->comment('OFFLINE, ZOOM, NONE');
                $table->dateTime('Meeting_Date')->nullable();
                $table->string('Meeting_Location', 255)->nullable()->comment('Ruang rapat atau tautan Zoom');
                $table->text('Employee_Commitment')->nullable()->comment('Komitmen perbaikan dari karyawan');
                $table->dateTime('Employee_Signed_At')->nullable();
                $table->unsignedInteger('HCGA_Verifier_ID')->nullable();
                $table->text('HCGA_Verifier_Notes')->nullable();
                $table->dateTime('HCGA_Approved_At')->nullable();
                $table->unsignedInteger('Approval_Request_ID')->nullable();
                $table->string('Status', 50)->default('DRAFT')->comment('DRAFT, PENDING_HCGA, WAITING_EMPLOYEE, ACTIVE, EXPIRED, REJECTED, CANCELLED');
                $table->unsignedInteger('Created_By');
                $table->timestamps();

                $table->index('User_ID');
                $table->index('Status');
                $table->index(['Valid_From', 'Valid_Until']);
            });
        }

        // 3. Berkas Digital & Arsip SP (PDF Resmi, Bukti, BA Klarifikasi)
        if (!Schema::hasTable('disciplinary_sanction_documents')) {
            Schema::create('disciplinary_sanction_documents', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('Sanction_ID');
                $table->string('Document_Type', 50)->comment('OFFICIAL_SP_PDF, EVIDENCE_FILE, MEETING_MINUTES_SCAN, EMPLOYEE_SIGNED_SCAN');
                $table->string('File_Name', 255);
                $table->string('File_Path', 255);
                $table->unsignedInteger('File_Size')->nullable();
                $table->string('Sha256_Checksum', 64)->nullable();
                $table->unsignedInteger('Uploaded_By');
                $table->timestamps();

                $table->index('Sanction_ID');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disciplinary_sanction_documents');
        Schema::dropIfExists('disciplinary_sanctions');
        Schema::dropIfExists('disciplinary_sanction_policies');
    }
}

