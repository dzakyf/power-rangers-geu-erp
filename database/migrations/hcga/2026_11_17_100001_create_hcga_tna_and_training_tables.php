<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateHcgaTnaAndTrainingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. TNA Requests (Analisa & Pengajuan Kebutuhan Pelatihan)
        if (!Schema::hasTable('hcga_tna_requests')) {
            Schema::create('hcga_tna_requests', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('tna_no', 100)->unique();
                $table->string('department', 150);
                $table->string('fiscal_year', 10);
                $table->string('title', 255);
                $table->text('background')->nullable();
                $table->string('requested_by', 150);
                $table->unsignedBigInteger('requester_user_id')->nullable();
                $table->string('status', 50)->default('DRAFT'); // DRAFT, WAITING_APPROVAL, APPROVED, REJECTED, CLOSED
                $table->unsignedBigInteger('approval_request_id')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->string('approved_by', 150)->nullable();
                $table->text('approval_notes')->nullable();
                $table->timestamps();

                $table->index(['department', 'fiscal_year']);
                $table->index('status');
                $table->index('approval_request_id');
            });
        }

        // 2. Module & Program Training
        if (!Schema::hasTable('hcga_training_programs')) {
            Schema::create('hcga_training_programs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('program_code', 100)->unique();
                $table->string('program_name', 255);
                $table->unsignedInteger('category_id')->nullable();
                $table->string('training_type', 50)->default('INTERNAL'); // INTERNAL, EXTERNAL, IN_HOUSE
                $table->string('training_method', 50)->default('OFFLINE'); // OFFLINE, ONLINE, HYBRID
                $table->text('description')->nullable();
                $table->text('objective')->nullable();
                $table->text('competency')->nullable();
                $table->decimal('default_passing_grade', 5, 2)->default(75.00);
                $table->string('module_file_path', 255)->nullable();
                $table->string('status', 30)->default('ACTIVE'); // ACTIVE, INACTIVE
                $table->string('created_by', 150);
                $table->string('updated_by', 150)->nullable();
                $table->timestamps();

                $table->index('status');
                $table->index('category_id');
            });
        }

        // 3. TNA Items (Daftar Karyawan & Gap Kompetensi per TNA)
        if (!Schema::hasTable('hcga_tna_items')) {
            Schema::create('hcga_tna_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tna_id');
                $table->string('employee_username', 150);
                $table->string('employee_name', 255);
                $table->string('position_name', 150)->nullable();
                $table->string('competency_gap', 255);
                $table->string('recommended_training', 255);
                $table->unsignedBigInteger('program_id')->nullable();
                $table->string('priority', 30)->default('MEDIUM'); // HIGH, MEDIUM, LOW
                $table->string('target_quarter', 20)->nullable(); // Q1, Q2, Q3, Q4
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('tna_id')->references('id')->on('hcga_tna_requests')->onDelete('cascade');
                $table->index('employee_username');
                $table->index('program_id');
            });
        }

        // 4. Perluasan tabel legacy training_hcga (Jadwal & Request Training)
        if (Schema::hasTable('training_hcga')) {
            DB::statement("ALTER TABLE `training_hcga`
                MODIFY COLUMN `Training_Category` VARCHAR(150) NULL,
                MODIFY COLUMN `Training_Type` VARCHAR(150) NULL,
                MODIFY COLUMN `Training_Method` VARCHAR(150) NULL,
                MODIFY COLUMN `Training_Description` TEXT NULL,
                MODIFY COLUMN `Training_Objective` TEXT NULL,
                MODIFY COLUMN `Training_Competence` TEXT NULL,
                MODIFY COLUMN `Training_Location` VARCHAR(255) NULL,
                MODIFY COLUMN `Location` VARCHAR(150) NULL,
                MODIFY COLUMN `Total_Cost` DOUBLE(30,2) NOT NULL DEFAULT 0.00,
                MODIFY COLUMN `Currency` VARCHAR(150) NOT NULL DEFAULT 'IDR',
                MODIFY COLUMN `Status` VARCHAR(150) NOT NULL DEFAULT 'DRAFT',
                MODIFY COLUMN `Updated_By` VARCHAR(150) NULL,
                MODIFY COLUMN `Updated_Date` DATETIME NULL,
                MODIFY COLUMN `Approved_Date` DATETIME NULL,
                MODIFY COLUMN `Approved_By` VARCHAR(150) NULL");

            Schema::table('training_hcga', function (Blueprint $table) {
                if (!Schema::hasColumn('training_hcga', 'tna_id')) {
                    $table->unsignedBigInteger('tna_id')->nullable()->after('Task_No');
                }
                if (!Schema::hasColumn('training_hcga', 'program_id')) {
                    $table->unsignedBigInteger('program_id')->nullable()->after('tna_id');
                }
                if (!Schema::hasColumn('training_hcga', 'trainer_id')) {
                    $table->unsignedInteger('trainer_id')->nullable()->after('program_id');
                }
                if (!Schema::hasColumn('training_hcga', 'trainer_name')) {
                    $table->string('trainer_name', 150)->nullable()->after('trainer_id');
                }
                if (!Schema::hasColumn('training_hcga', 'passing_grade')) {
                    $table->decimal('passing_grade', 5, 2)->default(75.00)->after('End_Date');
                }
                if (!Schema::hasColumn('training_hcga', 'batch_type')) {
                    $table->string('batch_type', 30)->default('REGULAR')->after('passing_grade'); // REGULAR, REFRESHMENT
                }
                if (!Schema::hasColumn('training_hcga', 'parent_training_id')) {
                    $table->integer('parent_training_id')->nullable()->after('batch_type');
                }
                if (!Schema::hasColumn('training_hcga', 'approval_request_id')) {
                    $table->unsignedBigInteger('approval_request_id')->nullable()->after('Status');
                }
            });
        }

        // 5. Kurikulum / Sesi Materi per Jadwal Training
        if (!Schema::hasTable('hcga_training_curriculums')) {
            Schema::create('hcga_training_curriculums', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('training_id');
                $table->integer('session_order')->default(1);
                $table->string('topic_title', 255);
                $table->string('speaker_name', 150)->nullable();
                $table->integer('duration_minutes')->default(60);
                $table->string('learning_method', 100)->nullable();
                $table->string('material_file_path', 255)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('training_id');
            });
        }

        // 6. Peserta & Penilaian Hasil Training Karyawan
        if (!Schema::hasTable('hcga_training_participants')) {
            Schema::create('hcga_training_participants', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('training_id');
                $table->unsignedBigInteger('tna_item_id')->nullable();
                $table->string('employee_username', 150);
                $table->string('employee_name', 255);
                $table->string('department', 150)->nullable();
                $table->string('attendance_status', 30)->default('REGISTERED'); // REGISTERED, ATTENDED, ABSENT
                $table->decimal('pre_test_score', 5, 2)->nullable();
                $table->decimal('post_test_score', 5, 2)->nullable();
                $table->decimal('practice_score', 5, 2)->nullable();
                $table->decimal('final_score', 5, 2)->nullable();
                $table->boolean('is_passed')->nullable();
                $table->text('evaluation_notes')->nullable();
                $table->string('evaluated_by', 150)->nullable();
                $table->dateTime('evaluated_at')->nullable();
                $table->dateTime('feedback_submitted_at')->nullable();
                $table->timestamps();

                $table->unique(['training_id', 'employee_username'], 'uniq_training_participant');
                $table->index('employee_username');
                $table->index('is_passed');
            });
        }

        // 7. Form Evaluasi Pemateri & Materi oleh Peserta
        if (!Schema::hasTable('hcga_training_feedbacks')) {
            Schema::create('hcga_training_feedbacks', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('training_id');
                $table->unsignedBigInteger('participant_id');
                $table->string('feedback_type', 30); // TRAINER, MATERIAL
                $table->string('aspect_name', 150);
                $table->unsignedTinyInteger('rating'); // 1-5
                $table->text('comments')->nullable();
                $table->timestamps();

                $table->foreign('participant_id')->references('id')->on('hcga_training_participants')->onDelete('cascade');
                $table->index(['training_id', 'feedback_type']);
            });
        }

        // 8. Sertifikat Digital (untuk peserta Lulus Evaluasi)
        if (!Schema::hasTable('hcga_training_certificates')) {
            Schema::create('hcga_training_certificates', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('training_id');
                $table->unsignedBigInteger('participant_id');
                $table->string('certificate_no', 120)->unique();
                $table->string('verification_token', 80)->unique();
                $table->date('issued_date');
                $table->string('file_path', 255)->nullable();
                $table->string('issued_by', 150);
                $table->timestamps();

                $table->unique('participant_id');
                $table->index('training_id');
            });
        }

        // 9. Coaching & Training Refreshment (untuk peserta Tidak Lulus Evaluasi)
        if (!Schema::hasTable('hcga_training_coachings')) {
            Schema::create('hcga_training_coachings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('training_id');
                $table->unsignedBigInteger('participant_id');
                $table->string('mentor_username', 150);
                $table->string('mentor_name', 255)->nullable();
                $table->date('coaching_date');
                $table->text('notes');
                $table->text('action_plan')->nullable();
                $table->string('follow_up_type', 30)->default('COACHING_ONLY'); // COACHING_ONLY, REFRESHMENT
                $table->integer('refreshment_training_id')->nullable();
                $table->string('status', 30)->default('OPEN'); // OPEN, IN_PROGRESS, COMPLETED
                $table->string('created_by', 150);
                $table->timestamps();

                $table->foreign('participant_id')->references('id')->on('hcga_training_participants')->onDelete('cascade');
                $table->index(['training_id', 'status']);
                $table->index('refreshment_training_id');
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
        Schema::dropIfExists('hcga_training_coachings');
        Schema::dropIfExists('hcga_training_certificates');
        Schema::dropIfExists('hcga_training_feedbacks');
        Schema::dropIfExists('hcga_training_participants');
        Schema::dropIfExists('hcga_training_curriculums');
        Schema::dropIfExists('hcga_tna_items');
        Schema::dropIfExists('hcga_training_programs');
        Schema::dropIfExists('hcga_tna_requests');

        if (Schema::hasTable('training_hcga')) {
            Schema::table('training_hcga', function (Blueprint $table) {
                $cols = [
                    'tna_id', 'program_id', 'trainer_id', 'trainer_name',
                    'passing_grade', 'batch_type', 'parent_training_id', 'approval_request_id'
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('training_hcga', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
