<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Models\HCGA\Training\HcgaTrainingTrainer;
use App\Services\HCGA\Training\TrainingTrainerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrainingTrainerController extends Controller
{
    protected $trainerService;

    public function __construct(TrainingTrainerService $trainerService)
    {
        $this->trainerService = $trainerService;
    }

    public function index()
    {
        return view('humanresources.training.trainer.index');
    }

    public function indexXml()
    {
        if (!headers_sent()) {
            header('Content-type: text/xml');
        }
        echo "<?xml version='1.0' encoding='utf-8'?>\n<rows>";

        $trainers = $this->trainerService->listTrainers();
        $no = 1;
        foreach ($trainers as $t) {
            echo "<row id=\"{$t->ID}\">";
            echo "<cell>{$no}</cell>";
            echo "<cell>" . htmlspecialchars((string) $t->Trainer_Name) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $t->Trainer_Type) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $t->Trainer_Company) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($t->Email ?? '-')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($t->Phone ?? '-')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($t->Certificate ?? '-')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $t->Status) . "</cell>";
            echo "</row>";
            $no++;
        }

        echo "</rows>";
    }

    public function form(Request $request)
    {
        $trainer = $request->filled('id')
            ? HcgaTrainingTrainer::findOrFail((int) $request->id)
            : null;
        $employees = DB::table('users')
            ->select('username', 'name', 'email', 'Department')
            ->orderBy('name')
            ->get();

        return view('humanresources.training.trainer.form', compact('trainer', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Trainer_Name' => 'required|string|max:150',
            'Trainer_Type' => 'required|in:INTERNAL,EXTERNAL',
            'Trainer_Company' => 'required|string|max:150',
            'Email' => 'nullable|email|max:150',
            'Phone' => 'nullable|string|max:150',
            'Certificate' => 'nullable|string|max:150',
            'Address' => 'nullable|string',
            'Status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $trainer = $this->trainerService->createOrUpdate(
            $request->only([
                'Trainer_Name', 'Trainer_Type', 'Trainer_Company',
                'Email', 'Phone', 'Certificate', 'Address', 'Status',
            ]),
            Auth::user(),
            $request->filled('id') ? (int) $request->id : null
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Master Pemateri / Trainer berhasil disimpan.',
            'data' => $trainer,
        ]);
    }

    public function toggleStatus(Request $request)
    {
        $id = (int) ($request->input('id') ?: $request->input('ID'));
        $trainer = $this->trainerService->toggleStatus($id, Auth::user());

        return response()->json([
            'status' => 'success',
            'message' => 'Status trainer diubah menjadi ' . $trainer->Status . '.',
            'data' => $trainer,
        ]);
    }
}
