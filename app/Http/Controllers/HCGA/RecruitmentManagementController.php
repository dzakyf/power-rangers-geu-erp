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

        // Urutan tahapan recruitment funnel
        $stageKeys = [
            'Seleksi_Adm',
            'Psikotes',
            'Interview_HR',
            'Interview_User',
            'Tes_Teknik',
            'MCU',
            'Offering',
        ];

        $rows = collect();

        foreach ($query->groupBy('Requisition_ID') as $ptk => $group) {
            // Hitung metrik funnel kumulatif per tanggal pendaftaran/tahap
            foreach ($group as $c) {
                $statusMap = $this->calculateCandidateFunnel($c);
                $date = !empty($c->Created_Date) ? \Carbon\Carbon::parse($c->Created_Date)->format('d M Y') : date('d M Y');

                foreach ($stageKeys as $st) {
                    if ($statusMap[$st]['entered']) {
                        $rows->push([
                            'no_ptk'   => $ptk,
                            'Stage'    => $st,
                            'Type'     => 'Total Masuk',
                            'Date'     => $date,
                            'Count'    => 1,
                        ]);
                    }
                    if ($statusMap[$st]['passed']) {
                        $rows->push([
                            'no_ptk'   => $ptk,
                            'Stage'    => $st,
                            'Type'     => 'Lolos',
                            'Date'     => $date,
                            'Count'    => 1,
                        ]);
                    }
                }
            }
        }

        $allCols = recruitment_management_columns();
        $defaultCols = recruitment_management_default_columns();
        $activeCols = $defaultCols;
        $username = Auth::user()->username ?? '';

        if (\Illuminate\Support\Facades\Schema::hasTable('user_grid_prefs') && $username !== '') {
            $savedJson = DB::table('user_grid_prefs')
                ->where('username', $username)
                ->where('view', 'recruitment-management')
                ->value('columns_json');
            $savedCols = $savedJson ? json_decode($savedJson, true) : null;
            if (is_array($savedCols) && count($savedCols)) {
                $valid = array_values(array_intersect($savedCols, array_keys($allCols)));
                if (count($valid)) {
                    $activeCols = $valid;
                }
            }
        }

        $meta = [];
        foreach ($allCols as $ck => $cm) {
            $meta[$ck] = [
                'label'  => $cm[0],
                'width'  => $cm[1],
                'align'  => $cm[2],
                'filter' => $cm[3],
                'sort'   => $cm[4],
                'type'   => 'ro',
            ];
        }

        return view('humanresources.recruitment.recruitment-management', compact('rows', 'allCols', 'defaultCols', 'activeCols', 'meta'));
    }

    /**
     * Hitung status funnel kumulatif seorang kandidat:
     * Menentukan tahap apa saja yang pernah dimasuki (entered) dan lolos (passed).
     */
    private function calculateCandidateFunnel($c): array
    {
        $statusUpper = strtoupper((string) ($c->Status ?? ''));

        // Stage hierarchy rank untuk mengetahui posisi progress kandidat saat ini
        $stageRank = [
            'CREATED'          => 1,
            'PENDING'          => 1,
            'UPLOADED'         => 1,
            'VERIFICATION'     => 1,
            'SEEK'             => 1,
            'PSIKOTEST'        => 2,
            'INTERVIEW_HR'     => 3,
            'INTERVIEWED'      => 4, // Legacy status setelah lolos HR
            'INTERVIEW_USER'   => 4,
            'BUSINESS_REVIEW'  => 5,
            'TECHNICAL_TEST'   => 5,
            'MCU'              => 6,
            'OFFERING'         => 7,
            'HIRED'            => 8,
            'CLOSED'           => 8,
        ];

        $currentRank = $stageRank[$statusUpper] ?? 1;

        // Kondisi lolos (passed) eksplisit tiap tahap
        $passAdm    = ($c->Document_Status === 'ACCEPT');
        $passPsi    = ($c->Psikotest_Status === 'ACCEPT');
        $passHr     = ($c->Interview_Status === 'ACCEPT');
        $passUser   = ($c->User_Interview_Status === 'ACCEPT');
        $passTech   = ($c->Technical_Test_Status === 'ACCEPT');
        $passMcu    = ($c->MCU_Status === 'ACCEPT');
        $passOffer  = ($c->Offering_Status === 'ACCEPT' || in_array($statusUpper, ['HIRED', 'CLOSED']));

        // Indikasi apakah kandidat sudah pernah disentuh/masuk (entered) ke tahap tertentu:
        // Masuk ke tahap X jika lolos tahap sebelumnya, ada riwayat tanggal/status tahap X,
        // ATAU status saat ini sudah berada di tahap X atau tahap setelahnya dalam pipeline!
        $touchAdm   = true; // Semua pendaftar masuk seleksi administrasi
        $touchPsi   = $passAdm || !empty($c->Psikotest_Status) || !empty($c->Psikotest_Date) || $currentRank >= 2;
        $touchHr    = ($passAdm && $passPsi) || !empty($c->Interview_Status) || !empty($c->Interview_Date) || $currentRank >= 3;
        $touchUser  = ($passAdm && $passPsi && $passHr) || !empty($c->User_Interview_Status) || !empty($c->User_Interview_Date) || $currentRank >= 4;
        $touchTech  = ($passAdm && $passPsi && $passHr && $passUser) || !empty($c->Technical_Test_Status) || !empty($c->Technical_Date) || $currentRank >= 5;
        $touchMcu   = ($passAdm && $passPsi && $passHr && $passUser && $passTech) || !empty($c->MCU_Status) || !empty($c->MCU_Schedule) || $currentRank >= 6;
        $touchOffer = ($passAdm && $passPsi && $passHr && $passUser && $passTech && $passMcu) || !empty($c->Offering_Status) || $passOffer || $currentRank >= 7;

        return [
            'Seleksi_Adm' => [
                'entered' => $touchAdm,
                'passed'  => $passAdm || $currentRank >= 2 || $touchPsi,
            ],
            'Psikotes' => [
                'entered' => $touchPsi,
                'passed'  => $passPsi || $currentRank >= 3 || $touchHr,
            ],
            'Interview_HR' => [
                'entered' => $touchHr,
                'passed'  => $passHr || $currentRank >= 4 || $touchUser,
            ],
            'Interview_User' => [
                'entered' => $touchUser,
                'passed'  => $passUser || $currentRank >= 5 || $touchTech,
            ],
            'Tes_Teknik' => [
                'entered' => $touchTech,
                'passed'  => $passTech || $currentRank >= 6 || $touchMcu,
            ],
            'MCU' => [
                'entered' => $touchMcu,
                'passed'  => $passMcu || $currentRank >= 7 || $touchOffer,
            ],
            'Offering' => [
                'entered' => $touchOffer,
                'passed'  => $passOffer || $currentRank >= 8,
            ],
        ];
    }

    /**
     * Simpan preferensi kolom yang dipilih user.
     * POST /hrd-recruitment-management-columns-save
     */
    public function saveColumns(Request $request)
    {
        $out = ['status' => 'error', 'message' => 'Terjadi kesalahan.'];
        try {
            $username = Auth::user()->username ?? '';
            if ($username === '') {
                throw new \Exception('Sesi user tidak valid.');
            }

            $allCols = recruitment_management_columns();
            $colsParam = (string) $request->input('cols', '');
            $keys = array_values(array_intersect(explode(',', $colsParam), array_keys($allCols)));

            if (!count($keys)) {
                throw new \Exception('Pilih minimal 1 kolom.');
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('user_grid_prefs')) {
                DB::table('user_grid_prefs')->updateOrInsert(
                    ['username' => $username, 'view' => 'recruitment-management'],
                    ['columns_json' => json_encode($keys), 'Updated_Date' => date('Y-m-d H:i:s')]
                );
            }

            $out = ['status' => 'ok'];
        } catch (\Exception $e) {
            $out['message'] = $e->getMessage();
        }

        return response()->json($out);
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

        // Filter berdasarkan Stage dan Metrik (jika dikirim dari klik kolom pivot)
        $stageParam = $request->input('stage');
        $metricParam = $request->input('metric', 'ENTERED'); // 'ENTERED' atau 'PASSED'
        if ($stageParam) {
            $candidates = $candidates->filter(function ($c) use ($stageParam, $metricParam) {
                $funnel = $this->calculateCandidateFunnel($c);
                if (!isset($funnel[$stageParam])) {
                    return true;
                }
                return $metricParam === 'PASSED' ? !empty($funnel[$stageParam]['passed']) : !empty($funnel[$stageParam]['entered']);
            });
        }

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
            'ACCEPT'  => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Selesai'],
            'PENDING' => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Pending'],
            'REJECT'  => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Ditolak'],
            'SEEK'    => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Process'],
            'CREATED'          => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Baru'],
            'UPLOADED'        => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Uploaded'],
            'PSIKOTEST'       => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Psikotest'],
            'INTERVIEW_HR'    => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Interview HR'],
            'INTERVIEW_USER'  => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Interview User'],
            'TECHNICAL_TEST'  => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Tes Teknik'],
            'MCU'             => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'MCU'],
            'OFFERING'        => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Offering'],
            'HIRED'           => ['bg' => '#28a745', 'text' => '#ffffff', 'label' => 'Hired'],
            'BUSINESS_REVIEW' => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Review'],
            'CLOSED'          => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Closed'],
            'OFFER DECLINED'  => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Declined'],
            'INVITATION DECLINED' => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Inv. Declined'],
            'CANCELED'        => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Canceled'],
        ];

        // Dapatkan kolom dinamis yang diminta
        $allCols = recruitment_management_columns();
        $defaultCols = recruitment_management_default_columns();
        $activeCols = $defaultCols;
        if ($request->filled('cols')) {
            $parsed = array_values(array_intersect(explode(',', (string) $request->input('cols')), array_keys($allCols)));
            if (count($parsed)) {
                $activeCols = $parsed;
            }
        }

        $xml = new \SimpleXMLElement('<rows/>');
        $nomor = 1;

        foreach ($candidates as $Fetch) {
            $pecahin = explode(',', (string) $Fetch->Requisition_ID);

            foreach ($pecahin as $pecahreq) {
                $Requisition = $requisitions->get($pecahreq);
                if (!$Requisition) continue;

                $funnel = $this->calculateCandidateFunnel($Fetch);

                $docStatus   = $Fetch->Document_Status ?? 'PENDING';
                $psyStatus   = $Fetch->Psikotest_Status ?? 'PENDING';
                $hrStatus    = $Fetch->Interview_Status ?? 'PENDING';
                $userStatus  = $Fetch->User_Interview_Status ?? 'PENDING';
                
                // Gunakan evaluasi hierarki funnel agar konsisten (jika sudah di tahap berikutnya, tahap sebelumnya dianggap lolos)
                $isDocDone   = !empty($funnel['Seleksi_Adm']['passed']);
                $isPsyDone   = !empty($funnel['Psikotes']['passed']);
                $isHrDone    = !empty($funnel['Interview_HR']['passed']);
                $isUserDone  = !empty($funnel['Interview_User']['passed']);

                $isRejected = in_array($Fetch->Status, ['REJECT', 'OFFER DECLINED', 'INVITATION DECLINED']);
                $isHired = ($Fetch->Status === 'HIRED' || $Fetch->Status === 'CLOSED');

                $row = $xml->addChild('row');
                $row->addAttribute('id', $Fetch->ID . '|' . $nomor);

                // Kolom nomor urut
                $row->addChild('cell', (string) $nomor);

                // Seluruh data per kolom dinamis
                $cellData = [
                    'Recruitment_Type' => ['val' => (string) ($Fetch->Recruitment_Type ?? ''), 'style' => null],
                    'Name' => ['val' => (string) ($Fetch->Name ?? ''), 'style' => null],
                    'Gender' => ['val' => (string) ($Fetch->Gender ?? ''), 'style' => null],
                    'PO_No' => ['val' => (string) ($Requisition->PO_No ?? ''), 'style' => null],
                    'Requisition' => ['val' => (string) ($Requisition->Job_Position ?? ''), 'style' => null],
                    'Last_Education' => ['val' => (string) ($Fetch->Last_Education ?? ''), 'style' => null],
                    'Education_Major' => ['val' => (string) ($Fetch->Education_Major ?? ''), 'style' => null],
                    'Interview_By' => ['val' => (string) ($Fetch->Interview_By ?? ''), 'style' => null],
                    'Doc_Status' => [
                        'val' => $isDocDone ? '1' : '0',
                        'style' => 'background-color: ' . ($statusColors[$docStatus]['bg'] ?? '#e2e3e5') . '; color: ' . ($statusColors[$docStatus]['text'] ?? '#383d41') . ';'
                    ],
                    'Psikotest_Status' => [
                        'val' => $isPsyDone ? '1' : '0',
                        'style' => 'background-color: ' . ($statusColors[$psyStatus]['bg'] ?? '#e2e3e5') . '; color: ' . ($statusColors[$psyStatus]['text'] ?? '#383d41') . ';'
                    ],
                    'Decline_Invitation' => [
                        'val' => ($Fetch->Status === 'INVITATION DECLINED') ? '1' : '0',
                        'style' => 'background-color: ' . (($Fetch->Status === 'INVITATION DECLINED') ? $statusColors['INVITATION DECLINED']['bg'] : $statusColors['PENDING']['bg']) . '; color: ' . (($Fetch->Status === 'INVITATION DECLINED') ? $statusColors['INVITATION DECLINED']['text'] : $statusColors['PENDING']['text']) . ';'
                    ],
                    'Interview_HR' => [
                        'val' => $isHrDone ? '1' : '0',
                        'style' => 'background-color: ' . ($statusColors[$hrStatus]['bg'] ?? '#e2e3e5') . '; color: ' . ($statusColors[$hrStatus]['text'] ?? '#383d41') . ';'
                    ],
                    'Pending_Status' => [
                        'val' => ($Fetch->Status === 'PENDING') ? '1' : '0',
                        'style' => 'background-color: ' . (($Fetch->Status === 'PENDING') ? $statusColors['PENDING']['bg'] : $statusColors['CREATED']['bg']) . '; color: ' . (($Fetch->Status === 'PENDING') ? $statusColors['PENDING']['text'] : $statusColors['CREATED']['text']) . ';'
                    ],
                    'Reject_Status' => [
                        'val' => $isRejected ? '1' : '0',
                        'style' => 'background-color: ' . ($isRejected ? $statusColors['REJECT']['bg'] : $statusColors['PENDING']['bg']) . '; color: ' . ($isRejected ? $statusColors['REJECT']['text'] : $statusColors['PENDING']['text']) . ';'
                    ],
                    'Interview_User' => [
                        'val' => $isUserDone ? '1' : '0',
                        'style' => 'background-color: ' . ($statusColors[$userStatus]['bg'] ?? '#e2e3e5') . '; color: ' . ($statusColors[$userStatus]['text'] ?? '#383d41') . ';'
                    ],
                    'Candidate_Analysis' => [
                        'val' => $isHrDone ? '1' : '0',
                        'style' => 'background-color: ' . ($statusColors[$hrStatus]['bg'] ?? '#e2e3e5') . '; color: ' . ($statusColors[$hrStatus]['text'] ?? '#383d41') . ';'
                    ],
                    'Decline_Offered' => [
                        'val' => ($Fetch->Status === 'OFFER DECLINED') ? '1' : '0',
                        'style' => 'background-color: ' . (($Fetch->Status === 'OFFER DECLINED') ? $statusColors['OFFER DECLINED']['bg'] : $statusColors['PENDING']['bg']) . '; color: ' . (($Fetch->Status === 'OFFER DECLINED') ? $statusColors['OFFER DECLINED']['text'] : $statusColors['PENDING']['text']) . ';'
                    ],
                    'Hired_Status' => [
                        'val' => $isHired ? '1' : '0',
                        'style' => 'background-color: ' . ($isHired ? $statusColors['HIRED']['bg'] : $statusColors['PENDING']['bg']) . '; color: ' . ($isHired ? $statusColors['HIRED']['text'] : $statusColors['PENDING']['text']) . ';'
                    ],
                ];

                foreach ($activeCols as $cKey) {
                    $cDef = $cellData[$cKey] ?? ['val' => '', 'style' => null];
                    $cNode = $row->addChild('cell', (string) $cDef['val']);
                    if (!empty($cDef['style'])) {
                        $cNode->addAttribute('style', $cDef['style']);
                    }
                }

                $nomor++;
            }
        }

        return response($xml->asXML())->header('Content-Type', 'text/xml');
    }

    /**
     * Generate XML untuk Pivot Grid (dashboard summary per PTK)
     * GET /xml-hrd-recruitment-pivot
     * Mendukung mode: 'total' (default) atau 'breakdown' (2 baris per PTK)
     */
    public function pivotXml(Request $request)
    {
        $LSP = Auth::user()->Location ?? 'GEU-HO';
        $mode = $request->input('mode', 'total'); // 'total' atau 'breakdown'

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

        $stageMap = [
            'Seleksi Administrasi' => 'Seleksi_Adm',
            'Psikotes'             => 'Psikotes',
            'Interview HR'         => 'Interview_HR',
            'Interview User'       => 'Interview_User',
            'Tes Teknik'           => 'Tes_Teknik',
            'MCU'                  => 'MCU',
            'Offering Letter'      => 'Offering',
        ];

        $xml = new \SimpleXMLElement('<rows/>');

        foreach ($candidates->groupBy('Requisition_ID') as $ptk => $group) {
            $requisition = $requisitions->get($ptk);
            $approvedBy = DB::table('requisition_approval')
                ->where('Task_ID', $ptk)
                ->where('Approved_Status', 1)
                ->orderBy('Level', 'DESC')
                ->value('Approved_By') ?? '';
            $desc = $requisition ? $requisition->Description : '';

            // Hitung status kumulatif tiap candidate
            $funnels = $group->map(function ($c) {
                return $this->calculateCandidateFunnel($c);
            });

            if ($mode === 'breakdown') {
                // Baris 1: Total Masuk (Entered)
                $nodeEntered = $xml->addChild('row');
                $nodeEntered->addAttribute('id', $ptk . '|ENTERED');
                $nodeEntered->addChild('cell', (string) $ptk);
                $nodeEntered->addChild('cell', 'Total Masuk');
                $nodeEntered->addChild('cell', (string) $approvedBy);
                foreach ($stageMap as $label => $key) {
                    $cnt = $funnels->where($key . '.entered', true)->count();
                    $nodeEntered->addChild('cell', (string) $cnt);
                }
                $nodeEntered->addChild('cell', (string) $desc);

                // Baris 2: Lolos / Approved (Passed)
                $nodePassed = $xml->addChild('row');
                $nodePassed->addAttribute('id', $ptk . '|PASSED');
                $nodePassed->addChild('cell', (string) $ptk);
                $nodePassed->addChild('cell', 'Lolos / Approved');
                $nodePassed->addChild('cell', (string) $approvedBy);
                foreach ($stageMap as $label => $key) {
                    $cnt = $funnels->where($key . '.passed', true)->count();
                    $nodePassed->addChild('cell', (string) $cnt);
                }
                $nodePassed->addChild('cell', (string) $desc);
            } else {
                // Mode Default: Total Masuk kumulatif per tahap
                $node = $xml->addChild('row');
                $node->addAttribute('id', (string) $ptk);
                $node->addChild('cell', (string) $ptk);
                $node->addChild('cell', (string) $approvedBy);
                foreach ($stageMap as $label => $key) {
                    $cnt = $funnels->where($key . '.entered', true)->count();
                    $node->addChild('cell', (string) $cnt);
                }
                $node->addChild('cell', (string) $desc);
            }
        }

        return response($xml->asXML())->header('Content-Type', 'text/xml');
    }
}
