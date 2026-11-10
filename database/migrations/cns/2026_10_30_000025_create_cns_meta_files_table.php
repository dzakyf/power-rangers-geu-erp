<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsMetaFilesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('meta_files')) {
            return; // already provisioned on the live database
        }

        Schema::create('meta_files', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Filename', 100)->nullable();
            $table->string('Path', 100)->nullable();
            $table->string('Extension', 100)->nullable();
            $table->text('Description')->nullable();
            $table->dateTime('Created_At')->nullable()->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));
            $table->string('Topic', 100)->nullable();
            $table->string('Map_To', 100)->nullable();

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
