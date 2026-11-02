<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaRoomBookingTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ga_room')) {
            Schema::create('ga_room', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Room_Code', 50)->unique();
                $table->string('Room_Name', 150);
                $table->string('Location', 50)->default('');
                $table->string('Building', 100)->nullable();
                $table->string('Floor', 50)->nullable();
                $table->integer('Capacity')->default(10);
                $table->text('Facilities')->nullable(); // Proyektor, AC, Sound, Whiteboard, Video Conference
                $table->tinyInteger('Is_Restricted')->default(0); // 1 = butuh approval
                $table->string('Status', 30)->default('ACTIVE')->index(); // ACTIVE, MAINTENANCE, INACTIVE
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }

        if (!Schema::hasTable('ga_room_booking')) {
            Schema::create('ga_room_booking', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Booking_No', 60)->unique();
                $table->unsignedInteger('Room_ID')->index();
                $table->date('Booking_Date')->index();
                $table->time('Start_Time');
                $table->time('End_Time');
                $table->string('Organizer_User_Id', 100)->nullable()->index();
                $table->string('Organizer_Name', 150);
                $table->string('Organizer_Department', 100)->nullable();
                $table->string('Organizer_Contact', 50)->nullable();
                $table->string('Subject', 200);
                $table->text('Purpose')->nullable();
                $table->integer('Attendees_Count')->default(1);
                $table->string('Status', 30)->default('CONFIRMED')->index(); // CONFIRMED, WAITING_APPROVAL, CANCELLED, COMPLETED
                $table->tinyInteger('Need_Projector')->default(0);
                $table->tinyInteger('Need_Sound')->default(0);
                $table->tinyInteger('Need_Layout')->default(0);
                $table->text('Layout_Note')->nullable();
                $table->unsignedBigInteger('Ticket_ID')->nullable()->index();
                $table->string('Cancellation_Reason', 500)->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();
                $table->index(['Room_ID', 'Booking_Date', 'Status'], 'ga_room_booking_schedule_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ga_room_booking');
        Schema::dropIfExists('ga_room');
    }
}
