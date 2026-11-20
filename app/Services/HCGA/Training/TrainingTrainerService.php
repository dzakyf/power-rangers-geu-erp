<?php

namespace App\Services\HCGA\Training;

use App\Models\HCGA\Training\HcgaTrainingTrainer;
use Carbon\Carbon;

class TrainingTrainerService
{
    public function listTrainers(bool $activeOnly = false)
    {
        $query = HcgaTrainingTrainer::orderBy('Trainer_Name');
        if ($activeOnly) {
            $query->where('Status', 'ACTIVE');
        }
        return $query->get();
    }

    public function createOrUpdate(array $data, $user, ?int $id = null): HcgaTrainingTrainer
    {
        $username = $user->username ?? 'system';
        $now = Carbon::now();

        if ($id) {
            $trainer = HcgaTrainingTrainer::findOrFail($id);
            $trainer->Updated_By = $username;
            $trainer->Updated_Date = $now;
        } else {
            $trainer = new HcgaTrainingTrainer();
            $trainer->Created_By = $username;
            $trainer->Created_Date = $now;
        }

        $trainer->Trainer_Name = trim((string) $data['Trainer_Name']);
        $trainer->Trainer_Type = strtoupper(trim((string) ($data['Trainer_Type'] ?? 'INTERNAL')));
        $trainer->Trainer_Company = trim((string) ($data['Trainer_Company'] ?? ($trainer->Trainer_Type === 'INTERNAL' ? 'Internal' : '-')));
        $trainer->Email = !empty($data['Email']) ? trim((string) $data['Email']) : null;
        $trainer->Phone = !empty($data['Phone']) ? trim((string) $data['Phone']) : null;
        $trainer->Certificate = !empty($data['Certificate']) ? trim((string) $data['Certificate']) : null;
        $trainer->Address = !empty($data['Address']) ? trim((string) $data['Address']) : null;
        $trainer->Status = strtoupper(trim((string) ($data['Status'] ?? 'ACTIVE')));
        $trainer->save();

        return $trainer;
    }

    public function toggleStatus(int $id, $user): HcgaTrainingTrainer
    {
        $trainer = HcgaTrainingTrainer::findOrFail($id);
        $trainer->Status = strtoupper((string) $trainer->Status) === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $trainer->Updated_By = $user->username ?? 'system';
        $trainer->Updated_Date = Carbon::now();
        $trainer->save();

        return $trainer;
    }
}
