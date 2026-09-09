<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    XML Generator untuk Recruitment Management Grid
    Digunakan oleh dhtmlxGrid di /hrd-recruitment-management
    
    Alur Recruitment yang benar:
    1. Input Candidate (CREATED) → 2. Verifikasi Dokumen (Document_Status=PENDING→ACCEPT) 
    → 3. Psikotest (Psikotest_Status) → 4. Interview HR → 5. Interview User 
    → 6. Tes Teknik → 7. MCU → 8. Offering Letter
    
    Visual Indicator per tahap:
    - Pending/Abai   : Abu-abu (#e2e3e5)
    - Scheduled/Proses: Kuning (#fff3cd) 
    - Selesai/Pass   : Hijau (#d4edda)
    - Ditolak/Fail   : Merah (#f8d7da)
-->
<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

header("Content-type: text/xml");

$LSP   = Auth::user()->Location ?? 'GEU-HO';
$nomor = 1;

// Urutan tahapan yang benar (sesuai spec)
$stages = [
    'Document_Status'      => ['label' => 'Seleksi Administrasi', 'color' => 'Document'],
    'Psikotest_Status'     => ['label' => 'Psikotest',            'color' => 'Psikotest'],
    'Interview_Status'     => ['label' => 'Interview HR',         'color' => 'Interview'],
    'User_Interview_Status'=> ['label' => 'Interview User',       'color' => 'Interview'],
    'Technical_Test_Status'=> ['label' => 'Tes Teknik',           'color' => 'Technical'],
    'MCU_Status'           => ['label' => 'MCU',                  'color' => 'MCU'],
    'Offering_Status'      => ['label' => 'Offering Letter',      'color' => 'Offering'],
];

// Color mapping untuk visual indicator
$statusColors = [
    // Status tahapan (berdasarkan field-by-field)
    'ACCEPT'  => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Selesai'],  // Hijau - diterima/lewat
    'PENDING' => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Pending'],  // Abu-abu - belum dicek
    'REJECT'  => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Ditolak'], // Merah - ditolak
    'SEEK'    => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Process'],  // Kuning - dalam proses
    
    // Status utama recruitment
    'CREATED'          => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Baru'],
    'UPLOADED'        => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Uploaded'],
    'PSIKOTEST'       => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Psikotest'],
    'INTERVIEW_HR'    => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Interview HR'],
    'INTERVIEW_USER'  => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Interview User'],
    'TECHNICAL_TEST'  => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Tes Teknik'],
    'MCU'             => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'MCU'],
    'OFFERING'        => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Offering'],
    'HIRED'           => ['bg' => '#28a745', 'text' => '#fff', 'label' => 'Hired ✓'],
    'PENDING'         => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Pending'],
    'BUSINESS_REVIEW' => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Review'],
    'CLOSED'          => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Closed'],
    'REJECT'          => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Ditolak'],
    'OFFER DECLINED'  => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Declined'],
    'INVITATION DECLINED' => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Inv. Declined'],
    'CANCELED'        => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Canceled'],
];

// Custom CSS class untuk enhanced styling
$customStyles = '
    <style>
        .grid-status-pending    { background-color: #e2e3e5 !important; color: #383d41 !important; font-weight: normal; }
        .grid-status-scheduled  { background-color: #fff3cd !important; color: #856404 !important; font-weight: normal; }
        .grid-status-process    { background-color: #cce5ff !important; color: #004085 !important; font-weight: normal; }
        .grid-status-done       { background-color: #d4edda !important; color: #155724 !important; font-weight: bold; }
        .grid-status-reject     { background-color: #f8d7da !important; color: #721c24 !important; font-weight: bold; }
        .grid-status-hired      { background-color: #28a745 !important; color: #ffffff !important; font-weight: bold; }
        
        /* Tooltip style */
        .grid-cell-tooltip {
            position: relative;
            display: inline-block;
        }
        .grid-cell-tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
        }
    </style>
';

echo $customStyles;
echo "<?xml version='1.0' encoding='UTF-8'?>\n";
echo "<rows>";

$query = DB::table('recruitment')
    ->where('Location', $LSP)
    ->where('Status', '!=', 'CANCELED')
    ->orderBy('ID', 'DESC')
    ->get();

// Ambil semua requisition ID unik
$reqIds = $query->pluck('Requisition_ID')
    ->flatMap(function ($r) {
        return array_filter(explode(',', (string) $r));
    })
    ->unique()
    ->values();

$requisitions = DB::table('requisition')
    ->whereIn('Task_ID', $reqIds)
    ->get()
    ->keyBy('Task_ID');

foreach ($query as $Fetch) {
    $pecahin = explode(',', (string) $Fetch->Requisition_ID);
    
    foreach ($pecahin as $pecahreq) {
        $Requisition = $requisitions->get($pecahreq);
        if (!$Requisition) continue;
        
        // ---- DETERMINE STATUS FOR EACH STAGE ----
        // Status berdasarkan field-by-field (bukan hanya kolom Status utama)
        $stageStatuses = [];
        $stageDates    = [];
        $stageNotes    = [];
        
        foreach ($stages as $field => $stageInfo) {
            $statusValue = $Fetch->$field ?? 'PENDING';
            $dateValue   = $Fetch->{$stageInfo['color'] . '_Date'} ?? 
                           $Fetch->{$stageInfo['color'] . '_Schedule'} ?? null;
            $noteValue   = $Fetch->{$stageInfo['color'] . '_Note'} ?? null;
            
            $stageStatuses[$field] = $statusValue;
            $stageDates[$field]    = $dateValue;
            $stageNotes[$field]    = $noteValue;
        }
        
        // ---- STATUS INDICATORS (1/0 dengan warna) ----
        // Setiap stage: 1 = selesai (hijau), 0 = belum (abu), reject = merah
        
        // Helper function untuk determine cell content (label teks + progress indicator)
        $getStageCell = function($status, $isDone) use ($statusColors) {
            $color = $statusColors[$status] ?? $statusColors['PENDING'];
            $label = $color['label'];
            $style = "background-color: {$color['bg']}; color: {$color['text']};";
            
            // Progress indicator: full (lebar) jika done, half jika process, empty jika pending
            if ($isDone) {
                $progress = '<div style="width:100%;height:8px;background:#28a745;border-radius:4px;margin-top:2px;"></div>';
            } elseif ($status === 'SEEK') {
                $progress = '<div style="width:50%;height:8px;background:#ffc107;border-radius:4px;margin-top:2px;"></div>';
            } else {
                $progress = '<div style="width:0%;height:8px;background:#adb5bd;border-radius:4px;margin-top:2px;"></div>';
            }
            
            return "<cell style=\"{$style}\">
                <cell_inner>
                    <div style=\"display:flex;flex-direction:column;align-items:center;min-width:50px;\">
                        <span>{$label}</span>
                        {$progress}
                    </div>
                </cell_inner>
            </cell>";
        };
        
        // Status untuk tiap tahap (berdasarkan Document_Status, Psikotest_Status, dll)
        $docStatus  = $stageStatuses['Document_Status'] ?? 'PENDING';
        $psyStatus  = $stageStatuses['Psikotest_Status'] ?? 'PENDING';
        $hrStatus   = $stageStatuses['Interview_Status'] ?? 'PENDING';
        $userStatus = $stageStatuses['User_Interview_Status'] ?? 'PENDING';
        $techStatus = $stageStatuses['Technical_Test_Status'] ?? 'PENDING';
        $mcuStatus  = $stageStatuses['MCU_Status'] ?? 'PENDING';
        $offerStatus= $stageStatuses['Offering_Status'] ?? 'PENDING';
        
        // Determine if stage is done (ACCEPT) or not
        $isDocDone  = ($docStatus === 'ACCEPT');
        $isPsyDone  = ($psyStatus === 'ACCEPT');
        $isHrDone   = ($hrStatus === 'ACCEPT');
        $isUserDone = ($userStatus === 'ACCEPT');
        $isTechDone = ($techStatus === 'ACCEPT');
        $isMcuDone  = ($mcuStatus === 'ACCEPT');
        $isOfferDone= ($offerStatus === 'ACCEPT');
        
        // ---- BUILD ROW ----
        echo "<row id=\"{$Fetch->ID}|{$nomor}\">";
        
        // Kolom 1: No
        echo "<cell>{$nomor}</cell>";
        
        // Kolom 2: Recruitment Type
        echo "<cell>" . htmlspecialchars($Fetch->Recruitment_Type ?? '') . "</cell>";
        
        // Kolom 3: Name
        echo "<cell>" . htmlspecialchars($Fetch->Name ?? '') . "</cell>";
        
        // Kolom 4: Gender
        echo "<cell>" . htmlspecialchars($Fetch->Gender ?? '') . "</cell>";
        
        // Kolom 5: PO No
        echo "<cell>" . htmlspecialchars($Requisition->PO_No ?? '') . "</cell>";
        
        // Kolom 6: Position/Requisition
        echo "<cell>" . htmlspecialchars($Requisition->Job_Position ?? '') . "</cell>";
        
        // Kolom 7: Last Education
        echo "<cell>" . htmlspecialchars($Fetch->Last_Education ?? '') . "</cell>";
        
        // Kolom 8: Education Major
        echo "<cell>" . htmlspecialchars($Fetch->Education_Major ?? '') . "</cell>";
        
        // Kolom 9: Interview By / Recruiter
        echo "<cell>" . htmlspecialchars($Fetch->Interview_By ?? '') . "</cell>";
        
        // ---- KOLOM STATUS PER TAHAP (dengan warna berbeda) ----
        
        // Kolom 10: Seleksi Administrasi (Document Verification)
        // Label teks + progress indicator
        echo $getStageCell($docStatus, $isDocDone);
        
        // Kolom 11: Psikotest
        echo $getStageCell($psyStatus, $isPsyDone);
        
        // Kolom 12: Decline Invitation (special column - jika candidate decline)
        $invDeclineStatus = ($Fetch->Status === 'INVITATION DECLINED') ? 'INVITATION DECLINED' : 'PENDING';
        echo $getStageCell($invDeclineStatus, $Fetch->Status === 'INVITATION DECLINED');
        
        // Kolom 13: Interview HR
        echo $getStageCell($hrStatus, $isHrDone);
        
        // Kolom 14: Pending (special - jika status PENDING)
        $pendingStageStatus = ($Fetch->Status === 'PENDING') ? 'PENDING' : 'CREATED';
        echo $getStageCell($pendingStageStatus, $Fetch->Status === 'PENDING');
        
        // Kolom 15: Rejected (special - jika candidate ditolak)
        $rejectStageStatus = in_array($Fetch->Status, ['REJECT', 'OFFER DECLINED', 'INVITATION DECLINED']) 
            ? 'REJECT' 
            : ($Fetch->Status === 'CREATED' ? 'CREATED' : 'PENDING');
        echo $getStageCell($rejectStageStatus, in_array($Fetch->Status, ['REJECT', 'OFFER DECLINED', 'INVITATION DECLINED']));
        
        // Kolom 16: Interview User
        echo $getStageCell($userStatus, $isUserDone);
        
        // Kolom 17: Candidate Analysis (sama seperti Interview HR)
        echo $getStageCell($hrStatus, $isHrDone);
        
        // Kolom 18: Decline Offered (jika menolak offering)
        $offerDeclineStatus = ($Fetch->Status === 'OFFER DECLINED') ? 'OFFER DECLINED' : 'PENDING';
        echo $getStageCell($offerDeclineStatus, $Fetch->Status === 'OFFER DECLINED');
        
        // Kolom 19: Hired (CLOSED - candidate sudah diterima)
        $hiredStageStatus = ($Fetch->Status === 'HIRED' || $Fetch->Status === 'CLOSED') 
            ? 'HIRED' 
            : 'PENDING';
        echo $getStageCell($hiredStageStatus, $Fetch->Status === 'HIRED' || $Fetch->Status === 'CLOSED');
        
        echo "</row>";
        
        $nomor++;
    }
}

echo "</rows>";
