<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SplitPermitLicenseMailSettings extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('email_settings')) {
            Schema::create('email_settings', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->boolean('Is_Enabled')->default(0);
                $table->string('Smtp_Host')->nullable();
                $table->unsignedInteger('Smtp_Port')->nullable()->default(587);
                $table->string('Smtp_Encryption', 20)->nullable();
                $table->string('Smtp_Username')->nullable();
                $table->text('Smtp_Password')->nullable();
                $table->string('Mail_From_Address')->nullable();
                $table->string('Mail_From_Name')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permit_license_mail_settings')) {
            return;
        }

        $old = DB::table('permit_license_mail_settings')->orderBy('ID')->first();
        if ($old && !DB::table('email_settings')->exists()) {
            DB::table('email_settings')->insert([
                'Is_Enabled' => property_exists($old, 'Is_Enabled') ? (int) $old->Is_Enabled : 0,
                'Smtp_Host' => property_exists($old, 'Smtp_Host') ? $old->Smtp_Host : null,
                'Smtp_Port' => property_exists($old, 'Smtp_Port') ? $old->Smtp_Port : 587,
                'Smtp_Encryption' => property_exists($old, 'Smtp_Encryption') ? $old->Smtp_Encryption : 'tls',
                'Smtp_Username' => property_exists($old, 'Smtp_Username') ? $old->Smtp_Username : null,
                'Smtp_Password' => property_exists($old, 'Smtp_Password') ? $old->Smtp_Password : null,
                'Mail_From_Address' => property_exists($old, 'Mail_From_Address') ? $old->Mail_From_Address : null,
                'Mail_From_Name' => property_exists($old, 'Mail_From_Name') ? $old->Mail_From_Name : null,
                'Created_By' => property_exists($old, 'Created_By') ? $old->Created_By : 'migration',
                'Updated_By' => property_exists($old, 'Updated_By') ? $old->Updated_By : 'migration',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (['Is_Enabled', 'Smtp_Host', 'Smtp_Port', 'Smtp_Encryption', 'Smtp_Username', 'Smtp_Password', 'Mail_From_Address', 'Mail_From_Name'] as $column) {
            if (Schema::hasColumn('permit_license_mail_settings', $column)) {
                DB::statement('ALTER TABLE `permit_license_mail_settings` DROP COLUMN `' . $column . '`');
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('permit_license_mail_settings')) {
            Schema::table('permit_license_mail_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('permit_license_mail_settings', 'Is_Enabled')) {
                    $table->boolean('Is_Enabled')->default(0);
                }
                if (!Schema::hasColumn('permit_license_mail_settings', 'Smtp_Host')) {
                    $table->string('Smtp_Host')->nullable();
                }
                if (!Schema::hasColumn('permit_license_mail_settings', 'Smtp_Port')) {
                    $table->unsignedInteger('Smtp_Port')->nullable()->default(587);
                }
                if (!Schema::hasColumn('permit_license_mail_settings', 'Smtp_Encryption')) {
                    $table->string('Smtp_Encryption', 20)->nullable();
                }
                if (!Schema::hasColumn('permit_license_mail_settings', 'Smtp_Username')) {
                    $table->string('Smtp_Username')->nullable();
                }
                if (!Schema::hasColumn('permit_license_mail_settings', 'Smtp_Password')) {
                    $table->text('Smtp_Password')->nullable();
                }
                if (!Schema::hasColumn('permit_license_mail_settings', 'Mail_From_Address')) {
                    $table->string('Mail_From_Address')->nullable();
                }
                if (!Schema::hasColumn('permit_license_mail_settings', 'Mail_From_Name')) {
                    $table->string('Mail_From_Name')->nullable();
                }
            });

            $email = Schema::hasTable('email_settings')
                ? DB::table('email_settings')->orderBy('ID')->first()
                : null;
            $target = DB::table('permit_license_mail_settings')->orderBy('ID')->first();

            if ($email && $target) {
                DB::table('permit_license_mail_settings')->where('ID', $target->ID)->update([
                    'Is_Enabled' => $email->Is_Enabled,
                    'Smtp_Host' => $email->Smtp_Host,
                    'Smtp_Port' => $email->Smtp_Port,
                    'Smtp_Encryption' => $email->Smtp_Encryption,
                    'Smtp_Username' => $email->Smtp_Username,
                    'Smtp_Password' => $email->Smtp_Password,
                    'Mail_From_Address' => $email->Mail_From_Address,
                    'Mail_From_Name' => $email->Mail_From_Name,
                ]);
            }
        }

        Schema::dropIfExists('email_settings');
    }
}
