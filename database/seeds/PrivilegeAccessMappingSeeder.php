<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrivilegeAccessMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaults = [
            // Berdasarkan Posisi / Level Jabatan
            [
                'mapping_type' => 'POSITION',
                'target_key'   => 'Staff',
                'priv_id'      => 'STAFF',
                'default_role' => 'STAFF',
                'description'  => 'Akses default level Staff operasional',
                'status'       => 'ACTIVE',
            ],
            [
                'mapping_type' => 'POSITION',
                'target_key'   => 'Officer',
                'priv_id'      => 'STAFF',
                'default_role' => 'STAFF',
                'description'  => 'Akses default level Officer',
                'status'       => 'ACTIVE',
            ],
            [
                'mapping_type' => 'POSITION',
                'target_key'   => 'Supervisor',
                'priv_id'      => 'HEAD',
                'default_role' => 'HEAD',
                'description'  => 'Akses default level Supervisor / Section Head',
                'status'       => 'ACTIVE',
            ],
            [
                'mapping_type' => 'POSITION',
                'target_key'   => 'Manager',
                'priv_id'      => 'MANAGER',
                'default_role' => 'MANAGER',
                'description'  => 'Akses default level Manager Departemen',
                'status'       => 'ACTIVE',
            ],
            [
                'mapping_type' => 'POSITION',
                'target_key'   => 'General Manager',
                'priv_id'      => 'GM',
                'default_role' => 'GM',
                'description'  => 'Akses default level General Manager',
                'status'       => 'ACTIVE',
            ],
            [
                'mapping_type' => 'POSITION',
                'target_key'   => 'Vice President',
                'priv_id'      => 'VP',
                'default_role' => 'VP',
                'description'  => 'Akses default level Vice President / Direksi',
                'status'       => 'ACTIVE',
            ],
            [
                'mapping_type' => 'POSITION',
                'target_key'   => 'Specialist',
                'priv_id'      => 'HEAD',
                'default_role' => 'HEAD',
                'description'  => 'Akses level Spesialis setara Senior/Head',
                'status'       => 'ACTIVE',
            ],
            [
                'mapping_type' => 'POSITION',
                'target_key'   => 'Lead',
                'priv_id'      => 'HEAD',
                'default_role' => 'HEAD',
                'description'  => 'Akses level Team Leader',
                'status'       => 'ACTIVE',
            ],
            [
                'mapping_type' => 'POSITION',
                'target_key'   => 'Foreman',
                'priv_id'      => 'HEAD',
                'default_role' => 'HEAD',
                'description'  => 'Akses level Foreman / Mandor Operasional',
                'status'       => 'ACTIVE',
            ],

            // Berdasarkan Direktorat
            [
                'mapping_type' => 'DIRECTORAT',
                'target_key'   => 'Human Capital & General Affairs',
                'priv_id'      => 'HCGA',
                'default_role' => 'HRD',
                'description'  => 'Akses default personel Direktorat HCGA',
                'status'       => 'ACTIVE',
            ],
            [
                'mapping_type' => 'DIRECTORAT',
                'target_key'   => 'Finance & Accounting',
                'priv_id'      => 'FINANCE',
                'default_role' => 'FINANCE',
                'description'  => 'Akses default personel Direktorat Finance & Accounting',
                'status'       => 'ACTIVE',
            ],
            [
                'mapping_type' => 'DIRECTORAT',
                'target_key'   => 'Operations',
                'priv_id'      => 'OPS',
                'default_role' => 'OPERATIONAL',
                'description'  => 'Akses default personel Direktorat Operations',
                'status'       => 'ACTIVE',
            ],
            [
                'mapping_type' => 'DIRECTORAT',
                'target_key'   => 'Commercial',
                'priv_id'      => 'SALES',
                'default_role' => 'SALES',
                'description'  => 'Akses default personel Direktorat Commercial & Sales',
                'status'       => 'ACTIVE',
            ],
        ];

        foreach ($defaults as $item) {
            DB::table('privilege_access_mappings')->updateOrInsert(
                [
                    'mapping_type' => $item['mapping_type'],
                    'target_key'   => $item['target_key'],
                ],
                array_merge($item, [
                    'created_by' => 'SYSTEM',
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

