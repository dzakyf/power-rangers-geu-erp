<?php

namespace App\Services\HCGA\Training;

use App\Models\HCGA\Training\HcgaTraining;
use App\Models\HCGA\Training\HcgaTrainingCoaching;
use App\Models\HCGA\Training\HcgaTrainingParticipant;
use Illuminate\Support\Facades\DB;

class TrainingCoachingService
{
    protected $scheduleService;

    public function __construct(TrainingScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function getFailedParticipants(?int $trainingId = null)
    {
        $query = HcgaTrainingParticipant::with(['training', 'coachings'])
            ->where('is_passed', false)
            ->orderBy('id', 'desc');

        if ($trainingId) {
            $query->where('training_id', $trainingId);
        }

        return $query->get();
    }

    public function recordCoaching(array $data, $user): HcgaTrainingCoaching
    {
        $participant = HcgaTrainingParticipant::with('training')
            ->findOrFail((int) $data['participant_id']);

        return DB::transaction(function () use ($data, $participant, $user) {
            $coaching = HcgaTrainingCoaching::create([
                'training_id' => $participant->training_id,
                'participant_id' => $participant->id,
                'mentor_username' => $data['mentor_username'],
                'mentor_name' => $data['mentor_name'] ?? $data['mentor_username'],
                'coaching_date' => date('Y-m-d', strtotime($data['coaching_date'])),
                'notes' => $data['notes'],
                'action_plan' => $data['action_plan'] ?? null,
                'follow_up_type' => $data['follow_up_type'] ?? 'COACHING_ONLY',
                'status' => $data['status'] ?? 'OPEN',
                'created_by' => $user->username ?? 'system',
            ]);

            // Jika tindak lanjut adalah Training Refreshment dan diminta auto-create jadwal baru
            if (($data['follow_up_type'] ?? '') === 'REFRESHMENT' && !empty($data['refreshment_start_date'])) {
                $parent = $participant->training;
                $refreshment = $this->scheduleService->createOrUpdate([
                    'tna_id' => $parent->tna_id,
                    'program_id' => $parent->program_id,
                    'trainer_id' => $parent->trainer_id,
                    'trainer_name' => $parent->trainer_name,
                    'Training_Name' => $parent->Training_Name . ' (Refreshment)',
                    'Training_Category' => $parent->Training_Category,
                    'Training_Type' => $parent->Training_Type,
                    'Training_Method' => $parent->Training_Method,
                    'Training_Description' => $parent->Training_Description,
                    'Training_Objective' => $parent->Training_Objective,
                    'Training_Competence' => $parent->Training_Competence,
                    'Start_Date' => $data['refreshment_start_date'],
                    'End_Date' => $data['refreshment_end_date'] ?? $data['refreshment_start_date'],
                    'passing_grade' => $parent->passing_grade,
                    'batch_type' => 'REFRESHMENT',
                    'parent_training_id' => $parent->ID,
                    'Training_Location' => $parent->Training_Location,
                    'Location' => $parent->Location,
                    'Status' => 'APPROVED',
                ], [], [
                    [
                        'tna_item_id' => $participant->tna_item_id,
                        'employee_username' => $participant->employee_username,
                        'employee_name' => $participant->employee_name,
                        'department' => $participant->department,
                    ],
                ], $user);

                $coaching->refreshment_training_id = $refreshment->ID;
                $coaching->save();
            }

            return $coaching->fresh(['participant', 'refreshmentTraining']);
        });
    }
}
