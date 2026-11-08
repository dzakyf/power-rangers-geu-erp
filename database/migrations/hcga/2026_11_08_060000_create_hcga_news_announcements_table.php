<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHcgaNewsAnnouncementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('hcga_news_announcements')) {
            Schema::create('hcga_news_announcements', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 255);
                $table->string('category', 50)->default('ANNOUNCEMENT'); // NEWS, ANNOUNCEMENT, POLICY, EVENT
                $table->string('summary', 500)->nullable();
                $table->longText('content');
                $table->string('thumbnail', 255)->nullable();
                $table->string('attachment_file', 255)->nullable();
                $table->string('attachment_name', 255)->nullable();
                $table->string('target_audience', 50)->default('ALL'); // ALL, HCGA, INTERNAL
                $table->tinyInteger('is_pinned')->default(0);
                $table->unsignedInteger('view_count')->default(0);
                $table->tinyInteger('is_final')->default(0); // 0 = Draft, 1 = Published
                $table->dateTime('final_date')->nullable();
                $table->string('created_by', 150);
                $table->dateTime('created_date')->useCurrent();
                $table->string('updated_by', 150)->nullable();
                $table->dateTime('updated_date')->nullable();

                $table->index('category');
                $table->index('is_final');
                $table->index('is_pinned');
                $table->index('target_audience');
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
        Schema::dropIfExists('hcga_news_announcements');
    }
}

