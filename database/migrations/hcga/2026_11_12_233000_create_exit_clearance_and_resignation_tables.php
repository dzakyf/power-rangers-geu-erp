<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExitClearanceAndResignationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Master Checklist Exit Clearance (17 butir standar & dapat diperluas)
        if (!Schema::hasTable('master_exit_clearance_items')) {
            Schema::create('master_exit_clearance_items', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('sort_order')->default(1);
                $table->string('item_name', 255);
                $table->string('category', 100)->default('ASSET_INVENTORY'); // ASSET_INVENTORY, IT_SECURITY, HR_FINANCE, HANDOVER
                $table->string('default_pic_role', 100)->nullable(); // IT, GA, HR, FINANCE, ATASAN
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Transaksi Pengajuan Pengunduran Diri (Employee Resignation)
        if (!Schema::hasTable('employee_resignations')) {
            Schema::create('employee_resignations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('No_Trx', 50)->unique();
                $table->unsignedInteger('User_ID');
                $table->string('Nama', 150);
                $table->string('Jabatan', 100)->nullable();
                $table->string('Divisi', 100)->nullable();
                $table->string('Departemen', 100)->nullable();
                $table->date('Join_Date')->nullable();
                $table->date('Resign_Date'); // Tanggal Pengajuan
                $table->date('Last_Working_Date'); // Tanggal Efektif Terakhir Kerja
                $table->text('Reason'); // Alasan pengunduran diri
                $table->string('Resignation_Letter_File', 255)->nullable(); // Surat Pengunduran Diri
                $table->string('Last_Contract_File', 255)->nullable(); // Kontrak Terakhir (opsional)
                $table->unsignedBigInteger('Approval_Request_ID')->nullable();
                $table->enum('Status', ['DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'REJECTED', 'CANCELLED'])->default('DRAFT');
                $table->text('Rejection_Reason')->nullable();
                $table->unsignedInteger('Approved_By')->nullable();
                $table->timestamp('Approved_At')->nullable();
                $table->unsignedInteger('Created_By')->nullable();
                $table->unsignedInteger('Updated_By')->nullable();
                $table->timestamps();

                $table->index(['User_ID', 'Status']);
                $table->index('Approval_Request_ID');
            });
        }

        // 3. Formulir Exit Clearance & Kuesioner Evaluasi
        if (!Schema::hasTable('exit_clearances')) {
            Schema::create('exit_clearances', function (Blueprint $table) {
                $table->increments('id');
                $table->string('No_Trx', 50)->unique();
                $table->unsignedInteger('Resignation_ID');
                $table->unsignedInteger('User_ID');
                $table->string('Nama', 150);
                $table->string('Jabatan', 100)->nullable();
                $table->string('Divisi', 100)->nullable();
                $table->string('Departemen', 100)->nullable();
                $table->date('Join_Date')->nullable();
                $table->date('Exit_Date')->nullable();

                // Bagian A: Alasan Pengakhiran Hubungan Kerja (Skala 1-5) & Other Benefits
                $table->json('reasons_ratings')->nullable(); 
                $table->text('other_uncovered_reasons')->nullable();
                $table->json('new_company_offers')->nullable(); // Gaji, cuti, jam kerja, bonus, dll

                // Bagian B: Mengenai Perusahaan (Skala 1-5)
                $table->json('company_ratings')->nullable();

                // Bagian C: Komentar / Saran per Divisi
                $table->json('division_feedbacks')->nullable();

                // Bagian D: Saran Untuk Perusahaan
                $table->text('company_suggestions')->nullable();

                // Bagian Form Interview Atasan & Catatan Verifikasi HCGA
                $table->text('exit_interview_notes')->nullable(); // Textarea catatan wawancara atasan
                $table->text('manager_notes')->nullable(); // Catatan tambahan Dept Head / Manager
                $table->unsignedInteger('Manager_Interview_By')->nullable();
                $table->timestamp('Manager_Interview_At')->nullable();

                $table->text('hcga_notes')->nullable(); // Catatan verifikasi HCGA
                $table->unsignedInteger('Hcga_Verified_By')->nullable();
                $table->timestamp('Hcga_Verified_At')->nullable();

                // Deklarasi Karyawan
                $table->boolean('employee_declaration')->default(false); // Komitmen aset & kerahasiaan
                $table->timestamp('Employee_Submitted_At')->nullable();

                // Workflow Approval ID
                $table->unsignedBigInteger('Approval_Request_ID')->nullable();
                $table->enum('Status', [
                    'DRAFT', 
                    'PENDING_INTERVIEW', 
                    'PENDING_HCGA_APPROVAL', 
                    'COMPLETED', 
                    'REJECTED'
                ])->default('DRAFT');

                $table->unsignedInteger('Created_By')->nullable();
                $table->unsignedInteger('Updated_By')->nullable();
                $table->timestamps();

                $table->index(['Resignation_ID', 'User_ID']);
                $table->index('Approval_Request_ID');
            });
        }

        // 4. Checklist Pengembalian Inventaris / Aset Karyawan (Bagian E)
        if (!Schema::hasTable('exit_clearance_checklist_values')) {
            Schema::create('exit_clearance_checklist_values', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('Exit_Clearance_ID');
                $table->unsignedInteger('Item_ID')->nullable();
                $table->string('Item_Name', 255);
                $table->enum('Status', ['SUDAH', 'TIDAK_ADA', 'BELUM'])->default('TIDAK_ADA');
                $table->text('Keterangan')->nullable();
                $table->string('Penanggung_Jawab', 150)->nullable();
                $table->string('Asset_No', 50)->nullable(); // Menghubungkan ke tabel asset jika ada
                $table->timestamps();

                $table->index('Exit_Clearance_ID');
                $table->index('Item_ID');
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
        Schema::dropIfExists('exit_clearance_checklist_values');
        Schema::dropIfExists('exit_clearances');
        Schema::dropIfExists('employee_resignations');
        Schema::dropIfExists('master_exit_clearance_items');
    }
}

