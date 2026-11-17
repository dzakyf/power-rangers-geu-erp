<?php

use App\Permission;
use App\Role;
use App\Services\RbacService;
use App\Services\Survey\SurveyScoringService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SurveyConfigurationSeeder extends Seeder
{
    public function run()
    {
        $this->seedMenu();
        $version = $this->seedFormVersion();
        $this->backfillSurveys($version);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function seedMenu(): void
    {
        if (!DB::table('menu_lists')->where('Menu_ID', 'SM00072')->exists()) {
            DB::table('menu_lists')->insert([
                'Menu_ID' => 'SM00072', 'Name' => 'Pengaturan', 'Path_Loc' => '#',
                'Position' => 2, 'Parent_Menu' => 'MS00010', 'Urutan' => 14, 'Modules' => 'MITRA',
            ]);
        }

        if (!DB::table('menu_lists')->where('Menu_ID', 'SS00109')->exists()) {
            DB::table('menu_lists')->insert([
                'Menu_ID' => 'SS00109', 'Name' => 'Survey', 'Path_Loc' => '#',
                'Position' => 3, 'Parent_Menu' => 'SM00072', 'Urutan' => 1, 'Modules' => 'MITRA',
            ]);
        }

        if (!DB::table('menu_lists')->where('Menu_ID', 'SI01052')->exists()) {
            DB::table('menu_lists')->insert([
                'Menu_ID' => 'SI01052', 'Name' => 'Form Builder', 'Path_Loc' => 'partner-survey-form-index',
                'Position' => 4, 'Parent_Menu' => 'SS00109', 'Urutan' => 1, 'Modules' => 'MITRA',
            ]);
        }

        foreach (['SM00072', 'SS00109', 'SI01052'] as $menuId) {
            foreach (['view', 'create', 'edit', 'delete', 'approve'] as $action) {
                Permission::firstOrCreate(
                    ['name' => "menu.$menuId.$action", 'guard_name' => 'web'],
                    ['menu_id' => $menuId, 'action' => $action]
                );
            }
        }

        $permissions = Permission::whereIn('menu_id', ['SM00072', 'SS00109', 'SI01052'])->get();

        $roleNames = DB::table('roles as r')
            ->join('role_has_permissions as rp', 'rp.role_id', '=', 'r.id')
            ->join('permissions as p', 'p.id', '=', 'rp.permission_id')
            ->whereIn('p.name', ['menu.SI00227.view', 'menu.SI00228.view'])
            ->distinct()
            ->pluck('r.name')
            ->all();

        $roleNames[] = 'ADM';

        foreach (array_unique($roleNames) as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $roleIds = Role::whereIn('name', array_unique($roleNames))->pluck('id');
        $userIds = DB::table('model_has_roles')->whereIn('role_id', $roleIds)->pluck('model_id')->unique();
        foreach ($userIds as $userId) {
            $user = \App\User::find($userId);
            if ($user) {
                RbacService::rebuildSubMenu($user);
            }
        }
    }

    private function seedFormVersion()
    {
        $version = DB::table('survey_form_versions')->where('Status', 'ACTIVE')->orderByDesc('ID')->first();
        if ($version) {
            return $version;
        }

        $versionId = DB::table('survey_form_versions')->insertGetId([
            'Version_No' => 1,
            'Name' => 'Survey Lokasi v1',
            'Status' => 'ACTIVE',
            'Max_Score' => 100,
            'Pass_Threshold_Pct' => 80,
            'Conditional_Threshold_Pct' => 70,
            'Score_Config' => json_encode([
                'f_max' => 15,
                'f_buckets' => [
                    ['gt' => 15, 'score' => 15],
                    ['gte' => 10, 'score' => 12],
                    ['gte' => 6, 'score' => 8],
                    ['gte' => 4, 'score' => 5],
                    ['gte' => 0, 'score' => 2],
                ],
            ]),
            'Created_By' => 'seeder',
            'Published_At' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seedItems($versionId);

        $items = DB::table('survey_form_items')->where('Form_Version_ID', $versionId)->get();
        $max = app(SurveyScoringService::class)->calculateMaxScore($items, (object) ['Score_Config' => json_encode(['f_max' => 15])]);
        DB::table('survey_form_versions')->where('ID', $versionId)->update(['Max_Score' => $max]);

        return DB::table('survey_form_versions')->where('ID', $versionId)->first();
    }

    private function seedItems(int $versionId): void
    {
        $photoData = [
            [1, 'Foto Tampak Depan', 'Foto Area Lokasi'],
            [2, 'Foto Area Operasional', 'Area untuk Booth / Mobil'],
            [3, 'Foto Akses Kendaraan', 'Jalan Masuk'],
            [4, 'Foto Lingkungan Sekitar', 'Kondisi Sekitar'],
        ];
        foreach ($photoData as [$no, $criteria, $keterangan]) {
            $this->insertItem($versionId, 'C', 'C' . $no, $no, 'boolean_photo', $criteria, $keterangan, null, null, ['yes' => 2.5, 'no' => 0], true, false);
        }

        $locationData = [
            [1, 'Luas Area Operasional', 'Area yang tersedia untuk penempatan equipment', 'Minimal 3 meter x 2 meter (6 m²)', 6],
            [2, 'Jenis Area', 'Kondisi area penempatan operasional', 'Outdoor / Semi terbuka, tidak berada di dalam ruangan tertutup', 6],
            [3, 'Akses Kendaraan Operasional', 'Kemudahan kendaraan masuk ke area hub', 'Kendaraan roda 4 dapat masuk dan melakukan aktivitas loading/unloading', 6],
            [4, 'Area Parkir', 'Ketersediaan parkir kendaraan operasional', 'Minimal 1 mobil dan 3 motor', 6],
            [5, 'Ketersediaan Listrik', 'Sumber listrik untuk operasional peralatan', 'Minimal 1.300 watt atau sumber listrik stabil', 6],
            [6, 'Ketersediaan Air Bersih', 'Air untuk kegiatan kebersihan operasional', 'Tersedia sumber air bersih (kran / sumur / PDAM)', 6],
            [7, 'Kondisi Lingkungan', 'Lingkungan sekitar mendukung aktivitas operasional', 'Area aman, tidak rawan banjir, tidak mengganggu aktivitas sekitar', 6],
            [8, 'Keamanan Lokasi', 'Tingkat keamanan area operasional', 'Area memiliki pengawasan / tidak rawan pencurian', 6],
            [9, 'Akses Jalan Utama', 'Kemudahan akses ke jalur transportasi utama', 'Lokasi mudah dijangkau kendaraan operasional', 7],
        ];
        foreach ($locationData as [$no, $criteria, $parameter, $minimum, $yesScore]) {
            $this->insertItem($versionId, 'D', 'D' . $no, $no, 'boolean_checklist', $criteria, null, $minimum, $parameter, ['yes' => $yesScore, 'no' => 0], false, true);
        }

        $environmentData = [
            [1, 'Kepadatan Area', 'Tingkat aktivitas dan kepadatan bangunan/usaha di sekitar lokasi dalam radius ±200–500 meter.', "Tinggi: area pasar, pusat kuliner, atau ruko ramai.\nSedang: area campuran ruko dan perumahan.\nRendah: area perumahan sepi atau lahan kosong."],
            [2, 'Aktivitas Ekonomi', 'Jenis aktivitas usaha di sekitar lokasi yang berpotensi menghasilkan minyak jelantah.', "Tinggi: >15 usaha kuliner.\nSedang: 5–15 usaha kuliner.\nRendah: <5 usaha kuliner."],
            [3, 'Potensi Sumber UCO', 'Perkiraan jumlah sumber penghasil minyak jelantah di sekitar lokasi dalam radius operasional.', "Tinggi: >20 sumber UCO.\nSedang: 10–20 sumber UCO.\nRendah: <10 sumber UCO."],
            [4, 'Keamanan Lokasi', 'Indikator: ada penjaga keamanan, CCTV, penerangan malam, tidak rawan pencurian atau vandalisme.', "Baik: area memiliki keamanan aktif (security/CCTV).\nCukup: area ramai namun tanpa sistem keamanan khusus.\nKurang: lokasi sepi atau rawan gangguan keamanan."],
        ];
        $ratingOptions = [
            ['label' => 'Kurang', 'value' => 1, 'score' => 1],
            ['label' => 'Sedang', 'value' => 2, 'score' => 3],
            ['label' => 'Tinggi', 'value' => 3, 'score' => 5],
        ];
        foreach ($environmentData as [$no, $criteria, $parameter, $keterangan]) {
            $this->insertItem($versionId, 'E', 'E' . $no, $no, 'rating', $criteria, $keterangan, null, $parameter, ['options' => $ratingOptions], false, false);
        }

        $ucoData = [
            [1, 'Restoran'],
            [2, 'Warung makan'],
            [3, 'Hotel'],
            [4, 'Rumah Tangga'],
        ];
        foreach ($ucoData as [$no, $source]) {
            $this->insertItem($versionId, 'F', 'F' . $no, $no, 'estimation', $source, null, null, null, [], false, false);
        }
    }

    private function insertItem(int $versionId, string $section, string $code, int $order, string $type, ?string $criteria, ?string $keterangan, ?string $minimum, ?string $parameter, array $scoreConfig, bool $fileRequired, bool $notesEnabled): void
    {
        if (DB::table('survey_form_items')->where('Form_Version_ID', $versionId)->where('Item_Code', $code)->exists()) {
            return;
        }

        DB::table('survey_form_items')->insert([
            'Form_Version_ID' => $versionId,
            'Section_Code' => $section,
            'Item_Code' => $code,
            'Sort_Order' => $order,
            'Input_Type' => $type,
            'Criteria' => $criteria,
            'Keterangan' => $keterangan,
            'Minimum_Standard' => $minimum,
            'Parameter_Evaluation' => $parameter,
            'Score_Config' => json_encode($scoreConfig),
            'File_Required' => $fileRequired ? 1 : 0,
            'Notes_Enabled' => $notesEnabled ? 1 : 0,
            'Is_Active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function backfillSurveys($version): void
    {
        if (!$version) {
            return;
        }

        $items = DB::table('survey_form_items')->where('Form_Version_ID', $version->ID)->where('Is_Active', 1)->get()->keyBy('Item_Code');
        $service = app(SurveyScoringService::class);

        DB::table('survey_location')->orderBy('ID')->chunkById(100, function ($surveys) use ($version, $items, $service) {
            foreach ($surveys as $survey) {
                $percentage = (float) ($survey->Total_Score ?? 0);
                $recommendation = $service->recommendation($version, $percentage);

                DB::table('survey_location')->where('ID', $survey->ID)->update([
                    'Survey_Form_Version_ID' => $version->ID,
                    'Max_Score' => $version->Max_Score,
                    'Score_Percentage' => $percentage,
                    'Recommendation' => $recommendation,
                ]);

                if (DB::table('survey_answers')->where('Survey_Location_ID', $survey->ID)->exists()) {
                    continue;
                }

                $answers = [];
                foreach ($items as $code => $item) {
                    $section = $item->Section_Code;
                    $value = null;
                    $notes = null;
                    $answerJson = ['value' => 0];
                    $score = 0;

                    if ($section === 'C' && preg_match('/^C(\d+)$/', $code, $m)) {
                        $value = (int) ($survey->{'C_c' . $m[1]} ?? 0);
                        $score = $value * 2.5;
                        $answerJson = ['value' => $value];
                    } elseif ($section === 'D' && preg_match('/^D(\d+)$/', $code, $m)) {
                        $value = (int) ($survey->{'D_c' . $m[1]} ?? 0);
                        $score = $value * ($m[1] == 9 ? 7 : 6);
                        $notes = $survey->{'D_c' . $m[1] . '_Notes'} ?? null;
                        $answerJson = ['value' => $value];
                    } elseif ($section === 'E' && preg_match('/^E(\d+)$/', $code, $m)) {
                        $value = (int) ($survey->{'E_p' . $m[1]} ?? 0);
                        $score = $value === 1 ? 1 : ($value === 2 ? 3 : ($value === 3 ? 5 : 0));
                        $answerJson = ['value' => $value];
                    } elseif ($section === 'F' && preg_match('/^F(\d+)$/', $code, $m)) {
                        $source = (float) ($survey->{'F_p' . $m[1] . '_col1'} ?? 0);
                        $uco = (float) ($survey->{'F_p' . $m[1] . '_col2'} ?? 0);
                        $value = $uco;
                        $answerJson = ['source_value' => $source, 'uco_estimation' => $uco, 'total' => $source * $uco];
                    }

                    DB::table('survey_answers')->insert([
                        'Survey_Location_ID' => $survey->ID,
                        'Form_Item_ID' => $item->ID,
                        'Item_Code' => $code,
                        'Section_Code' => $section,
                        'Criteria_Snapshot' => $item->Criteria,
                        'Keterangan_Snapshot' => $item->Keterangan,
                        'Minimum_Standard_Snapshot' => $item->Minimum_Standard,
                        'Parameter_Evaluation_Snapshot' => $item->Parameter_Evaluation,
                        'Answer_JSON' => json_encode($answerJson),
                        'Score' => $score,
                        'Notes' => $notes,
                        'File_Path' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }, 'ID');
    }
}
