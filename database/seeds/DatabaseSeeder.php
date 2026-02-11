<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         DB::table('users')->insert([
            'username' => "nirone",
			'Name' => "nirone",
			'Location' => "GEU-HO",
            'email' => Str::random(10).'@gmail.com',
            'password' => bcrypt('admin')
        ]);
		
    }
}
