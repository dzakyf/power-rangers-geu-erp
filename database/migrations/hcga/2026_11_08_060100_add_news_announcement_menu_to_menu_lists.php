<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddNewsAnnouncementMenuToMenuLists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if menu item already exists
        $existsSub = DB::table('menu_lists')->where('Menu_ID', 'SS00105')->exists();
        if (!$existsSub) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SS00105',
                'Name'        => 'News & Announcement',
                'Path_Loc'    => '#',
                'Position'    => 3,
                'Parent_Menu' => 'SM00067', // Under GA / HCGA
                'Urutan'      => 12,
                'Modules'     => 'HRD',
            ]);
        }

        $existsItem = DB::table('menu_lists')->where('Menu_ID', 'SI01037')->exists();
        if (!$existsItem) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SI01037',
                'Name'        => 'Announcement CMS',
                'Path_Loc'    => 'hrd-announcement-index',
                'Position'    => 4,
                'Parent_Menu' => 'SS00105',
                'Urutan'      => 1,
                'Modules'     => 'HRD',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('menu_lists')->whereIn('Menu_ID', ['SS00105', 'SI01037'])->delete();
    }
}

