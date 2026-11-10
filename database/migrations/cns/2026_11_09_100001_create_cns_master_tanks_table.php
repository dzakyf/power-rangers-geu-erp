<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsMasterTanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cns_master_tanks')) {
            Schema::create('cns_master_tanks', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 100)->unique();
                $table->decimal('capacity', 12, 2)->default(0); // in KG
                $table->text('notes')->nullable();
                $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE')->index();
                $table->string('created_by', 100)->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('cns_master_tanks');
    }
}
