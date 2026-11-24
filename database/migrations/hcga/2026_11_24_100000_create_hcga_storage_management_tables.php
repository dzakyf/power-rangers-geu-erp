<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHcgaStorageManagementTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('hcga_storage_documents')) {
            Schema::create('hcga_storage_documents', function (Blueprint $table) {
                $table->increments('id');
                $table->string('module_type', 30); // REGULATION, INFORMATION
                $table->string('sub_type', 40); // SK_DIR, SOP, INTERNAL_MEMO, UMUM, PANDUAN, EVENT, PENGUMUMAN
                $table->string('document_number', 100)->nullable();
                $table->string('title', 255);
                $table->string('owner_department', 100)->nullable();
                $table->unsignedInteger('current_version_id')->nullable();
                $table->unsignedInteger('current_version_number')->default(1);
                $table->string('status', 30)->default('DRAFT'); // DRAFT, WAITING_APPROVAL, PUBLISHED, REVOKED, ARCHIVED
                $table->text('revoked_reason')->nullable();
                $table->dateTime('revoked_at')->nullable();
                $table->string('revoked_by', 150)->nullable();
                $table->string('created_by', 150);
                $table->dateTime('created_date')->useCurrent();
                $table->string('updated_by', 150)->nullable();
                $table->dateTime('updated_date')->nullable();

                $table->index('module_type');
                $table->index('sub_type');
                $table->index('document_number');
                $table->index('current_version_id');
                $table->index('status');
            });
        }

        if (!Schema::hasTable('hcga_storage_document_versions')) {
            Schema::create('hcga_storage_document_versions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('document_id');
                $table->unsignedInteger('version_number')->default(1);
                $table->string('version_label', 20)->default('v1');

                // Audit-ready snapshots at version creation/publication time
                $table->string('document_number_snapshot', 100)->nullable();
                $table->string('sub_type_snapshot', 40)->nullable();
                $table->string('owner_department_snapshot', 100)->nullable();

                $table->string('title', 255);
                $table->string('summary', 500)->nullable();
                $table->longText('content');
                $table->text('changelog')->nullable();
                $table->string('thumbnail', 255)->nullable();
                $table->string('attachment_file', 255)->nullable();
                $table->string('attachment_name', 255)->nullable();
                $table->string('attachment_mime', 100)->nullable();
                $table->unsignedInteger('attachment_size')->nullable();

                // Validity / Ketetapan
                $table->string('validity_mode', 20)->default('PERPETUAL'); // FIXED_RANGE, PERPETUAL
                $table->date('effective_from')->nullable();
                $table->date('effective_until')->nullable();
                $table->tinyInteger('is_perpetual')->default(1);

                // Target Audience & Mobile Announcement Integration
                $table->string('target_audience', 30)->default('ALL'); // ALL, DEPARTMENT
                $table->text('target_departments')->nullable(); // JSON array of department codes
                $table->tinyInteger('is_pinned')->default(0);
                $table->tinyInteger('require_read_receipt')->default(0);
                $table->tinyInteger('publish_to_mobile')->default(1);
                $table->string('announcement_sync_mode', 30)->default('UPDATE_EXISTING'); // UPDATE_EXISTING, CREATE_NEW
                $table->tinyInteger('archive_old_announcement')->default(1);

                // Visibility when this version becomes SUPERSEDED
                $table->string('history_visibility', 30)->default('ADMIN_ONLY'); // ADMIN_ONLY, EMPLOYEE_VISIBLE
                $table->string('previous_version_visibility', 30)->default('ADMIN_ONLY');

                // Lifecycle & Approval
                $table->string('version_status', 30)->default('DRAFT'); // DRAFT, WAITING_APPROVAL, ACTIVE, SUPERSEDED, REJECTED, REVOKED
                $table->unsignedInteger('approval_request_id')->nullable();
                $table->string('approval_status', 30)->nullable();
                $table->dateTime('published_at')->nullable();
                $table->string('published_by', 150)->nullable();
                $table->string('created_by', 150);
                $table->dateTime('created_date')->useCurrent();
                $table->string('updated_by', 150)->nullable();
                $table->dateTime('updated_date')->nullable();

                $table->index('document_id');
                $table->index('version_status');
                $table->index('effective_from');
                $table->index('effective_until');
                $table->index('approval_request_id');
                $table->unique(['document_id', 'version_number'], 'uq_storage_doc_version');
            });
        }

        if (!Schema::hasTable('hcga_storage_read_receipts')) {
            Schema::create('hcga_storage_read_receipts', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('document_id');
                $table->unsignedInteger('version_id');
                $table->unsignedInteger('announcement_id')->nullable();
                $table->unsignedInteger('user_id')->nullable();
                $table->string('username', 150);
                $table->string('emp_nik', 50)->nullable();
                $table->string('emp_name', 150)->nullable();
                $table->string('department', 100)->nullable();
                $table->dateTime('read_at')->useCurrent();
                $table->dateTime('acknowledged_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('source', 30)->default('MOBILE_WEB'); // MOBILE_WEB, MOBILE_API, DESKTOP_WEB

                $table->index('document_id');
                $table->index('version_id');
                $table->index('announcement_id');
                $table->unique(['version_id', 'username'], 'uq_storage_read_version_user');
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
        Schema::dropIfExists('hcga_storage_read_receipts');
        Schema::dropIfExists('hcga_storage_document_versions');
        Schema::dropIfExists('hcga_storage_documents');
    }
}
