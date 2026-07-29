<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class OnboardingController extends Controller
{
    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function index()
    {
        return view('humanresources.onboarding.index');
    }

    public function onboardingData()
    {
        $materials = DB::table('onboarding_materials as om')
            ->join('meta_files as mf', 'om.Meta_File_ID', '=', 'mf.id')
            ->orderBy('om.created_at', 'DESC')
            ->get();

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="iso-8859-1"?><rows></rows>');
        $counter = 1;

        foreach ($materials as $m) {
            $row = $xml->addChild('row');
            $row->addAttribute('id', $m->id);
            $row->addChild('cell', $counter);
            $row->addChild('cell', htmlspecialchars($m->Title));
            $row->addChild('cell', htmlspecialchars($m->Category ?? ''));
            $row->addChild('cell', htmlspecialchars($m->Description ?? ''));
            $fileUrl = asset('filevendor/' . $m->Path);
            $docCell = $row->addChild('cell');
            $dom = dom_import_simplexml($docCell);
            $cdata = $dom->ownerDocument->createCDATASection('<a href="' . $fileUrl . '" target="_blank">Dokumen</a>');
            $dom->appendChild($cdata);
            $row->addChild('cell', htmlspecialchars($m->Created_By ?? ''));
            $row->addChild('cell', htmlspecialchars($m->created_at));
            $counter++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'text/xml'
        ]);
    }

    public function create()
    {
        return view('humanresources.onboarding.create');
    }

    public function edit(Request $request)
    {
        $id = $request->input('id');
        $material = DB::table('onboarding_materials as om')
            ->join('meta_files as mf', 'om.Meta_File_ID', '=', 'mf.id')
            ->where('om.id', $id)
            ->first();

        if (!$material) {
            abort(404, 'Material not found');
        }

        return view('humanresources.onboarding.edit', compact('material'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Title' => 'required|string|max:255',
            'pdf_file' => 'required|file|mimes:pdf|max:10240',
            'Category' => 'nullable|string|max:255',
            'Description' => 'nullable|string',
        ]);

        $fileId = $this->uploadService->storeFile(
            $request->file('pdf_file'),
            'file',
            'onboarding-material',
            Auth::id(),
            $request->input('Title')
        );

        DB::table('onboarding_materials')->insert([
            'Title' => $request->input('Title'),
            'Description' => $request->input('Description'),
            'Category' => $request->input('Category'),
            'Meta_File_ID' => $fileId,
            'Created_By' => Auth::user()->username,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Material created.']);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:onboarding_materials,id',
            'Title' => 'required|string|max:255',
            'Category' => 'nullable|string|max:255',
            'Description' => 'nullable|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $id = $request->input('id');
        $material = DB::table('onboarding_materials')->where('id', $id)->first();

        $updateData = [
            'Title' => $request->input('Title'),
            'Description' => $request->input('Description'),
            'Category' => $request->input('Category'),
            'Updated_By' => Auth::user()->username,
            'updated_at' => now(),
        ];

        if ($request->hasFile('pdf_file')) {
            $oldFile = DB::table('meta_files')->where('id', $material->Meta_File_ID)->first();
            if ($oldFile) {
                $oldPath = public_path('filevendor/' . $oldFile->Path);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
                DB::table('meta_files')->where('id', $material->Meta_File_ID)->delete();
            }

            $fileId = $this->uploadService->storeFile(
                $request->file('pdf_file'),
                'file',
                'onboarding-material',
                Auth::id(),
                $request->input('Title')
            );
            $updateData['Meta_File_ID'] = $fileId;
        }

        DB::table('onboarding_materials')->where('id', $id)->update($updateData);

        return response()->json(['status' => 'success', 'message' => 'Material updated.']);
    }

    public function delete(Request $request)
    {
        $id = $request->input('id');
        $material = DB::table('onboarding_materials')->where('id', $id)->first();

        if (!$material) {
            return response()->json(['status' => 'error', 'message' => 'Material not found.'], 404);
        }

        $file = DB::table('meta_files')->where('id', $material->Meta_File_ID)->first();
        if ($file) {
            $filePath = public_path('filevendor/' . $file->Path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            DB::table('meta_files')->where('id', $material->Meta_File_ID)->delete();
        }

        DB::table('onboarding_materials')->where('id', $id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Material deleted.']);
    }
}
