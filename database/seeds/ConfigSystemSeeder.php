<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigSystemSeeder extends Seeder
{
    public function run()
    {
        $rows = [
            'WAREHOUSE_FEE'        => 0,
            'INVENTORY_FEE'        => 0,
            'PKS_MEMBERSHIP_FEE'   => 0,
        ];

        foreach ($rows as $constraint => $value) {
            if (!DB::table('config_system')->where('Constraint', $constraint)->exists()) {
                DB::table('config_system')->insert([
                    'Constraint' => $constraint,
                    'Value'      => $value,
                ]);
            }
        }

        $this->command->info('config_system seeded: WAREHOUSE_FEE, INVENTORY_FEE, PKS_MEMBERSHIP_FEE.');
    }
}
