<?php

namespace App\Services\HCGA\Training;

use App\Models\HCGA\Training\HcgaTrainingProgram;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TrainingProgramService
{
    public function generateProgramCode(): string
    {
        $prefix = 'PRG/' . date('Y') . '/';
        $count = HcgaTrainingProgram::where('program_code', 'like', $prefix . '%')->count() + 1;
        return $prefix . sprintf('%04d', $count);
    }

    public function listPrograms(bool $activeOnly = false)
    {
        $query = HcgaTrainingProgram::orderBy('id', 'desc');
        if ($activeOnly) {
            $query->where('status', 'ACTIVE');
        }
        return $query->get();
    }

    public function getCategories()
    {
        return DB::table('master_training_hcga_category')->orderBy('Category')->get();
    }

    public function createOrUpdate(array $data, $user, ?UploadedFile $moduleFile = null, ?int $id = null): HcgaTrainingProgram
    {
        if ($id) {
            $program = HcgaTrainingProgram::findOrFail($id);
            $program->updated_by = $user->username ?? 'system';
        } else {
            $program = new HcgaTrainingProgram();
            $program->program_code = !empty($data['program_code']) ? $data['program_code'] : $this->generateProgramCode();
            $program->created_by = $user->username ?? 'system';
        }

        $program->program_name = $data['program_name'];
        $program->category_id = !empty($data['category_id']) ? (int) $data['category_id'] : null;
        $program->training_type = $data['training_type'] ?? 'INTERNAL';
        $program->training_method = $data['training_method'] ?? 'OFFLINE';
        $program->description = $data['description'] ?? null;
        $program->objective = $data['objective'] ?? null;
        $program->competency = $data['competency'] ?? null;
        $program->default_passing_grade = isset($data['default_passing_grade']) ? (float) $data['default_passing_grade'] : 75.00;
        $program->status = $data['status'] ?? 'ACTIVE';

        if ($moduleFile) {
            $path = $moduleFile->store('hcga/training/modules', 'public');
            $program->module_file_path = $path;
        }

        $program->save();

        return $program;
    }
}
