<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed the local admin user (standalone; idempotent by username).
 * Sub_Menu is filled with the CNS menu IDs so the CNS sidebar renders.
 */
class UserSeeder extends Seeder
{
    public function run()
    {
        $cnsMenuIds = DB::table('menu_lists')
            ->where('Modules', 'CNS')
            ->orderBy('Position')
            ->orderBy('Urutan')
            ->pluck('Menu_ID')
            ->implode(',');

        $row = [
            'username'        => 'admin',
            'Emp_ID'          => 'ADMIN-0001',
            'Name'            => 'ADMINISTRATOR',
            'password'        => bcrypt('geu009'),
            'Location'        => 'GEU-HO',
            'Position'        => 'Administrator',
            'Contact'         => null,
            'email'           => 'admin@geu.local',
            'Company_ID'      => 'GEU',
            'Department'      => 'ICT',
            'City_ID'         => null,
            'Priv_ID'         => 'ADM',
            'Area'            => null,
            'Class'           => null,
            'Expired'         => null,
            'ChangePwd'       => 0,
            'Status'          => 'ACTIVE',
            'DOP_Class'       => 'NONE',
            'created_at'      => now(),
            'Note'            => null,
            'LastLogin'       => null,
            'COD_Login'       => null,
            'MainPriv'        => null,
            'Alternatif_Pass' => null,
            'mobile_id'       => null,
            'update_at'       => null,
            'email_confirmed' => 1,
            'remember_token'  => null,
            'PR_Approval'     => 'ALL',
            'WH_ID'           => null,
            'Main_Menu'       => 'CNS',
            'Sub_Menu'        => $cnsMenuIds,
            'BP_Approval'     => 0,
            'Blasting'        => 0,
            'Blasting_Status' => null,
            'Blasting_Date'   => null,
            'Admin_Chat'      => 0,
            'Address'         => null,
            'Bank_Account_No' => null,
            'Bank_Name'       => null,
            'City'            => null,
            'Bank_Account_Name' => null,
            'Training_Approval' => 0,
            'TokenAuth'       => null,
        ];

        DB::table('users')->updateOrInsert(['username' => 'admin'], $row);

        $this->command->info('UserSeeder: admin user seeded/updated (CNS Sub_Menu).');
    }
}
