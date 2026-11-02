<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaSecurityTables extends Migration
{
    public function up()
    {
        // 1. Buku Tamu (Visitor Log)
        if (!Schema::hasTable('ga_security_visitor')) {
            Schema::create('ga_security_visitor', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Visitor_No', 60)->unique();
                $table->string('Visitor_Name', 150);
                $table->string('Identity_Number', 60)->nullable(); // KTP / SIM / Passport
                $table->string('Institution', 150)->nullable(); // Perusahaan / Asal Tamu
                $table->string('Phone_Number', 50)->nullable();
                $table->string('Host_User_Id', 100)->nullable()->index();
                $table->string('Host_Name', 150);
                $table->string('Host_Department', 100)->nullable();
                $table->string('Purpose', 255);
                $table->string('Location', 50)->default('');
                $table->string('Post_Name', 100)->nullable(); // Pos Penjagaan (Pos Barat, Lobby, Pos Gerbang)
                $table->string('Badge_Number', 50)->nullable(); // No Kartu Tamu / Visitor Pass
                $table->dateTime('Time_In');
                $table->dateTime('Time_Out')->nullable();
                $table->string('Vehicle_Number', 50)->nullable(); // No Polisi Kendaraan
                $table->string('Status', 30)->default('IN')->index(); // IN, OUT
                $table->text('Remarks')->nullable();
                $table->string('Recorded_By', 100)->nullable();
                $table->string('Check_Out_By', 100)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }

        // 2. Laporan Insiden Keamanan (Incident Reports)
        if (!Schema::hasTable('ga_security_incident')) {
            Schema::create('ga_security_incident', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Incident_No', 60)->unique();
                $table->string('Title', 200);
                $table->string('Incident_Type', 50); // THEFT, PROPERTY_DAMAGE, INTRUSION, FIGHT, SAFETY_HAZARD, OTHER
                $table->string('Severity', 20)->default('MEDIUM'); // LOW, MEDIUM, HIGH, CRITICAL
                $table->string('Confidentiality', 20)->default('NORMAL'); // NORMAL, CONFIDENTIAL, STRICTLY_CONFIDENTIAL
                $table->date('Incident_Date')->index();
                $table->time('Incident_Time')->nullable();
                $table->string('Location', 50)->default('');
                $table->string('Specific_Location', 150)->nullable();
                $table->text('Description');
                $table->text('Involved_Parties')->nullable(); // Pihak / Korban / Saksi / Terduga yang terlibat
                $table->text('Initial_Action')->nullable(); // Tindakan langsung yang diambil sekuriti
                $table->string('Status', 30)->default('REPORTED')->index(); // REPORTED, INVESTIGATING, RESOLVED, CLOSED
                $table->unsignedBigInteger('Ticket_ID')->nullable()->index(); // Follow-up ticket jika perlu perbaikan teknis
                $table->string('Reporter_Name', 150);
                $table->string('Reported_By', 100)->nullable();
                $table->string('Resolved_By', 100)->nullable();
                $table->text('Resolution_Notes')->nullable();
                $table->timestamp('Resolved_At')->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }

        // 3. Log Audit Akses Insiden Rahasia (Confidential View Audit)
        if (!Schema::hasTable('ga_security_incident_view_log')) {
            Schema::create('ga_security_incident_view_log', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->unsignedInteger('Incident_ID')->index();
                $table->string('Viewed_By', 100);
                $table->string('Role', 50)->nullable();
                $table->string('IP_Address', 50)->nullable();
                $table->timestamp('Viewed_At')->useCurrent();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ga_security_incident_view_log');
        Schema::dropIfExists('ga_security_incident');
        Schema::dropIfExists('ga_security_visitor');
    }
}
