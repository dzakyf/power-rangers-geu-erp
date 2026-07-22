<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(RbacSeeder::class);

        DB::table('users')->insert([
            'username' => "nirone",
            'Name' => "nirone",
            'Location' => "GEU-HO",
            'email' => Str::random(10).'@gmail.com',
            'password' => bcrypt('admin')
        ]);
    }
}
