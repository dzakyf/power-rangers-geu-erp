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
        return ['admin', 'admin.hris'];
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