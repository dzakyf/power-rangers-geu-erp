<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GaCleaningChecklistSeeder extends Seeder
{
    public function run()
    {
        $now = now();
        $templates = [
            'TOILET' => [
                ['Wastafel, Kran & Cermin', 'Kering, bersih bebas bercak air dan sabun'],
                ['Kloset / Urinoir & Flush', 'Disikat bersih, disinfektan, flush berfungsi normal'],
                ['Lantai & Dinding Keramik', 'Disikat, dipel kering, tidak licin dan tidak berbau'],
                ['Tempat Sampah Toilet', 'Dikosongkan, diganti plastik sampah baru'],
                ['Refill Sabun Cuci Tangan & Tissue', 'Diisi penuh dan siap pakai'],
            ],
            'OFFICE' => [
                ['Sapu & Pel Lantai Ruang Kerja', 'Lantai bersih dari debu, kotoran, dan noda'],
                ['Lap Permukaan Meja & Kursi', 'Bebas debu, rapi, dan tidak menggeser dokumen'],
                ['Kaca Pintu & Jendela', 'Dibersihkan dengan pembersih kaca'],
                ['Tempat Sampah Meja / Ruangan', 'Dikosongkan dan dibuang ke penampungan utama'],
                ['Kerapian Kabel & Area Sekitar', 'Tidak ada sampah berserakan'],
            ],
            'PANTRY' => [
                ['Bak Cuci Piring (Sink)', 'Bersih dari sisa makanan dan minyak'],
                ['Meja Counter & Meja Makan', 'Dibersihkan, disemprot sanitasi dan dilap kering'],
                ['Dispenser Air & Kulkas', 'Permukaan luar dilap bersih bebas noda'],
                ['Lantai Pantry / Kantin', 'Dipel dengan cairan pembersih lantai'],
                ['Tempat Sampah Organik & Anorganik', 'Dikosongkan dan diganti plastik baru'],
            ],
            'LOBBY' => [
                ['Lantai Utama & Keset Masuk', 'Dipel mengkilap, keset divakum / dibersihkan'],
                ['Pintu Kaca Utama & Handle', 'Bebas sidik jari dan debu'],
                ['Sofa & Meja Resepsionis', 'Dilap bersih dan rapi'],
                ['Tempat Sampah Lobby', 'Dikosongkan'],
            ],
            'CORRIDOR' => [
                ['Sapu & Pel Selasar / Koridor', 'Lantai bersih dan kering'],
                ['Dinding & Sudut Koridor', 'Bebas sarang laba-laba dan noda'],
                ['Tempat Sampah Koridor', 'Dikosongkan dan dibersihkan'],
            ],
            'OUTDOOR' => [
                ['Sapu Halaman & Area Parkir', 'Bersih dari daun kering dan sampah'],
                ['Saluran Air / Selokan Terbuka', 'Bebas sampah penyumbat'],
                ['Tempat Sampah Luar', 'Dikosongkan dan ditutup rapat'],
            ],
        ];

        foreach ($templates as $category => $items) {
            $order = 1;
            foreach ($items as $item) {
                $exists = DB::table('ga_cleaning_checklist_template')
                    ->where('Area_Category', $category)
                    ->where('Item_Name', $item[0])
                    ->first();

                if (!$exists) {
                    DB::table('ga_cleaning_checklist_template')->insert([
                        'Area_Category' => $category,
                        'Item_Name' => $item[0],
                        'Item_Description' => $item[1],
                        'Sort_Order' => $order++,
                        'Status' => 'ACTIVE',
                        'Created_At' => $now,
                        'Updated_At' => $now,
                    ]);
                }
            }
        }

        $this->command->info('GaCleaningChecklistSeeder completed successfully.');
    }
}
