<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveBalanceAndPolicyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Master Aturan Default Jatah Cuti per Posisi / Level / Role
        if (!Schema::hasTable('leave_entitlement_policies')) {
            Schema::create('leave_entitlement_policies', function (Blueprint $table) {
                $table->increments('id');
                $table->string('policy_name', 150);
                $table->string('level_or_role', 50)->nullable(); // Misal: STAFF, SUPERVISOR, MANAGER, VP
                $table->integer('annual_quota')->default(12); // Jatah dasar per tahun
                $table->integer('carry_forward_max')->default(6); // Maksimal sisa cuti tahun lalu yang bisa diakumulasi
                $table->integer('carry_forward_expire_months')->default(6); // Berapa bulan sisa tahun lalu berlaku (misal s/d Juni)
                $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
            });
        }

        // 2. Saldo Cuti Individu Karyawan (Mendukung Recurring, Carry Forward & Masa Reset)
        if (!Schema::hasTable('user_leave_balances')) {
            Schema::create('user_leave_balances', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nik', 50)->index();
                $table->integer('year'); // Tahun periode cuti (e.g. 2026)
                $table->integer('allocated_quota')->default(12); // Jatah tahun berjalan (bisa diedit custom per individu)
                $table->integer('carry_forward_quota')->default(0); // Sisa kuota akumulasi dari tahun lalu
                $table->date('carry_forward_expired_at')->nullable(); // Tanggal batas akhir cuti tahun lalu hangus (reset time)
                $table->integer('used_carry_forward')->default(0); // Jumlah cuti tahun lalu yang sudah dipakai
                $table->integer('used_current_year')->default(0); // Jumlah cuti tahun berjalan yang sudah dipakai
                $table->integer('remaining_total')->default(12); // Sisa total saldo aktif
                $table->text('notes')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();

                $table->unique(['nik', 'year']);
            });
        }

        // 3. Buku Besar Riwayat Penggunaan & Penyesuaian Saldo Cuti
        if (!Schema::hasTable('leave_balance_ledgers')) {
            Schema::create('leave_balance_ledgers', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nik', 50)->index();
                $table->string('reference_no', 100)->nullable(); // Nomor request izin/cuti
                $table->enum('transaction_type', ['ALLOCATION', 'CARRY_FORWARD', 'DEDUCTION', 'EXPIRED_RESET', 'ADJUSTMENT']);
                $table->integer('amount'); // Positif (penambahan) atau Negatif (pemotongan)
                $table->enum('source_balance', ['CARRY_FORWARD', 'CURRENT_YEAR', 'SYSTEM']);
                $table->integer('balance_after');
                $table->text('description')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->timestamp('created_at')->useCurrent();
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
        Schema::dropIfExists('leave_balance_ledgers');
        Schema::dropIfExists('user_leave_balances');
        Schema::dropIfExists('leave_entitlement_policies');
    }
}
