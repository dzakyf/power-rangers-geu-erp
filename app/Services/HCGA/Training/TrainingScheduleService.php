<?php

namespace App\Services\HCGA\Training;

use App\Models\HCGA\Training\HcgaTnaRequest;
use App\Models\HCGA\Training\HcgaTraining;
use App\Models\HCGA\Training\HcgaTrainingCurriculum;
use App\Models\HCGA\Training\HcgaTrainingParticipant;
use App\Models\HCGA\Training\HcgaTrainingProgram;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrainingScheduleService
{
    public function generateTaskNo($user): string
    {
        $lsp = $user->Location ?? 'HO';
        $username = $user->username ?? 'sys';
        $counter = HcgaTraining::where('Created_Date', 'LIKE', date('Y') . '%')->count() + 1;
        return 'TRN/' . $lsp . '/' . substr(md5($username), 0, 5) . '/' . sprintf('%04d', $counter);
    }

    public function listTrainings(array $filters = [])
    {
        $query = HcgaTraining::withCount('participants')->orderBy('ID', 'desc');
        if (!empty($filters['status'])) {
            $query->where('Status', $filters['status']);
        }
        if (!empty($filters['batch_type'])) {
            $query->where('batch_type', $filters['batch_type']);
        }
        return $query->get();
    }

    public function createOrUpdate(array $data, array $curriculums, array $participants, $user, ?int $id = null): HcgaTraining
    {
        return DB::transaction(function () use ($data, $curriculums, $participants, $user, $id) {
            $username = $user->username ?? 'system';
            if ($id) {
                $training = HcgaTraining::findOrFail($id);
                $training->Updated_By = $username;
                $training->Updated_Date = Carbon::now();
            } else {
                $training = new HcgaTraining();
                $training->Task_No = !empty($data['Task_No']) ? $data['Task_No'] : $this->generateTaskNo($user);
                $training->Status = $data['Status'] ?? 'DRAFT';
                $training->Created_By = $username;
                $training->Created_Date = Carbon::now();
                $training->Total_Cost = 0;
                $training->Currency = 'IDR';
            }

            $program = !empty($data['program_id']) ? HcgaTrainingProgram::find($data['program_id']) : null;

            $training->tna_id = !empty($data['tna_id']) ? (int) $data['tna_id'] : null;
            $training->program_id = $program ? $program->id : null;
            $training->trainer_id = !empty($data['trainer_id']) ? (int) $data['trainer_id'] : null;
            $training->trainer_name = $data['trainer_name'] ?? null;
            $training->Training_Name = $data['Training_Name'] ?? ($program ? $program->program_name : '');
            $training->Training_Category = $data['Training_Category'] ?? ($program && $program->category_id ? (string) $program->category_id : '');
            $training->Training_Type = $data['Training_Type'] ?? ($program ? $program->training_type : 'INTERNAL');
            $training->Training_Method = $data['Training_Method'] ?? ($program ? $program->training_method : 'OFFLINE');
            $training->Training_Description = $data['Training_Description'] ?? ($program ? $program->description : '');
            $training->Training_Objective = $data['Training_Objective'] ?? ($program ? $program->objective : '');
            $training->Training_Competence = $data['Training_Competence'] ?? ($program ? $program->competency : '');
            $training->Start_Date = date('Y-m-d', strtotime($data['Start_Date']));
            $training->End_Date = date('Y-m-d', strtotime($data['End_Date']));
            $training->passing_grade = isset($data['passing_grade'])
                ? (float) $data['passing_grade']
                : ($program ? (float) $program->default_passing_grade : 75.00);
            $training->batch_type = $data['batch_type'] ?? ($training->batch_type ?: 'REGULAR');
            $training->parent_training_id = !empty($data['parent_training_id']) ? (int) $data['parent_training_id'] : $training->parent_training_id;
            $training->Training_Location = $data['Training_Location'] ?? '';
            $training->Location = $data['Location'] ?? ($user->Location ?? 'HEAD_OFFICE');
            $training->save();

            // Sinkronisasi Kurikulum
            if (is_array($curriculums)) {
                $training->curriculums()->delete();
                $order = 1;
                foreach ($curriculums as $cur) {
                    if (empty($cur['topic_title'])) {
                        continue;
                    }
                    HcgaTrainingCurriculum::create([
                        'training_id' => $training->ID,
                        'session_order' => !empty($cur['session_order']) ? (int) $cur['session_order'] : $order,
                        'topic_title' => $cur['topic_title'],
                        'speaker_name' => $cur['speaker_name'] ?? $training->trainer_name,
                        'duration_minutes' => !empty($cur['duration_minutes']) ? (int) $cur['duration_minutes'] : 60,
                        'learning_method' => $cur['learning_method'] ?? $training->Training_Method,
                        'notes' => $cur['notes'] ?? null,
                    ]);
                    $order++;
                }
            }

            // Sinkronisasi Peserta
            if (is_array($participants) && count($participants) > 0) {
                $this->syncParticipants($training, $participants);
            } elseif ($training->tna_id && $training->participants()->count() === 0) {
                // Auto-populate peserta dari TNA items jika belum ada
                $tna = HcgaTnaRequest::with('items')->find($training->tna_id);
                if ($tna) {
                    $tnaParticipants = [];
                    foreach ($tna->items as $item) {
                        $tnaParticipants[] = [
                            'tna_item_id' => $item->id,
                            'employee_username' => $item->employee_username,
                            'employee_name' => $item->employee_name,
                            'department' => $tna->department,
                        ];
                    }
                    $this->syncParticipants($training, $tnaParticipants);
                }
            }

            return $training->fresh(['curriculums', 'participants']);
        });
    }

    public function syncParticipants(HcgaTraining $training, array $participants): void
    {
        foreach ($participants as $p) {
            if (empty($p['employee_username'])) {
                continue;
            }
            HcgaTrainingParticipant::updateOrCreate(
                [
                    'training_id' => $training->ID,
                    'employee_username' => $p['employee_username'],
                ],
                [
                    'tna_item_id' => !empty($p['tna_item_id']) ? (int) $p['tna_item_id'] : null,
                    'employee_name' => $p['employee_name'] ?? $p['employee_username'],
                    'department' => $p['department'] ?? null,
                    'attendance_status' => $p['attendance_status'] ?? 'REGISTERED',
                ]
            );
        }
    }
}
