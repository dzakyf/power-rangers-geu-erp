<?php
/**
 * RecruitmentManagementController - Controller untuk halaman management grid
 * menampilkan dashboard progress dan daftar candidate dengan status per tahap
 */
namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecruitmentManagementController extends Controller
{
    /**
     * Tampilkan halaman dashboard recruitment management
     * GET /hrd-recruitment-management
     */
    public function index()
    {
        $query = DB::table('recruitment')->whereIn('Requisition_ID', function ($q) {
            $q->select('Task_ID')->from('requisition');
        })->get();

        // Tahapan yang ditampilkan di dashboard pivot (urut sesuai alur yang benar)
        $stages = [
            'ACC'            => ['status' => 'CREATED', 'statusField' => 'Registration_Status',   'dateField' => 'Registration_Status_Date'],
            'Seleksi_Adm'    => ['status' => 'ACCEPT',  'statusField' => 'Document_Status',      'dateField' => 'Document_Date'],
            'Psikotes'       => ['status' => 'ACCEPT',  'statusField' => 'Psikotest_Status',     'dateField' => 'Psikotest_Date'],
            'Interview_HR'   => ['status' => 'ACCEPT',  'statusField' => 'Interview_Status',     'dateField' => 'Interview_Date'],
            'Interview_User' => ['status' => 'ACCEPT',  'statusField' => 'User_Interview_Status','dateField' => 'User_Interview_Date'],
            'Tes_Teknik'     => ['status' => 'ACCEPT',  'statusField' => 'Technical_Test_Status','dateField' => 'Technical_Date'],
            'MCU'            => ['status' => 'ACCEPT',  'statusField' => 'MCU_Status',           'dateField' => 'MCU_Schedule'],
            'Offering'       => ['status' => 'ACCEPT',  'statusField' => 'Offering_Status',      'dateField' => null],
        ];

        $rows = collect();

        foreach ($query->groupBy('Requisition_ID') as $ptk => $group) {
            foreach ($stages as $stageName => $cfg) {
                $matching = $group->filter(function ($c) use ($cfg) {
                    return $c->{$cfg['statusField']} === $cfg['status']
                        && !is_null($c->{$cfg['dateField']});
                });

                foreach ($matching->groupBy(function ($c) use ($cfg) {
                    return \Carbon\Carbon::parse($c->{$cfg['dateField']})->format('Y-m-d');
                }) as $date => $items) {
                    $rows->push([
                        'no_ptk' => $ptk,
                        'Stage'  => $stageName,
                        'Date'   => \Carbon\Carbon::parse($date)->format('d M Y'),
                        'Count'  => $items->count(),
                    ]);
                }
            }
        }

        return view('humanresources.recruitment.recruitment-management', compact('rows'));
    }

    /**
     * Generate XML untuk dhtmlxGrid utama (RecruitmentLists)
     * GET /xml-hrd-recruitment-management
     * 
     * Kolom grid (urut sesuai alur recruitment yang benar):
     * 1. No
     * 2. Recruitment Type
     * 3. Name
     * 4. Gender
     * 5. PO No
     * 6. Position/Requisition
     * 7. Last Education
     * 8. Education Major
     * 9. Interview By
     * 10. Seleksi Administrasi (Document Status) - warna: hijau=selesai, abu=pending, merah=reject
     * 11. Psikotest - warna: hijau=selesai, abu=pending, merah=reject
     * 12. Decline Invitation (special)
     * 13. Interview HR - warna: hijau=selesai, abu=pending, merah=reject
     * 14. Pending (special)
     * 15. Rejected (special)
     * 16. Interview User - warna: hijau=selesai, abu=pending, merah=reject
     * 17. Candidate Analysis (special - link ke detail)
     * 18. Decline Offered (special)
     * 19. Hired - warna: hijau=bright (hired), abu=belum
     */
    public function indexXml(Request $request)
    {
        $LSP = Auth::user()->Location ?? 'GEU-HO';

        $query = DB::table('recruitment')
            ->where('Location', $LSP)
            ->where('Status', '!=', 'CANCELED')
            ->orderBy('ID', 'DESC');

        if ($request->filled('Requisition_ID')) {
            $query->where('Requisition_ID', $request->input('Requisition_ID'));
        }

        $candidates = $query->get();

        $reqIds = $candidates->pluck('Requisition_ID')
            ->flatMap(function ($r) {
                return array_filter(explode(',', (string) $r));
            })
            ->unique()
            ->values();

        $requisitions = DB::table('requisition')
            ->whereIn('Task_ID', $reqIds)
            ->get()
            ->keyBy('Task_ID');

        // Color mapping untuk visual indicator di grid
        $statusColors = [
            // Status tahapan (field-by-field)
            'ACCEPT'  => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Selesai'],   // Hijau
            'PENDING' => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Pending'],   // Abu-abu
            'REJECT'  => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Ditolak'],   // Merah
            'SEEK'    => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Process'],    // Kuning
            
            // Status utama
            'CREATED'          => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Baru'],
            'UPLOADED'        => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Uploaded'],
            'PSIKOTEST'       => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Psikotest'],
            'INTERVIEW_HR'    => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Interview HR'],
            'INTERVIEW_USER'  => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Interview User'],
            'TECHNICAL_TEST'  => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Tes Teknik'],
            'MCU'             => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'MCU'],
            'OFFERING'        => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Offering'],
            'HIRED'           => ['bg' => '#28a745', 'text' => '#ffffff', 'label' => 'Hired'],
            'PENDING'         => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Pending'],
            'BUSINESS_REVIEW' => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Review'],
            'CLOSED'          => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Closed'],
            'REJECT'          => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Ditolak'],
            'OFFER DECLINED'  => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Declined'],
            'INVITATION DECLINED' => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Inv. Declined'],
            'CANCELED'        => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Canceled'],
        ];

        $xml = new \SimpleXMLElement('<rows/>');
        $nomor = 1;

        foreach ($candidates as $Fetch) {
            $pecahin = explode(',', (string) $Fetch->Requisition_ID);

            foreach ($pecahin as $pecahreq) {
                $Requisition = $requisitions->get($pecahreq);
                if (!$Requisition) continue;

                // ---- Status untuk tiap tahap ----
                // Status berdasarkan field-by-field di tabel recruitment
                $docStatus   = $Fetch->Document_Status ?? 'PENDING';
                $psyStatus   = $Fetch->Psikotest_Status ?? 'PENDING';
                $hrStatus    = $Fetch->Interview_Status ?? 'PENDING';
                $userStatus  = $Fetch->User_Interview_Status ?? 'PENDING';
                $techStatus  = $Fetch->Technical_Test_Status ?? 'PENDING';
                $mcuStatus   = $Fetch->MCU_Status ?? 'PENDING';
                $offerStatus = $Fetch->Offering_Status ?? 'PENDING';

                // Helper: dapatkan warna dan nilai untuk cell status
                $getStatusCell = function($statusValue, $isDone, $colors) use ($statusColors) {
                    $color = $statusColors[$statusValue] ?? $statusColors['PENDING'];
                    $val   = $isDone ? '1' : '0';
                    $style = "background-color: {$color['bg']}; color: {$color['text']};";
                    return "<cell style=\"{$style}\">{$val}</cell>";
                };

                // Tentukan apakah stage selesai (ACCEPT) atau tidak
                $isDocDone   = ($docStatus === 'ACCEPT');
                $isPsyDone   = ($psyStatus === 'ACCEPT');
                $isHrDone    = ($hrStatus === 'ACCEPT');
                $isUserDone  = ($userStatus === 'ACCEPT');
                $isTechDone  = ($techStatus === 'ACCEPT');
                $isMcuDone   = ($mcuStatus === 'ACCEPT');
                $isOfferDone = ($offerStatus === 'ACCEPT');

                // Build row XML
                $row = $xml->addChild('row');
                $row->addAttribute('id', $Fetch->ID . '|' . $nomor);

                // Kolom 1: No
                $row->addChild('cell', (string) $nomor);

                // Kolom 2: Recruitment Type
                $row->addChild('cell', (string) ($Fetch->Recruitment_Type ?? ''));

                // Kolom 3: Name
                $row->addChild('cell', (string) ($Fetch->Name ?? ''));

                // Kolom 4: Gender
                $row->addChild('cell', (string) ($Fetch->Gender ?? ''));

                // Kolom 5: PO No
                $row->addChild('cell', (string) ($Requisition->PO_No ?? ''));

                // Kolom 6: Position/Requisition
                $row->addChild('cell', (string) ($Requisition->Job_Position ?? ''));

                // Kolom 7: Last Education
                $row->addChild('cell', (string) ($Fetch->Last_Education ?? ''));

                // Kolom 8: Education Major
                $row->addChild('cell', (string) ($Fetch->Education_Major ?? ''));

                // Kolom 9: Interview By / Recruiter
                $row->addChild('cell', (string) ($Fetch->Interview_By ?? ''));

                // ---- KOLOM STATUS PER TAHAP (dengan warna berbeda) ----
                
                // Kolom 10: Seleksi Administrasi (Document Status)
                // 1 = selesai (hijau), 0 = pending (abu), reject = merah
                $docBg    = isset($statusColors[$docStatus]['bg']) ? $statusColors[$docStatus]['bg'] : '#e2e3e5';
                $docText  = isset($statusColors[$docStatus]['text']) ? $statusColors[$docStatus]['text'] : '#383d41';
                $cell10   = $row->addChild('cell', $isDocDone ? '1' : '0');
                $cell10->addAttribute('style', "background-color: {$docBg}; color: {$docText};");

                // Kolom 11: Psikotest
                $psyBg    = isset($statusColors[$psyStatus]['bg']) ? $statusColors[$psyStatus]['bg'] : '#e2e3e5';
                $psyText  = isset($statusColors[$psyStatus]['text']) ? $statusColors[$psyStatus]['text'] : '#383d41';
                $cell11   = $row->addChild('cell', $isPsyDone ? '1' : '0');
                $cell11->addAttribute('style', "background-color: {$psyBg}; color: {$psyText};");

                // Kolom 12: Decline Invitation (special column)
                // 1 jika candidate decline undangan
                $declineColor = ($Fetch->Status === 'INVITATION DECLINED') 
                    ? $statusColors['INVITATION DECLINED'] 
                    : $statusColors['PENDING'];
                $declineVal   = ($Fetch->Status === 'INVITATION DECLINED') ? '1' : '0';
                $cell12 = $row->addChild('cell', $declineVal);
                $cell12->addAttribute('style', "background-color: {$declineColor['bg']}; color: {$declineColor['text']};");

                // Kolom 13: Interview HR
                $hrBg     = isset($statusColors[$hrStatus]['bg']) ? $statusColors[$hrStatus]['bg'] : '#e2e3e5';
                $hrText   = isset($statusColors[$hrStatus]['text']) ? $statusColors[$hrStatus]['text'] : '#383d41';
                $cell13   = $row->addChild('cell', $isHrDone ? '1' : '0');
                $cell13->addAttribute('style', "background-color: {$hrBg}; color: {$hrText};");

                // Kolom 14: Pending (special - status PENDING)
                $pendingColor = ($Fetch->Status === 'PENDING') 
                    ? $statusColors['PENDING'] 
                    : $statusColors['CREATED'];
                $pendingVal   = ($Fetch->Status === 'PENDING') ? '1' : '0';
                $cell14 = $row->addChild('cell', $pendingVal);
                $cell14->addAttribute('style', "background-color: {$pendingColor['bg']}; color: {$pendingColor['text']};");

                // Kolom 15: Rejected (special - candidate ditolak)
                $isRejected = in_array($Fetch->Status, ['REJECT', 'OFFER DECLINED', 'INVITATION DECLINED']);
                $rejectColor = $isRejected 
                    ? $statusColors['REJECT'] 
                    : ($Fetch->Status === 'CREATED' ? $statusColors['CREATED'] : $statusColors['PENDING']);
                $rejectVal   = $isRejected ? '1' : '0';
                $cell15 = $row->addChild('cell', $rejectVal);
                $cell15->addAttribute('style', "background-color: {$rejectColor['bg']}; color: {$rejectColor['text']};");

                // Kolom 16: Interview User
                $userBg   = isset($statusColors[$userStatus]['bg']) ? $statusColors[$userStatus]['bg'] : '#e2e3e5';
                $userText = isset($statusColors[$userStatus]['text']) ? $statusColors[$userStatus]['text'] : '#383d41';
                $cell16   = $row->addChild('cell', $isUserDone ? '1' : '0');
                $cell16->addAttribute('style', "background-color: {$userBg}; color: {$userText};");

                // Kolom 17: Candidate Analysis (link ke halaman detail)
                // Sama seperti Interview HR - kolom navigasi ke detail candidate
                $analysisColor = isset($statusColors[$hrStatus]) ? $statusColors[$hrStatus] : $statusColors['PENDING'];
                $analysisVal   = $isHrDone ? '1' : '0';
                $cell17 = $row->addChild('cell', $analysisVal);
                $cell17->addAttribute('style', "background-color: {$analysisColor['bg']}; color: {$analysisColor['text']};");

                // Kolom 18: Decline Offered (menolak offering)
                $declinedOffColor = ($Fetch->Status === 'OFFER DECLINED') 
                    ? $statusColors['OFFER DECLINED'] 
                    : $statusColors['PENDING'];
                $declinedOffVal   = ($Fetch->Status === 'OFFER DECLINED') ? '1' : '0';
                $cell18 = $row->addChild('cell', $declinedOffVal);
                $cell18->addAttribute('style', "background-color: {$declinedOffColor['bg']}; color: {$declinedOffColor['text']};");

                // Kolom 19: Hired (candidate sudah diterima)
                $isHired = ($Fetch->Status === 'HIRED' || $Fetch->Status === 'CLOSED');
                $hiredColor = $isHired 
                    ? $statusColors['HIRED'] 
                    : $statusColors['PENDING'];
                $hiredVal   = $isHired ? '1' : '0';
                $cell19 = $row->addChild('cell', $hiredVal);
                $cell19->addAttribute('style', "background-color: {$hiredColor['bg']}; color: {$hiredColor['text']};");

                $nomor++;
            }
        }

        return response($xml->asXML())->header('Content-Type', 'text/xml');
    }

    /**
     * Generate XML untuk Pivot Grid (dashboard summary per PTK)
     * GET /xml-hrd-recruitment-pivot
     */
    public function pivotXml(Request $request)
    {
        $LSP = Auth::user()->Location ?? 'GEU-HO';

        $candidates = DB::table('recruitment')
            ->where('Location', $LSP)
            ->where('Status', '!=', 'CANCELED')
            ->whereIn('Requisition_ID', function ($q) {
                $q->select('Task_ID')->from('requisition');
            })
            ->get();

        $reqIds = $candidates->pluck('Requisition_ID')
            ->flatMap(function ($r) {
                return array_filter(explode(',', (string) $r));
            })
            ->unique()
            ->values();

        $requisitions = DB::table('requisition')
            ->whereIn('Task_ID', $reqIds)
            ->get()
            ->keyBy('Task_ID');

        $approvals = DB::table('requisition_approval')
            ->whereIn('Task_ID', $reqIds)
            ->where('Approved_Status', 1)
            ->orderBy('Level', 'DESC')
            ->get()
            ->groupBy('Task_ID');

        // Tahapan untuk pivot (urut sesuai alur yang benar)
        $stages = [
            'Seleksi Administrasi' => ['status' => 'Document_Status', 'value' => 'ACCEPT', 'date' => 'Document_Date'],
            'Psikotes'             => ['status' => 'Psikotest_Status', 'value' => 'ACCEPT', 'date' => 'Psikotest_Date'],
            'Interview HR'         => ['status' => 'Interview_Status', 'value' => 'ACCEPT', 'date' => 'Interview_Date'],
            'Interview User'       => ['status' => 'User_Interview_Status', 'value' => 'ACCEPT', 'date' => 'User_Interview_Date'],
            'Tes Teknik'           => ['status' => 'Technical_Test_Status', 'value' => 'ACCEPT', 'date' => 'Technical_Date'],
            'MCU'                  => ['status' => 'MCU_Status', 'value' => 'ACCEPT', 'date' => 'MCU_Schedule'],
            'Offering Letter'      => ['status' => 'Offering_Status', 'value' => 'ACCEPT'],
        ];

        $pivotRows = collect();

        foreach ($candidates->groupBy('Requisition_ID') as $ptk => $group) {
            $requisition = $requisitions->get($ptk);
            $approvedBy = DB::table('requisition_approval')
                ->where('Task_ID', $ptk)
                ->where('Approved_Status', 1)
                ->orderBy('Level', 'DESC')
                ->value('Approved_By');

            $row = [
                'PTK_Number'          => $ptk,
                'Current_Approver_By' => $approvedBy ?? '',
                'Keterangan'          => $requisition ? $requisition->Description : '',
            ];

            foreach ($stages as $stageName => $cfg) {
                $count = $group->filter(function ($c) use ($cfg) {
                    $statusMatch = $c->{$cfg['status']} === $cfg['value'];
                    $dateMatch = !isset($cfg['date']) || !is_null($c->{$cfg['date']});
                    return $statusMatch && $dateMatch;
                })->count();

                $row[$stageName] = $count;
            }

            $pivotRows->push($row);
        }

        $xml = new \SimpleXMLElement('<rows/>');

        foreach ($pivotRows as $row) {
            $node = $xml->addChild('row');
            $node->addAttribute('id', (string) $row['PTK_Number']);
            $node->addChild('cell', (string) $row['PTK_Number']);
            $node->addChild('cell', (string) $row['Current_Approver_By']);
            $node->addChild('cell', (string) $row['Seleksi Administrasi']);
            $node->addChild('cell', (string) $row['Psikotes']);
            $node->addChild('cell', (string) $row['Interview HR']);
            $node->addChild('cell', (string) $row['Interview User']);
            $node->addChild('cell', (string) $row['Tes Teknik']);
            $node->addChild('cell', (string) $row['MCU']);
            $node->addChild('cell', (string) $row['Offering Letter']);
            $node->addChild('cell', (string) $row['Keterangan']);
        }

        return response($xml->asXML())->header('Content-Type', 'text/xml');
    }
}
