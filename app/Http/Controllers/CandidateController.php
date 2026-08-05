<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CandidateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $taskId = Auth::user()->username;
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();

        if (!$rec) {
            return view('humanresources.candidate.dashboard', [
                'rec' => null,
                'documentsCount' => 0,
                'hasTest' => false,
                'hasInterview' => false,
                'hasFinalInterview' => false,
                'currentStatus' => '-',
                'position' => '-',
                'createdDate' => null,
                'steps' => [],
            ]);
        }

        $docRecord = DB::table('recruitment_document')->where('Task_ID', $taskId)->first();
        $documentsCount = 0;
        if ($docRecord) {
            foreach (['Foto', 'CV', 'Ijazah', 'Sertifikat', 'KTP', 'NPWP'] as $col) {
                if ($docRecord->$col) $documentsCount++;
            }
        }

        $hasTest = ($rec->Psikotest_Score || $rec->Test_Score || $rec->Technical_Score);
        $hasInterview = $rec->Interview_Date;
        $hasFinalInterview = $rec->User_Interview_Date;
        $currentStatus = $rec->Status ?? 'CREATED';
        $position = $rec->Position;
        $createdDate = $rec->Created_Date;

        $steps = [
            ['label' => 'Applied', 'done' => true, 'date' => $createdDate],
            ['label' => 'Documents', 'done' => $documentsCount > 0, 'date' => null],
            ['label' => 'Assessment', 'done' => $hasTest, 'date' => null],
            ['label' => 'HR Interview', 'done' => $hasInterview, 'date' => $rec->Interview_Date ?? null],
            ['label' => 'Final Interview', 'done' => $hasFinalInterview, 'date' => $rec->User_Interview_Date ?? null],
            ['label' => 'Decision', 'done' => in_array($currentStatus, ['BUSINESS_REVIEW', 'CLOSED']), 'date' => null],
        ];

        return view('humanresources.candidate.dashboard', compact(
            'rec', 'documentsCount', 'hasTest', 'hasInterview',
            'hasFinalInterview', 'currentStatus', 'position', 'createdDate', 'steps'
        ));
    }

    public function profile()
    {
        $taskId = Auth::user()->username;
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();
        return view('humanresources.candidate.profile', compact('rec'));
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'Gender' => 'nullable|in:L,P',
            'Birth_Date' => 'nullable|date',
            'KTP' => 'nullable|digits:16',
            'Last_Education' => 'nullable|string|max:50',
            'Last_Education_From' => 'nullable|string|max:255',
            'Religion' => 'nullable|string|max:50',
            'Marital_Status' => 'nullable|string|max:50',
            'kewarganegaraan' => 'nullable|string|max:100',
            'Address' => 'nullable|string',
            'City' => 'nullable|string|max:255',
            'Phone' => 'nullable|numeric',
            'Handphone' => 'nullable|numeric',
            'Email' => 'nullable|email',
        ]);

        $taskId = Auth::user()->username;
        $allowed = [
            'Gender', 'Birth_Date', 'Birth_Place', 'KTP',
            'Last_Education', 'Last_Education_From', 'Religion', 'Marital_Status',
            'kewarganegaraan', 'Address', 'City', 'Phone', 'Handphone', 'Email'
        ];
        $data = $request->only($allowed);
        DB::table('recruitment')->updateOrInsert(
            ['Task_ID' => $taskId],
            $data
        );
        return 'success';
    }

    public function documents()
    {
        $taskId = Auth::user()->username;
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();
        $docRecord = DB::table('recruitment_document')->where('Task_ID', $taskId)->first();

        $categories = ['FOTO', 'CV', 'IJAZAH', 'SERTIFIKAT', 'KTP', 'NPWP'];
        $labels = [
            'FOTO' => 'Photo', 'CV' => 'CV / Resume', 'IJAZAH' => 'Diploma',
            'SERTIFIKAT' => 'Certificate', 'KTP' => 'ID Card', 'NPWP' => 'Tax ID',
        ];

        $columnMap = [
            'FOTO' => 'Foto', 'CV' => 'CV', 'IJAZAH' => 'Ijazah',
            'SERTIFIKAT' => 'Sertifikat', 'KTP' => 'KTP', 'NPWP' => 'NPWP',
        ];

        $docStatus = [];
        foreach ($categories as $cat) {
            $col = $columnMap[$cat];
            $filePath = null;
            if ($docRecord && $docRecord->$col) {
                $filePath = $docRecord->$col;
            } elseif ($cat === 'FOTO' && $rec && $rec->Foto) {
                $filePath = $rec->Foto;
            }
            $docStatus[$cat] = [
                'uploaded' => $filePath && file_exists(public_path($filePath)),
                'filePath' => $filePath,
            ];
        }

        return view('humanresources.candidate.documents', compact(
            'rec', 'categories', 'labels', 'docStatus'
        ));
    }

    public function documentUpload(Request $request)
    {
        $taskId = Auth::user()->username;
        $category = $request->input('category');
        $file = $request->file('file');

        if (!$file || !$category) {
            return 'error:Missing file or category';
        }

        $columnMap = [
            'foto' => 'Foto', 'cv' => 'CV', 'ijazah' => 'Ijazah',
            'sertifikat' => 'Sertifikat', 'ktp' => 'KTP', 'npwp' => 'NPWP',
        ];

        $column = $columnMap[$category] ?? null;
        if (!$column) {
            return 'error:Invalid category';
        }

        $ext = $file->getClientOriginalExtension();
        $filename = $category . '.' . $ext;
        $path = 'uploads/recruitment/' . $taskId;

        $file->move(public_path($path), $filename);
        $filePath = $path . '/' . $filename;

        if ($category === 'foto') {
            DB::table('recruitment')->where('Task_ID', $taskId)->update(['Foto' => $filePath]);
        }

        $docRecord = DB::table('recruitment_document')->where('Task_ID', $taskId)->first();
        if ($docRecord) {
            DB::table('recruitment_document')->where('Task_ID', $taskId)->update([$column => $filePath]);
        } else {
            DB::table('recruitment_document')->insert([
                'Task_ID' => $taskId,
                $column => $filePath,
            ]);
        }

        return 'success';
    }

    public function assessment()
    {
        $taskId = Auth::user()->username;
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();
        return view('humanresources.candidate.assessment', compact('rec'));
    }

    public function progress()
    {
        $taskId = Auth::user()->username;
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();

        if (!$rec) {
            return view('humanresources.candidate.progress', [
                'rec' => null,
                'stages' => [],
            ]);
        }

        $stages = [
            ['stage' => 'Application Submitted', 'date' => $rec->Created_Date ?? null, 'status' => $rec->Status ? 'Completed' : '-', 'note' => ''],
            ['stage' => 'Document Review', 'date' => null, 'status' => $rec->Registration_Status ?? '-', 'note' => $rec->Registration_Status_Note ?? ''],
            ['stage' => 'Assessment', 'date' => null, 'status' => ($rec->Psikotest_Score || $rec->Test_Score || $rec->Technical_Score) ? 'Completed' : 'Pending', 'note' => ''],
            ['stage' => 'HR Interview', 'date' => $rec->Interview_Date ?? null, 'status' => $rec->Interview_Status ?? '-', 'note' => $rec->Interview_Note ?? ''],
            ['stage' => 'User Interview', 'date' => $rec->User_Interview_Date ?? null, 'status' => $rec->User_Interview_Status ?? '-', 'note' => $rec->User_Interview_Note ?? ''],
            ['stage' => 'Final Decision', 'date' => null, 'status' => $rec->Status ?? '-', 'note' => $rec->Notes ?? ''],
        ];

        return view('humanresources.candidate.progress', compact('rec', 'stages'));
    }
}
