<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExitClearanceDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('exit_clearance_documents')) {
            Schema::create('exit_clearance_documents', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('Exit_Clearance_ID');
                $table->string('Document_Type', 50)->default('FINAL_CLEARANCE_PDF'); // RESIGNATION_LETTER, FINAL_CLEARANCE_PDF, HANDOVER_FORM
                $table->string('Document_No', 100)->nullable();
                $table->string('File_Name', 255);
                $table->string('File_Path', 255);
                $table->integer('File_Size')->nullable(); // dalam bytes
                $table->string('Mime_Type', 50)->default('application/pdf');
                $table->string('Sha256_Checksum', 64)->nullable(); // untuk integritas audit kepatuhan ISO/HCGA
                $table->unsignedInteger('Created_By')->nullable();
                $table->timestamps();

                $table->index(['Exit_Clearance_ID', 'Document_Type']);
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
        Schema::dropIfExists('exit_clearance_documents');
    }
}

