<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSurveyFormTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('survey_form_versions')) {
            Schema::create('survey_form_versions', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->integer('Version_No')->default(1);
                $table->string('Name');
                $table->enum('Status', ['DRAFT', 'ACTIVE', 'ARCHIVED'])->default('DRAFT');
                $table->decimal('Max_Score', 10, 2)->default(100);
                $table->decimal('Pass_Threshold_Pct', 5, 2)->default(80);
                $table->decimal('Conditional_Threshold_Pct', 5, 2)->default(70);
                $table->json('Score_Config')->nullable();
                $table->string('Created_By')->nullable();
                $table->timestamp('Published_At')->nullable();
                $table->timestamp('Archived_At')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('survey_form_items')) {
            Schema::create('survey_form_items', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Form_Version_ID');
                $table->string('Section_Code', 2);
                $table->string('Item_Code', 30);
                $table->integer('Sort_Order')->default(1);
                $table->string('Input_Type', 30);
                $table->text('Criteria')->nullable();
                $table->text('Keterangan')->nullable();
                $table->text('Minimum_Standard')->nullable();
                $table->text('Parameter_Evaluation')->nullable();
                $table->json('Score_Config')->nullable();
                $table->boolean('File_Required')->default(false);
                $table->boolean('Notes_Enabled')->default(false);
                $table->boolean('Is_Active')->default(true);
                $table->timestamps();

                $table->index(['Form_Version_ID', 'Section_Code', 'Sort_Order'], 'survey_form_items_version_idx');
            });
        }

        if (!Schema::hasTable('survey_answers')) {
            Schema::create('survey_answers', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Survey_Location_ID');
                $table->unsignedBigInteger('Form_Item_ID')->nullable();
                $table->string('Item_Code', 30);
                $table->string('Section_Code', 2);
                $table->text('Criteria_Snapshot')->nullable();
                $table->text('Keterangan_Snapshot')->nullable();
                $table->text('Minimum_Standard_Snapshot')->nullable();
                $table->text('Parameter_Evaluation_Snapshot')->nullable();
                $table->json('Answer_JSON')->nullable();
                $table->decimal('Score', 10, 2)->default(0);
                $table->text('Notes')->nullable();
                $table->string('File_Path')->nullable();
                $table->timestamps();

                $table->index(['Survey_Location_ID', 'Section_Code'], 'survey_answers_survey_idx');
            });
        }

        if (!Schema::hasColumn('survey_location', 'Survey_Form_Version_ID')) {
            Schema::table('survey_location', function (Blueprint $table) {
                $table->unsignedBigInteger('Survey_Form_Version_ID')->nullable();
                $table->decimal('Max_Score', 10, 2)->nullable();
                $table->decimal('Score_Percentage', 8, 2)->nullable();
                $table->string('Recommendation', 50)->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('survey_location')) {
            Schema::table('survey_location', function (Blueprint $table) {
                foreach (['Recommendation', 'Score_Percentage', 'Max_Score', 'Survey_Form_Version_ID'] as $column) {
                    if (Schema::hasColumn('survey_location', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('survey_answers');
        Schema::dropIfExists('survey_form_items');
        Schema::dropIfExists('survey_form_versions');
    }
}
