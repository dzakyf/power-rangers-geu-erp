<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_changes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('form_number')->unique()->nullable();
            $table->string('jenis', 20)->default('MUTATION')->index();

            // Identitas karyawan
            $table->string('employee_name');
            $table->string('nik', 50);
            $table->date('join_date')->nullable();

            // Kondisi saat ini
            $table->string('current_position')->nullable();
            $table->string('current_division')->nullable();
            $table->string('current_department')->nullable();
            $table->string('current_grade')->nullable();
            $table->string('current_work_location')->nullable();
            $table->string('current_employment_status')->nullable();
            $table->string('current_marital_dependents', 10)->nullable();
            $table->decimal('current_salary', 15, 2)->default(0);
            $table->text('current_benefit')->nullable();
            $table->text('current_description')->nullable();

            // Usulan
            $table->string('proposed_position')->nullable();
            $table->string('proposed_division')->nullable();
            $table->string('proposed_department')->nullable();
            $table->string('proposed_grade')->nullable();
            $table->string('proposed_work_location')->nullable();
            $table->string('proposed_employment_status')->nullable();
            $table->string('proposed_marital_dependents', 10)->nullable();
            $table->decimal('proposed_salary', 15, 2)->default(0);
            $table->text('proposed_benefit')->nullable();
            $table->text('proposed_description')->nullable();

            $table->date('effective_date')->nullable();

            // Nama penandatangan/approval disimpan sebagai snapshot teks
            $table->string('proposed_by')->nullable();
            $table->string('origin_department_head')->nullable();
            $table->string('destination_department_head')->nullable();
            $table->string('hcga_verifier')->nullable()->default('Arie S. Wirawan');
            $table->string('director_approver')->nullable()->default('Dhimas Arif Wibowo');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('status_changes');
    }
}
