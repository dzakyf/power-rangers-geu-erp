<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBdpAuditTrainingMasterTables extends Migration
{
    public function up()
    {
        Schema::create('master_audit_category', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('id');
            $table->string('category', 255);
            $table->string('category_description', 255);
            $table->string('parameter_list', 255);
            $table->string('parameter_score', 255);
            $table->string('status', 50);
            $table->Integer('urutan');
            $table->string('created_by', 150);
            $table->dateTime('created_date')->useCurrent();
        });
        DB::statement("ALTER TABLE `master_audit_category` MODIFY `id` int NOT NULL AUTO_INCREMENT");
        Schema::create('master_audit_parameter', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('id');
            $table->string('category', 255);
            $table->string('parameter', 255);
            $table->string('parameter_id', 150);
            $table->string('parameter_list', 255);
            $table->string('parameter_score', 255);
            $table->string('parameter_status', 150);
            $table->string('created_by', 150);
            $table->dateTime('created_date');
            $table->string('parameter_minor', 255)->nullable();
            $table->string('parameter_major', 255)->nullable();
            $table->string('parameter_critical', 255)->nullable();
        });
        DB::statement("ALTER TABLE `master_audit_parameter` MODIFY `id` int NOT NULL AUTO_INCREMENT");
        Schema::create('master_audit_score', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('id');
            $table->string('score_range', 150);
            $table->string('status', 150);
        });
        DB::statement("ALTER TABLE `master_audit_score` MODIFY `id` int NOT NULL AUTO_INCREMENT");
        Schema::create('master_audit_temuan', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('id');
            $table->string('category', 150);
            $table->string('area', 150);
            $table->string('parameter', 150);
            $table->Integer('ca_days');
            $table->Integer('ca_score');
        });
        DB::statement("ALTER TABLE `master_audit_temuan` MODIFY `id` int NOT NULL AUTO_INCREMENT");
        Schema::create('master_satisfication', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('id');
            $table->string('target', 150);
            $table->string('question', 255);
            $table->string('created_by', 150);
            $table->dateTime('created_date')->useCurrent();
        });
        DB::statement("ALTER TABLE `master_satisfication` MODIFY `id` int NOT NULL AUTO_INCREMENT");
        Schema::create('master_training', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('id');
            $table->string('training_no', 150);
            $table->string('target', 150);
            $table->string('category', 150);
            $table->string('training_name', 255);
            $table->string('training_coverage', 255);
            $table->string('frequency', 150);
            $table->Integer('training_month');
            $table->date('training_date_from')->nullable();
            $table->date('training_date_to')->nullable();
            $table->time('training_time_from')->nullable();
            $table->time('training_time_to')->nullable();
            $table->string('status', 50)->default('OPEN');
            $table->string('created_by', 150);
            $table->dateTime('created_date')->useCurrent();
        });
        DB::statement("ALTER TABLE `master_training` MODIFY `id` int NOT NULL AUTO_INCREMENT");
        Schema::create('master_materials', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('id');
            $table->string('material_no', 255);
            $table->string('material_name', 255);
            $table->string('material_speaker', 255)->nullable();
            $table->string('material_category', 50);
            $table->boolean('material_download');
            $table->boolean('material_view');
            $table->string('material_attachment', 255)->nullable();
            $table->string('material_link', 255)->nullable();
            $table->boolean('material_is_upload');
            $table->string('material_status', 150);
            $table->string('created_by', 150);
            $table->dateTime('created_date')->useCurrent();
        });
        DB::statement("ALTER TABLE `master_materials` MODIFY `id` int NOT NULL AUTO_INCREMENT");
    }

    public function down()
    {
        Schema::dropIfExists('master_materials');
        Schema::dropIfExists('master_training');
        Schema::dropIfExists('master_satisfication');
        Schema::dropIfExists('master_audit_temuan');
        Schema::dropIfExists('master_audit_score');
        Schema::dropIfExists('master_audit_parameter');
        Schema::dropIfExists('master_audit_category');
    }
}
