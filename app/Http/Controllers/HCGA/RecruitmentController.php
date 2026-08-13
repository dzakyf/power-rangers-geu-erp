<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecruitmentController extends Controller
{
    public function index()
    {
        $query = DB::table('recruitment')->whereIn('Requisition_ID', function ($q) {
            $q->select('Task_ID')->from('requisition');
        })->get();

        $stages = [
            'ACC'            => ['status' => 'CREATED', 'statusField' => 'Registration_Status',   'dateField' => 'Registration_Status_Date'],
            'Seleksi_Adm'    => ['status' => 'ACCEPT',  'statusField' => 'Document_Status',      'dateField' => 'Document_Date'],
            'Psikotes'       => ['status' => 'ACCEPT',  'statusField' => 'Psikotest_Status',     'dateField' => 'Psikotest_Date'],
            'Interview_HR'   => ['status' => 'ACCEPT',  'statusField' => 'Interview_Status',     'dateField' => 'Interview_Date'],
            'Interview_User' => ['status' => 'ACCEPT',  'statusField' => 'User_Interview_Status','dateField' => 'User_Interview_Date'],
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

    public function indexXml(Request $request)
    {
        $LSP = Auth::user()->Location;

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

        $xml = new \SimpleXMLElement('<rows/>');
        $nomor = 1;

        foreach ($candidates as $Fetch) {
            $pecahin = explode(',', (string) $Fetch->Requisition_ID);

            foreach ($pecahin as $pecahreq) {
                $Requisition = $requisitions->get($pecahreq);

                $statusPending = 0;
                $status1 = 0;
                $status2 = 0;
                $status3 = 0;
                $status4 = 0;
                $status5 = 0;
                $status6 = 0;
                $status7 = 0;
                $status8 = 0;
                $status9 = 0;

                $style1 = false;
                $style2 = false;
                $style3 = false;
                $style4 = false;
                $style5 = false;
                $style6 = false;
                $style7 = false;
                $style8 = false;
                $style9 = false;
                $stylePending = false;

                if ($Fetch->Status == 'CREATED' || $Fetch->Status == 'UPLOADED') {
                    $status1 = 1;
                    $style1 = true;
                } elseif ($Fetch->Status == 'PSIKOTEST') {
                    $status2 = 1;
                    $style2 = true;
                } elseif ($Fetch->Status == 'PENDING') {
                    $statusPending = 1;
                    $stylePending = true;
                } elseif ($Fetch->Status == 'INTERVIEWED') {
                    $status3 = 1;
                    $style3 = true;
                } elseif ($Fetch->Status == 'BUSINESS_REVIEW') {
                    $status4 = 1;
                    $style4 = true;
                } elseif ($Fetch->Status == 'CLOSED') {
                    $status6 = 1;
                    $style6 = true;
                } elseif ($Fetch->Status == 'REJECT') {
                    $status7 = 1;
                    $style5 = true;
                    $style7 = true;
                } elseif ($Fetch->Status == 'OFFER DECLINED') {
                    $status8 = 1;
                    $style8 = true;
                } elseif ($Fetch->Status == 'INVITATION DECLINED') {
                    $status9 = 1;
                    $style9 = true;
                }

                $row = $xml->addChild('row');
                $row->addAttribute('id', $Fetch->ID . '|' . $nomor);
                $row->addChild('cell', (string) $nomor);
                $row->addChild('cell', (string) $Fetch->Recruitment_Type);
                $row->addChild('cell', (string) $Fetch->Name);
                $row->addChild('cell', (string) $Fetch->Gender);
                $row->addChild('cell', (string) ($Requisition ? $Requisition->PO_No : ''));
                $row->addChild('cell', (string) ($Requisition ? $Requisition->Job_Position : ''));
                $row->addChild('cell', (string) $Fetch->Last_Education);
                $row->addChild('cell', (string) $Fetch->Education_Major);
                $row->addChild('cell', (string) $Fetch->Interview_By);

                $cell = $row->addChild('cell', (string) $status1);
                if ($style1) { $cell->addAttribute('style', 'background-color:red'); }
                $cell = $row->addChild('cell', (string) $status2);
                if ($style2) { $cell->addAttribute('style', 'background-color:red'); }
                $cell = $row->addChild('cell', (string) $status9);
                if ($style9) { $cell->addAttribute('style', 'background-color:red'); }
                $cell = $row->addChild('cell', (string) $status7);
                if ($style7) { $cell->addAttribute('style', 'background-color:red'); }
                $cell = $row->addChild('cell', (string) $status3);
                if ($style3) { $cell->addAttribute('style', 'background-color:red'); }
                $cell = $row->addChild('cell', (string) $statusPending);
                if ($stylePending) { $cell->addAttribute('style', 'background-color:red'); }
                $cell = $row->addChild('cell', (string) $status4);
                if ($style4) { $cell->addAttribute('style', 'background-color:red'); }
                $cell = $row->addChild('cell', (string) $status5);
                if ($style5) { $cell->addAttribute('style', 'background-color:red'); }
                $cell = $row->addChild('cell', (string) $status8);
                if ($style8) { $cell->addAttribute('style', 'background-color:red'); }
                $cell = $row->addChild('cell', (string) $status6);
                if ($style6) { $cell->addAttribute('style', 'background-color:red'); }

                $nomor++;
            }
        }

        return response($xml->asXML())->header('Content-Type', 'text/xml');
    }

    public function pivotXml()
    {
        $LSP = Auth::user()->Location;

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
            $latestApproval = optional($approvals->get($ptk))->first();

            $row = [
                'PTK_Number'          => $ptk,
                'Current_Approver_By' => $latestApproval ? $latestApproval->Approved_By : '',
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

    public function recruitmentDocumentApproval(Request $request)
    {
        $status = $request->stat;
        if ($status) {
            $status = "PSIKOTEST";
        }else {
            $status = "REJECT";
        }

        DB::table('recruitment')->where("ID", $request->id)->update([
            'Status' => $status,
            'Registration_Status' =>  $status,
        ]);

        if ($status == 'PSIKOTEST') {
            $message = "di setujui dan lanjut ke tahap Psikotest";
        }else {
            $message = "di tolak";
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
        ]);
    }
}
