<?php

if (!function_exists('can_menu')) {
    function can_menu(string $menuId, string $action = 'view'): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->can("menu.{$menuId}.{$action}");
    }
}

if (!function_exists('leads_interest_label')) {
    /**
     * Partnership package label for a lead ("Mobil Listrik" / "Booth").
     * Priority: opportunity_partner.package_option, then opportunity.Hub_Type,
     * then the legacy Partnership_Interest int (1 = mobil, 2 = booth).
     * Returns "-" when the package is unknown (never a bogus "Opsi 0").
     */
    function leads_interest_label($packageOption = null, $hubType = null, $partnershipInterest = null): string
    {
        foreach ([$packageOption, $hubType] as $code) {
            if ($code === 'mobil' || $code === 'option2') return 'Mobil Listrik';
            if ($code === 'booth' || $code === 'option1') return 'Booth';
        }

        $pi = (int) $partnershipInterest;
        if ($pi === 1) return 'Mobil Listrik';
        if ($pi === 2) return 'Booth';

        return '-';
    }
}

if (!function_exists('user_signature_path')) {
    /**
     * Absolute path of the user's latest stored signature image, or null.
     */
    function user_signature_path($userId): ?string
    {
        if (!$userId) {
            return null;
        }

        $user = \App\User::find($userId);
        if ($user && !empty($user->signature)) {
            $candidates = [
                public_path($user->signature),
                public_path('filevendor/' . ltrim($user->signature, '/')),
                public_path('uploads/' . ltrim($user->signature, '/')),
                $user->signature,
            ];
            foreach ($candidates as $cand) {
                if (file_exists($cand) && is_file($cand)) {
                    return $cand;
                }
            }
        }

        $meta = \Illuminate\Support\Facades\DB::table('meta_files')
            ->where('Topic', 'user-signature')
            ->where('Map_To', (string) $userId)
            ->orderByDesc('ID')
            ->first();

        if (!$meta) {
            return null;
        }

        $path = public_path('filevendor/' . $meta->Path);

        return file_exists($path) ? $path : null;
    }
}

if (!function_exists('apply_stored_signature')) {
    /**
     * Embed the user's stored signature into a PhpWord TemplateProcessor.
     * Returns true when an image was applied.
     */
    function apply_stored_signature($doc, string $placeholder, $userId): bool
    {
        $path = user_signature_path($userId);

        if (!$path) {
            return false;
        }

        $doc->setImageValue($placeholder, [
            'path' => $path,
            'height' => 100,
            'ratio' => false,
        ]);

        return true;
    }
}

if (!function_exists('employee_grid_columns')) {
    function employee_grid_columns(): array
    {
        return [
            'Task_ID' => ['NIK', 100, 'center', '#text_filter', 'str'],
            'NIK' => ['NIK KTP', 140, 'center', '#text_filter', 'str'],
            'NIK_Client' => ['NIK Client', 120, 'center', '#text_filter', 'str'],
            'Attendant_No' => ['Attendant No', 110, 'center', '#text_filter', 'str'],
            'Name' => ['Name', 170, 'left', '#text_filter', 'str'],
            'Initial' => ['Initial', 70, 'center', '#text_filter', 'str'],
            'Birth_Place' => ['Birth Place', 120, 'left', '#text_filter', 'str'],
            'Birth_Date' => ['Birth Date', 110, 'center', '#text_filter', 'date'],
            'Gender' => ['Gender', 90, 'center', '#combo_filter', 'str'],
            'Marital_Status' => ['Marital Status', 110, 'center', '#combo_filter', 'str'],
            'PTKP_Status' => ['PTKP Status', 100, 'center', '#combo_filter', 'str'],
            'kewarganegaraan' => ['Kewarganegaraan', 120, 'center', '#combo_filter', 'str'],
            'Last_Education' => ['Last Education', 120, 'center', '#combo_filter', 'str'],
            'Last_Education_From' => ['Last Education From', 160, 'left', '#text_filter', 'str'],
            'Education_Major' => ['Education Major', 160, 'left', '#text_filter', 'str'],
            'Religion' => ['Religion', 100, 'center', '#combo_filter', 'str'],
            'Blood' => ['Blood', 70, 'center', '#combo_filter', 'str'],
            'Blood_Rhesus' => ['Rhesus', 70, 'center', '#combo_filter', 'str'],
            'Status' => ['Status', 100, 'center', '#combo_filter', 'str'],
            'Address' => ['Address', 200, 'left', '#text_filter', 'str'],
            'City' => ['City', 120, 'left', '#text_filter', 'str'],
            'Address_Living' => ['Address Living', 200, 'left', '#text_filter', 'str'],
            'City_Living' => ['City Living', 120, 'left', '#text_filter', 'str'],
            'Phone' => ['Phone', 120, 'left', '#text_filter', 'str'],
            'Handphone' => ['Handphone', 130, 'left', '#text_filter', 'str'],
            'Email' => ['Email', 170, 'left', '#text_filter', 'str'],
            'Email_Individual' => ['Email Individual', 170, 'left', '#text_filter', 'str'],
            'KTP' => ['KTP', 140, 'center', '#text_filter', 'str'],
            'Foto' => ['Foto', 120, 'center', '#text_filter', 'str'],
            'Position' => ['Last Position', 150, 'center', '#combo_filter', 'str'],
            'Reason' => ['Reason', 150, 'left', '#text_filter', 'str'],
            'Location' => ['Location', 100, 'center', '#combo_filter', 'str'],
            'Alocation' => ['Alocation', 120, 'center', '#text_filter', 'str'],
            'Work_Area' => ['Work Area', 120, 'center', '#combo_filter', 'str'],
            'Department' => ['Department', 130, 'center', '#combo_filter', 'str'],
            'Directorat' => ['Directorat', 110, 'center', '#combo_filter', 'str'],
            'Priority' => ['Priority', 90, 'center', '#combo_filter', 'str'],
            'Created_Date' => ['Created Date', 110, 'center', '#text_filter', 'date'],
            'Created_By' => ['Created By', 120, 'center', '#text_filter', 'str'],
            'Registration_Status' => ['Registration Status', 130, 'center', '#combo_filter', 'str'],
            'Registration_Status_Date' => ['Registration Status Date', 150, 'center', '#text_filter', 'date'],
            'Registration_Status_Note' => ['Registration Status Note', 180, 'left', '#text_filter', 'str'],
            'Interview_Note' => ['Interview Note', 180, 'left', '#text_filter', 'str'],
            'Interview_Date' => ['Interview Date', 110, 'center', '#text_filter', 'date'],
            'Interview_Result' => ['Interview Result', 130, 'center', '#combo_filter', 'str'],
            'Interview_By' => ['Interview By', 130, 'center', '#text_filter', 'str'],
            'Employment_Status' => ['Employee Status', 120, 'center', '#combo_filter', 'str'],
            'Employment_Status_Date' => ['Employment Status Date', 150, 'center', '#text_filter', 'date'],
            'Employment_Class' => ['Employment Class', 120, 'center', '#combo_filter', 'str'],
            'Employment_Grade' => ['Employment Grade', 120, 'center', '#combo_filter', 'str'],
            'Salary_Accepted' => ['Salary Accepted', 130, 'right', '#text_filter', 'int'],
            'Contract_No' => ['Contract No', 130, 'center', '#text_filter', 'str'],
            'Subsection' => ['Subsection', 120, 'center', '#combo_filter', 'str'],
            'Start_Date' => ['Start Date', 110, 'center', '#text_filter', 'date'],
            'End_Date' => ['End Date', 110, 'center', '#text_filter', 'date'],
            'Date_in' => ['Date In', 110, 'center', '#text_filter', 'date'],
            'Note_In' => ['Note In', 150, 'left', '#text_filter', 'str'],
            'Date_Out' => ['Date Out', 110, 'center', '#text_filter', 'date'],
            'Note_Out' => ['Note Out', 150, 'left', '#text_filter', 'str'],
            'Salary_Group' => ['Salary Group', 110, 'center', '#combo_filter', 'str'],
            'Payment_Method' => ['Payment Method', 120, 'center', '#combo_filter', 'str'],
            'Payment_Reference' => ['Payment Reference', 150, 'center', '#text_filter', 'str'],
            'Payment_Reference2' => ['Payment Reference 2', 150, 'center', '#text_filter', 'str'],
            'Payment_Reference_Branch' => ['Payment Reference Branch', 170, 'center', '#text_filter', 'str'],
            'Payment_Reference3' => ['Payment Reference 3', 150, 'center', '#text_filter', 'str'],
            'Notes' => ['Notes', 180, 'left', '#text_filter', 'str'],
            'NPWP' => ['NPWP', 150, 'center', '#text_filter', 'str'],
            'PPH_Allowance' => ['PPH Allowance', 110, 'center', '#combo_filter', 'str'],
            'Sisa_Cuti' => ['Sisa Cuti', 90, 'center', '#numeric_filter', 'int'],
            'Category' => ['Category', 100, 'center', '#combo_filter', 'str'],
            'Prorate_Salary' => ['Prorate Salary', 120, 'center', '#text_filter', 'str'],
            'Prorate_Salary_Date' => ['Prorate Salary Date', 140, 'center', '#text_filter', 'date'],
            'Prorate_Resigned' => ['Prorate Resigned', 120, 'center', '#text_filter', 'str'],
            'Prorate_Resigned_Date' => ['Prorate Resigned Date', 150, 'center', '#text_filter', 'date'],
            'Resigned_Date' => ['Resigned Date', 110, 'center', '#text_filter', 'date'],
            'Resigned_Note' => ['Resigned Note', 170, 'left', '#text_filter', 'str'],
            'Recruitment_ID' => ['Recruitment ID', 120, 'center', '#text_filter', 'str'],
            'Recruitment_Status' => ['Recruitment Status', 130, 'center', '#combo_filter', 'str'],
            'Address_Living_City' => ['Address Living City', 140, 'left', '#text_filter', 'str'],
            'bpjs_tk' => ['BPJS TK', 130, 'center', '#text_filter', 'str'],
            'bpjs_kes' => ['BPJS Kes', 130, 'center', '#text_filter', 'str'],
            'Conversion_Date' => ['Conversion Date', 120, 'center', '#text_filter', 'date'],
            'Mutation_File' => ['Mutation File', 140, 'center', '#text_filter', 'str'],
            'Reference_No' => ['Reference No', 130, 'center', '#text_filter', 'str'],
            'Employee_Type' => ['Employee Type', 120, 'center', '#combo_filter', 'str'],
            'Age' => ['Age', 70, 'center', '#numeric_filter', 'int'],
            'Academic_Major' => ['Last Education Major', 160, 'left', '#text_filter', 'str'],
        ];
    }
}

if (!function_exists('employee_grid_default_columns')) {
    function employee_grid_default_columns(): array
    {
        return ['Name', 'Task_ID', 'Gender', 'Marital_Status', 'Age', 'Position', 'Department', 'Directorat', 'Employment_Status', 'Location', 'Last_Education', 'Academic_Major'];
    }
}

if (!function_exists('employee_grid_cell')) {
    function employee_grid_cell(string $key, $row, array $akademis = []): string
    {
        if ($key === 'Age') {
            $b = isset($row->Birth_Date) ? $row->Birth_Date : '';
            if ($b !== '' && $b !== '0000-00-00') {
                return (string) floor((time() - strtotime($b)) / 31556926);
            }
            return '';
        }
        if ($key === 'Academic_Major') {
            $tid = isset($row->Task_ID) ? $row->Task_ID : null;
            return isset($akademis[$tid]) ? (string) $akademis[$tid] : '';
        }
        return isset($row->$key) ? (string) $row->$key : '';
    }
}

if (!function_exists('employee_grid_column_admins')) {
    function employee_grid_column_admins(): array
    {
        return ['admin', 'admin.hris', 'user.hcga'];
    }
}

if (!function_exists('employee_grid_can_manage_columns')) {
    function employee_grid_can_manage_columns(): bool
    {
        if (!auth()->check()) {
            return false;
        }
        return in_array(auth()->user()->username, employee_grid_column_admins(), true);
    }
}

if (!function_exists('employee_pivot_fields')) {
    function employee_pivot_fields(): array
    {
        return [
            'Department' => 'Department',
            'Directorat' => 'Directorat',
            'Location' => 'Location',
            'Position' => 'Last Position',
            'Employment_Status' => 'Employee Status',
            'Employment_Class' => 'Employment Class',
            'Employment_Grade' => 'Employment Grade',
            'Gender' => 'Gender',
            'Marital_Status' => 'Marital Status',
            'Last_Education' => 'Last Education',
            'Religion' => 'Religion',
            'Category' => 'Category',
            'Employee_Type' => 'Employee Type',
            'Work_Area' => 'Work Area',
            'Subsection' => 'Subsection',
        ];
    }
}


if (!function_exists('requisition_manager_levels')) {
    function requisition_manager_levels(): array
    {
        return [1, 2, 3];
    }
}

if (!function_exists('requisition_approval_chain')) {
    function requisition_approval_chain(string $location, string $dept, int $posLevel): array
    {
        $db = '\\Illuminate\\Support\\Facades\\DB';
        $isManagerUp = in_array($posLevel, requisition_manager_levels(), true);
        $steps = [];

        $mgrDept = $db::table('master_hr_approval')
            ->where('Level', 3)->where('Location', $location)->where('Department', $dept)->first();
        if (!$mgrDept) {
            return ['chain' => [], 'error' => 'Manager departemen ' . $dept . ' belum terdaftar di master_hr_approval.'];
        }
        $steps[] = ['Level' => 1, 'Approved_By' => $mgrDept->username, 'Role' => 'Manager ' . $dept];

        if ($isManagerUp) {
            $vpDept = $db::table('master_hr_approval')
                ->where('Level', 2)->where('Location', $location)->where('Department', $dept)->first();
            if (!$vpDept) {
                return ['chain' => [], 'error' => 'VP departemen ' . $dept . ' belum terdaftar di master_hr_approval (Level 2).'];
            }
            $steps[] = ['Level' => 2, 'Approved_By' => $vpDept->username, 'Role' => 'VP ' . $dept];
        }

        $mgrHcga = $db::table('master_hr_approval')
            ->where('Level', 3)->where('Location', $location)->where('Department', 'HCGA')->first();
        if (!$mgrHcga) {
            return ['chain' => [], 'error' => 'Manager HCGA belum terdaftar di master_hr_approval.'];
        }
        $steps[] = ['Level' => 3, 'Approved_By' => $mgrHcga->username, 'Role' => 'Manager HCGA'];

        if ($isManagerUp) {
            $vpGeu = $db::table('master_hr_approval')
                ->where('Level', 1)->where('Location', $location)->first();
            if (!$vpGeu) {
                return ['chain' => [], 'error' => 'VP Geu belum terdaftar di master_hr_approval (Level 1).'];
            }
            $steps[] = ['Level' => 4, 'Approved_By' => $vpGeu->username, 'Role' => 'VP Geu'];
        }

        $chain = [];
        $seen = [];
        foreach ($steps as $s) {
            if (in_array($s['Approved_By'], $seen, true)) {
                continue;
            }
            $seen[] = $s['Approved_By'];
            $chain[] = $s;
        }
        foreach ($chain as $i => $s) {
            $chain[$i]['Level'] = $i + 1;
        }
        return ['chain' => $chain, 'error' => null];
    }
}

if (!function_exists('asset_photo_topic')) {
    /**
     * meta_files.Topic used for every asset_list photo. Photos are append-only rows
     * (one row per file) so an asset can hold more than one picture; asset_list.Photo
     * caches the cover (first) photo so grids/QR pages keep working.
     */
    function asset_photo_topic(): string
    {
        return 'asset-photo';
    }
}

if (!function_exists('asset_photos')) {
    /**
     * All photos of one asset, oldest first. $assetNo is asset_list.Asset_No.
     */
    function asset_photos($assetNo)
    {
        if (!$assetNo) {
            return collect();
        }

        return \Illuminate\Support\Facades\DB::table('meta_files')
            ->where('Topic', asset_photo_topic())
            ->where('Map_To', (string) $assetNo)
            ->orderBy('ID', 'ASC')
            ->get();
    }
}

if (!function_exists('asset_photo_url')) {
    /**
     * Public URL of a meta_files row, or null when the file is missing on disk.
     */
    function asset_photo_url($meta): ?string
    {
        return $meta && $meta->Path ? asset('filevendor/' . $meta->Path) : null;
    }
}

if (!function_exists('asset_photo_sync_cover')) {
    /**
     * Keep asset_list.Photo pointing at the first photo of the asset (cover).
     * Never called on delete-with-remaining-rows without a re-read, so the cover
     * always follows the photos table.
     */
    function asset_photo_sync_cover($assetNo): void
    {
        if (!$assetNo) {
            return;
        }

        $first = asset_photos($assetNo)->first();

        \Illuminate\Support\Facades\DB::table('asset_list')
            ->where('Asset_No', (string) $assetNo)
            ->update(['Photo' => $first ? asset_photo_url($first) : null]);
    }
}

if (!function_exists('asset_photo_store')) {
    /**
     * Store one uploaded photo for an asset and register it in meta_files.
     * Returns the meta_files ID. Mirrors UploadService::storeFile() (filevendor/YYYY/MM).
     */
    function asset_photo_store($file, $assetNo, string $description = 'Asset Photo'): ?int
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        $year = now()->format('Y');
        $month = now()->format('m');
        $dir = public_path("filevendor/{$year}/{$month}");

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        $name = 'asset_' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $assetNo)
            . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $file->move($dir, $name);

        return \Illuminate\Support\Facades\DB::table('meta_files')->insertGetId([
            'Filename'    => $name,
            'Path'        => "{$year}/{$month}/{$name}",
            'Extension'   => $ext,
            'Description' => $description,
            'Topic'       => asset_photo_topic(),
            'Map_To'      => (string) $assetNo,
            'Created_At'  => now(),
        ]);
    }
}

if (!function_exists('asset_photo_delete')) {
    /**
     * Delete one photo row (and its file) then refresh the asset cover.
     * Deleting a photo never touches the other photos of the asset.
     */
    function asset_photo_delete($photoId, $assetNo = null): bool
    {
        $photo = \Illuminate\Support\Facades\DB::table('meta_files')
            ->where('ID', $photoId)
            ->where('Topic', asset_photo_topic())
            ->first();

        if (!$photo) {
            return false;
        }

        $fullPath = public_path('filevendor/' . $photo->Path);
        if ($photo->Path && file_exists($fullPath)) {
            @unlink($fullPath);
        }

        \Illuminate\Support\Facades\DB::table('meta_files')->where('ID', $photoId)->delete();

        asset_photo_sync_cover($assetNo ?: $photo->Map_To);

        return true;
    }
}

if (!function_exists('org_chart_level_of')) {
    /**
     * Level struktural satu baris organization_structure.
     * Angka kecil = jabatan lebih tinggi.
     */
    function org_chart_level_of($row): int
    {
        // organization_structure versi bagan baru menyimpan Level langsung.
        return (int) ($row->Level ?? 8);
    }
}

if (!function_exists('org_chart_nodes')) {
    /**
     * Bangun daftar node bagan struktur organisasi dari organization_structure.
     *
     * Atasan (Parent_ID) memakai Reports_To_Position_ID. Bila kosong, dicari
     * atasan terdekat dengan Level lebih kecil di departemen yang sama.
     *
     * $opts: department, directorat, include_system
     */
    function org_chart_nodes(array $opts = []): array
    {
        $db = '\Illuminate\Support\Facades\DB';

        $query = $db::table('organization_structure as o')
            ->select(
                'o.ID',
                'o.Position_ID',
                'o.NIK',
                'o.Name',
                'o.Position',
                'o.Department',
                'o.Dept_Name',
                'o.Directorat',
                'o.Email',
                'o.Is_System',
                'o.Reports_To_Position_ID',
                'o.Sort_Order',
                'o.Level'
            );

        if (empty($opts['include_system'])) {
            $query->where('o.Is_System', 0);
        }
        if (!empty($opts['directorat'])) {
            $query->where('o.Directorat', $opts['directorat']);
        }
        if (!empty($opts['department'])) {
            $query->where('o.Department', $opts['department']);
        }

        $rows = $query->orderBy('o.Sort_Order')->orderBy('o.ID')->get();

        $nodes = [];
        foreach ($rows as $r) {
            $level = (int) ($r->Level ?? 8);
            $nodes[(int) $r->ID] = [
                'ID'          => (int) $r->ID,
                'Position_ID' => (int) ($r->Position_ID ?? 0),
                'NIK'         => (string) ($r->NIK ?? ''),
                'Name'        => (string) ($r->Name ?? ''),
                'Position'    => (string) ($r->Position ?: '-'),
                'Department'  => (string) ($r->Department ?? ''),
                'Dept_Name'   => (string) ($r->Dept_Name ?: $r->Department),
                'Directorat'  => (string) ($r->Directorat ?: '-'),
                'Email'       => (string) ($r->Email ?: ''),
                'Level'       => $level,
                'Level_Name'  => org_chart_level_name($level),
                'Is_System'   => (int) ($r->Is_System ?? 0),
                'Parent_ID'   => !empty($r->Reports_To_Position_ID) ? (int) $r->Reports_To_Position_ID : null,
                'Children'    => [],
            ];
        }

        if (empty($nodes)) {
            return ['nodes' => [], 'roots' => [], 'issues' => []];
        }

        // Lengkapi atasan yang belum diisi / menunjuk node di luar filter.
        foreach ($nodes as $id => $n) {
            if ($n['Parent_ID'] !== null && isset($nodes[$n['Parent_ID']]) && $n['Parent_ID'] !== $id) {
                continue;
            }

            $bestId = null;
            $bestLevel = -1;
            foreach ($nodes as $cid => $cand) {
                if ($cid === $id || $cand['Department'] !== $n['Department']) {
                    continue;
                }
                if ($cand['Level'] >= $n['Level']) {
                    continue;
                }
                if ($cand['Level'] > $bestLevel) {
                    $bestLevel = $cand['Level'];
                    $bestId = $cid;
                }
            }

            $nodes[$id]['Parent_ID'] = $bestId;
        }

        $roots = [];
        foreach ($nodes as $id => $n) {
            $parentId = $n['Parent_ID'];
            if ($parentId === null || !isset($nodes[$parentId])) {
                $roots[] = $id;
            } else {
                $nodes[$parentId]['Children'][] = $id;
            }
        }

        $sorter = function ($a, $b) use (&$nodes) {
            if ($nodes[$a]['Level'] !== $nodes[$b]['Level']) {
                return $nodes[$a]['Level'] <=> $nodes[$b]['Level'];
            }
            return strcasecmp($nodes[$a]['Name'], $nodes[$b]['Name']);
        };
        foreach ($nodes as $id => $n) {
            if (!empty($nodes[$id]['Children'])) {
                usort($nodes[$id]['Children'], $sorter);
            }
        }
        usort($roots, $sorter);

        $issues = [];
        $nikCounts = [];
        foreach ($nodes as $n) {
            $nik = trim((string) $n['NIK']);
            if ($nik !== '' && strtoupper(trim($n['Name'])) !== 'VACANT') {
                $nikCounts[$nik] = ($nikCounts[$nik] ?? 0) + 1;
            }
            if ($n['Position'] === '-') {
                $issues[] = ['type' => 'NO_POSITION', 'message' => $n['Name'] . ' belum punya jabatan'];
            }
        }
        foreach ($nikCounts as $nik => $count) {
            if ($count > 1) {
                $issues[] = ['type' => 'DUPLICATE_NIK', 'message' => "NIK $nik dipakai $count baris"];
            }
        }
        if (count($roots) > 1) {
            $issues[] = ['type' => 'MULTI_ROOT', 'message' => count($roots) . ' node tidak punya atasan (multi-root)'];
        }

        return ['nodes' => $nodes, 'roots' => $roots, 'issues' => $issues];
    }
}

if (!function_exists('org_chart_level_name')) {
    /**
     * Label level dari master level_general (fallback statis).
     */
    function org_chart_level_name(int $level): string
    {
        static $cache = null;

        if ($cache === null) {
            $cache = [];
            try {
                foreach (\Illuminate\Support\Facades\DB::table('level_general')->get() as $l) {
                    $cache[(int) $l->Level] = (string) $l->Techincal;
                }
            } catch (\Throwable $e) {
                $cache = [];
            }
        }

        return $cache[$level] ?? ('Level ' . $level);
    }
}

if (!function_exists('org_chart_departments')) {
    /**
     * Daftar departemen yang punya data di bagan (untuk filter).
     */
    function org_chart_departments(): array
    {
        try {
            return \Illuminate\Support\Facades\DB::table('organization_structure')
                ->select('Department')
                ->distinct()
                ->whereNotNull('Department')
                ->where('Department', '<>', '')
                ->orderBy('Department')
                ->pluck('Department')
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('org_chart_directorats')) {
    /**
     * Daftar direktorat yang punya data di bagan (untuk filter).
     */
    function org_chart_directorats(): array
    {
        try {
            return \Illuminate\Support\Facades\DB::table('organization_structure')
                ->select('Directorat')
                ->distinct()
                ->whereNotNull('Directorat')
                ->where('Directorat', '<>', '')
                ->orderBy('Directorat')
                ->pluck('Directorat')
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('recruitment_management_columns')) {
    function recruitment_management_columns(): array
    {
        return [
            'Recruitment_Type' => ['Recruitment Type', 120, 'left', '#combo_filter', 'str'],
            'Name' => ['Name', 170, 'left', '#text_filter', 'str'],
            'Gender' => ['Gender', 80, 'center', '#combo_filter', 'str'],
            'PO_No' => ['PO No', 100, 'center', '#text_filter', 'str'],
            'Requisition' => ['Requisition / Job Position', 180, 'left', '#text_filter', 'str'],
            'Last_Education' => ['Last Education', 110, 'center', '#combo_filter', 'str'],
            'Education_Major' => ['Education Major', 150, 'left', '#text_filter', 'str'],
            'Interview_By' => ['Recruiter / Interviewer', 130, 'left', '#text_filter', 'str'],
            'Doc_Status' => ['Seleksi Administrasi', 120, 'center', '#combo_filter', 'str'],
            'Psikotest_Status' => ['Psikotest', 100, 'center', '#combo_filter', 'str'],
            'Decline_Invitation' => ['Decline Invitation', 110, 'center', '#combo_filter', 'str'],
            'Interview_HR' => ['Interview HR', 110, 'center', '#combo_filter', 'str'],
            'Pending_Status' => ['Pending', 90, 'center', '#combo_filter', 'str'],
            'Reject_Status' => ['Rejected', 90, 'center', '#combo_filter', 'str'],
            'Interview_User' => ['Interview User', 110, 'center', '#combo_filter', 'str'],
            'Candidate_Analysis' => ['Candidate Analysis', 120, 'center', '#combo_filter', 'str'],
            'Decline_Offered' => ['Decline Offered', 110, 'center', '#combo_filter', 'str'],
            'Hired_Status' => ['Hired', 90, 'center', '#combo_filter', 'str'],
        ];
    }
}

if (!function_exists('recruitment_management_default_columns')) {
    function recruitment_management_default_columns(): array
    {
        return [
            'Recruitment_Type',
            'Name',
            'Gender',
            'PO_No',
            'Requisition',
            'Last_Education',
            'Education_Major',
            'Interview_By',
            'Doc_Status',
            'Psikotest_Status',
            'Decline_Invitation',
            'Interview_HR',
            'Pending_Status',
            'Reject_Status',
            'Interview_User',
            'Candidate_Analysis',
            'Decline_Offered',
            'Hired_Status',
        ];
    }
}

if (!function_exists('recruitment_data_columns')) {
    function recruitment_data_columns(): array
    {
        return [
            'KTP' => ['No. KTP', 140, 'center', '#text_filter', 'str'],
            'NIK' => ['NIK', 120, 'center', '#text_filter', 'str'],
            'Task_ID' => ['Task ID', 140, 'center', '#text_filter', 'str'],
            'Name' => ['Nama Kandidat', 170, 'left', '#text_filter', 'str'],
            'Initial' => ['Initial', 80, 'center', '#text_filter', 'str'],
            'Gender' => ['Jenis Kelamin', 90, 'center', '#combo_filter', 'str'],
            'Birth_Place' => ['Tempat Lahir', 120, 'left', '#text_filter', 'str'],
            'Birth_Date' => ['Tanggal Lahir', 110, 'center', '#text_filter', 'date'],
            'Age' => ['Usia', 70, 'center', '#numeric_filter', 'int'],
            'Marital_Status' => ['Status Nikah', 110, 'center', '#combo_filter', 'str'],
            'PTKP_Status' => ['Status PTKP', 100, 'center', '#combo_filter', 'str'],
            'kewarganegaraan' => ['Kewarganegaraan', 120, 'center', '#combo_filter', 'str'],
            'Religion' => ['Agama', 100, 'center', '#combo_filter', 'str'],
            'Blood' => ['Gol. Darah', 70, 'center', '#combo_filter', 'str'],
            'Blood_Rhesus' => ['Rhesus', 70, 'center', '#combo_filter', 'str'],
            'Requisition' => ['Posisi / Requisition', 180, 'left', '#text_filter', 'str'],
            'Position' => ['Posisi Dilamar', 160, 'left', '#text_filter', 'str'],
            'Department' => ['Departemen', 130, 'center', '#combo_filter', 'str'],
            'Directorat' => ['Direktorat', 120, 'center', '#combo_filter', 'str'],
            'Location' => ['Lokasi', 100, 'center', '#combo_filter', 'str'],
            'Priority' => ['Prioritas', 90, 'center', '#combo_filter', 'str'],
            'Recruitment_Type' => ['Tipe Rekrutmen', 120, 'center', '#combo_filter', 'str'],
            'Last_Education' => ['Pendidikan Terakhir', 110, 'center', '#combo_filter', 'str'],
            'Education_Major' => ['Jurusan', 150, 'left', '#text_filter', 'str'],
            'Last_Education_From' => ['Institusi / Universitas', 170, 'left', '#text_filter', 'str'],
            'Phone' => ['No. Telepon', 120, 'left', '#text_filter', 'str'],
            'Handphone' => ['No. Handphone', 130, 'left', '#text_filter', 'str'],
            'Email' => ['Email', 170, 'left', '#text_filter', 'str'],
            'Email_Individual' => ['Email Pribadi', 170, 'left', '#text_filter', 'str'],
            'Address' => ['Alamat KTP', 200, 'left', '#text_filter', 'str'],
            'City' => ['Kota KTP', 120, 'left', '#text_filter', 'str'],
            'Address_Living' => ['Alamat Domisili', 200, 'left', '#text_filter', 'str'],
            'City_Living' => ['Kota Domisili', 120, 'left', '#text_filter', 'str'],
            'NPWP' => ['NPWP', 140, 'center', '#text_filter', 'str'],
            'BPJS_Kesehatan' => ['BPJS Kesehatan', 130, 'center', '#text_filter', 'str'],
            'BPJS_TK' => ['BPJS Ketenagakerjaan', 130, 'center', '#text_filter', 'str'],
            'Salary_Accepted' => ['Ekspektasi Gaji', 130, 'right', '#numeric_filter', 'int'],
            'Status' => ['Status Rekrutmen', 130, 'center', '#combo_filter', 'str'],
            'Document_Status' => ['Status Administrasi', 120, 'center', '#combo_filter', 'str'],
            'Document_Date' => ['Tgl Administrasi', 110, 'center', '#text_filter', 'date'],
            'Psikotest_Status' => ['Status Psikotes', 110, 'center', '#combo_filter', 'str'],
            'Psikotest_Score' => ['Nilai Psikotes', 100, 'center', '#numeric_filter', 'int'],
            'Psikotest_Date' => ['Tgl Psikotes', 110, 'center', '#text_filter', 'date'],
            'Interview_By' => ['Pewawancara HR', 140, 'left', '#text_filter', 'str'],
            'Interview_Status' => ['Status Interview HR', 120, 'center', '#combo_filter', 'str'],
            'Interview_Date' => ['Tgl Interview HR', 110, 'center', '#text_filter', 'date'],
            'User_Interview_By' => ['Pewawancara User', 140, 'left', '#text_filter', 'str'],
            'User_Interview_Status' => ['Status Interview User', 120, 'center', '#combo_filter', 'str'],
            'User_Interview_Date' => ['Tgl Interview User', 110, 'center', '#text_filter', 'date'],
            'Technical_Test_Status' => ['Status Tes Teknik', 120, 'center', '#combo_filter', 'str'],
            'Technical_Score' => ['Nilai Tes Teknik', 100, 'center', '#numeric_filter', 'int'],
            'Technical_Date' => ['Tgl Tes Teknik', 110, 'center', '#text_filter', 'date'],
            'MCU_Status' => ['Status MCU', 110, 'center', '#combo_filter', 'str'],
            'MCU_Schedule' => ['Jadwal MCU', 110, 'center', '#text_filter', 'date'],
            'Offering_Status' => ['Status Offering', 110, 'center', '#combo_filter', 'str'],
            'PO_Desc' => ['PO Description', 140, 'left', '#text_filter', 'str'],
            'Remaining_Days' => ['Sisa Waktu (Hari)', 110, 'center', '#numeric_filter', 'int'],
            'Created_Date' => ['Tanggal Masuk', 110, 'center', '#text_filter', 'date'],
            'Created_By' => ['Dibuat Oleh', 120, 'center', '#text_filter', 'str'],
        ];
    }
}

if (!function_exists('recruitment_data_default_columns')) {
    function recruitment_data_default_columns(): array
    {
        return [
            'KTP',
            'Name',
            'Gender',
            'Birth_Date',
            'Requisition',
            'Last_Education',
            'Education_Major',
            'Last_Education_From',
            'Handphone',
            'Email',
            'Status',
            'Document_Status',
            'Psikotest_Status',
            'Interview_Status',
            'User_Interview_Status',
            'Offering_Status',
            'Remaining_Days',
        ];
    }
}
if (!function_exists('cns_eligible_approvers')) {
    /**
     * Active users eligible to approve CNS export documents (SI, FSI, PL)
     * who have a valid signature on file.
     */
    function cns_eligible_approvers()
    {
        try {
            return \App\User::where('Status', 'ACTIVE')
                ->orderBy('Name')
                ->get()
                ->filter(function ($u) {
                    $isApproverRole = $u->username === 'admin'
                        || (method_exists($u, 'hasRole') && ($u->hasRole('ADM') || $u->hasRole('super-admin')))
                        || in_array($u->Priv_ID ?? '', ['ADM', 'CNS', 'SPV', 'MANAGER'])
                        || (method_exists($u, 'can') && ($u->can('menu.SI00307.approve') || $u->can('cns.shipment_instruction.approve')));

                    return $isApproverRole && !empty(user_signature_path($u->id));
                })
                ->values();
        } catch (\Throwable $e) {
            return collect();
        }
    }
}

if (!function_exists('terbilang_id')) {
    /**
     * Konversi angka nominal ke kalimat Terbilang Bahasa Indonesia.
     *
     * @param float|int $n
     * @return string
     */
    function terbilang_id($n)
    {
        $penyebut = function ($nilai) use (&$penyebut) {
            $nilai = abs($nilai);
            $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
            $temp = '';
            if ($nilai < 12) {
                $temp = ' ' . $huruf[(int)$nilai];
            } elseif ($nilai < 20) {
                $temp = $penyebut($nilai - 10) . ' Belas';
            } elseif ($nilai < 100) {
                $temp = $penyebut($nilai / 10) . ' Puluh' . $penyebut($nilai % 10);
            } elseif ($nilai < 200) {
                $temp = ' Seratus' . $penyebut($nilai - 100);
            } elseif ($nilai < 1000) {
                $temp = $penyebut($nilai / 100) . ' Ratus' . $penyebut($nilai % 100);
            } elseif ($nilai < 2000) {
                $temp = ' Seribu' . $penyebut($nilai - 1000);
            } elseif ($nilai < 1000000) {
                $temp = $penyebut($nilai / 1000) . ' Ribu' . $penyebut($nilai % 1000);
            } elseif ($nilai < 1000000000) {
                $temp = $penyebut($nilai / 1000000) . ' Juta' . $penyebut(fmod($nilai, 1000000));
            } elseif ($nilai < 1000000000000) {
                $temp = $penyebut($nilai / 1000000000) . ' Miliar' . $penyebut(fmod($nilai, 1000000000));
            } elseif ($nilai < 1000000000000000) {
                $temp = $penyebut($nilai / 1000000000000) . ' Triliun' . $penyebut(fmod($nilai, 1000000000000));
            }
            return $temp;
        };

        $teks = trim($penyebut($n));
        return $teks ? ($teks . ' Rupiah') : 'Nol Rupiah';
    }
}

if (!function_exists('terbilang_en')) {
    /**
     * Number to English words.
     *
     * @param float|int $n
     * @return string
     */
    function terbilang_en($n)
    {
        $n = abs($n);
        if ($n < 10) {
            switch ((int)$n) {
                case 0: return 'Zero';
                case 1: return 'One';
                case 2: return 'Two';
                case 3: return 'Three';
                case 4: return 'Four';
                case 5: return 'Five';
                case 6: return 'Six';
                case 7: return 'Seven';
                case 8: return 'Eight';
                case 9: return 'Nine';
            }
        } elseif ($n < 100) {
            $tens = (int)floor($n / 10);
            $units = (int)($n % 10);
            $words = [
                10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
                15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
            ];
            if ($tens == 1) {
                return ($words[(int)$n] ?? 'Ten') . ' Rupiah';
            }
            $tensWords = [2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty', 6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'];
            $res = $tensWords[$tens] ?? '';
            if ($units > 0) {
                $res .= ' ' . terbilang_en($units);
            }
            return $res;
        } elseif ($n < 1000) {
            $hundreds = (int)floor($n / 100);
            $rem = (int)($n % 100);
            $res = terbilang_en($hundreds) . ' Hundred';
            if ($rem > 0) {
                $res .= ' and ' . terbilang_en($rem);
            }
            return $res;
        } elseif ($n < 1000000) {
            $thousands = (int)floor($n / 1000);
            $rem = (int)($n % 1000);
            $res = terbilang_en($thousands) . ' Thousand';
            if ($rem > 0) {
                $res .= ' ' . terbilang_en($rem);
            }
            return $res;
        } elseif ($n < 1000000000) {
            $millions = (int)floor($n / 1000000);
            $rem = (int)fmod($n, 1000000);
            $res = terbilang_en($millions) . ' Million';
            if ($rem > 0) {
                $res .= ' ' . terbilang_en($rem);
            }
            return $res;
        } elseif ($n < 1000000000000) {
            $billions = (int)floor($n / 1000000000);
            $rem = (int)fmod($n, 1000000000);
            $res = terbilang_en($billions) . ' Billion';
            if ($rem > 0) {
                $res .= ' ' . terbilang_en($rem);
            }
            return $res;
        }

        return (string)$n;
    }
}

