<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(MenuListsSeeder::class);
        $this->call(RbacSeeder::class);
        $this->call(MasterBankSeeder::class);

        DB::table('users')->insert([
            'username' => "nirone",
            'Name' => "nirone",
            'Location' => "GEU-HO",
            'email' => Str::random(10).'@gmail.com',
            'password' => bcrypt('admin')
        ]);
    }
}
