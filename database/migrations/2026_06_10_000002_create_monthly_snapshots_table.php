<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonthlySnapshotsTable extends Migration
{
    public function up()
    {
        Schema::create('pelindo_monthly_snapshots', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->year('Year');
            $table->tinyInteger('Month');
            $table->decimal('Opening_Balance', 12, 2)->default(0);
            $table->decimal('Total_Inbound', 12, 2)->default(0);
            $table->decimal('Total_Outbound', 12, 2)->default(0);
            $table->decimal('Closing_Balance', 12, 2)->default(0);
            $table->date('Snapshot_Date');
            $table->timestamp('Created_At')->nullable();
            $table->timestamp('Updated_At')->nullable();

            $table->unique(['Year', 'Month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pelindo_monthly_snapshots');
    }
}
