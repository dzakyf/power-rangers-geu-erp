<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Models\HCGA\Training\HcgaTrainingProgram;
use App\Services\HCGA\Training\TrainingProgramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingProgramController extends Controller
{
    protected $programService;

    public function __construct(TrainingProgramService $programService)
    {
        $this->programService = $programService;
    }

    public function index()
    {
        return view('humanresources.training.program.index');
    }

    public function indexXml()
    {
        header('Content-type: text/xml');
        echo "<?xml version='1.0' encoding='utf-8'?>\n<rows>";
        $programs = $this->programService->listPrograms();
        $categories = $this->programService->getCategories()->pluck('Category', 'ID');
        $no = 1;
        foreach ($programs as $p) {
            $catName = $categories[$p->category_id] ?? '-';
            echo "<row id=\"{$p->id}\">";
            echo "<cell>{$no}</cell>";
            echo "<cell>" . htmlspecialchars((string) $p->program_code) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $p->program_name) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $catName) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $p->training_type) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $p->training_method) . "</cell>";
            echo "<cell>" . number_format((float) $p->default_passing_grade, 2) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $p->status) . "</cell>";
            echo "</row>";
            $no++;
        }
        echo "</rows>";
    }

    public function form(Request $request)
    {
        $program = $request->filled('id')
            ? HcgaTrainingProgram::findOrFail((int) $request->id)
            : null;
        $categories = $this->programService->getCategories();
        return view('humanresources.training.program.form', compact('program', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_name' => 'required|string|max:255',
            'training_type' => 'required|string|max:50',
            'training_method' => 'required|string|max:50',
            'default_passing_grade' => 'required|numeric|min:0|max:100',
            'module_file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
        ]);

        $program = $this->programService->createOrUpdate(
            $request->only([
                'program_code', 'program_name', 'category_id', 'training_type',
                'training_method', 'description', 'objective', 'competency',
                'default_passing_grade', 'status',
            ]),
            Auth::user(),
            $request->file('module_file'),
            $request->filled('id') ? (int) $request->id : null
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Module & Program Training berhasil disimpan.',
            'data' => $program,
        ]);
    }
}
