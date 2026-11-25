<?php

namespace App\Services\HCGA\Training;

use App\Models\HCGA\Training\HcgaTnaRequest;
use App\Models\HCGA\Training\HcgaTraining;
use App\Models\HCGA\Training\HcgaTrainingCurriculum;
use App\Models\HCGA\Training\HcgaTrainingParticipant;
use App\Models\HCGA\Training\HcgaTrainingProgram;
use App\Models\HCGA\Training\HcgaTrainingTrainer;
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

    public function getTnaSchedulePayload(int $tnaId, ?int $currentTrainingId = null): array
    {
        $tna = HcgaTnaRequest::with(['items.program'])->findOrFail($tnaId);

        $usernames = $tna->items
            ->pluck('employee_username')
            ->filter(function ($u) {
                return trim((string) $u) !== '';
            })
            ->unique()
            ->values()
            ->all();

        $deptMap = [];
        if (!empty($usernames)) {
            $userRows = DB::table('users')
                ->select('username', 'Department')
                ->whereIn('username', $usernames)
                ->get();
            foreach ($userRows as $uRow) {
                $key = mb_strtolower(trim((string) $uRow->username));
                $deptMap[$key] = $uRow->Department;
            }
        }

        $itemIds = $tna->items->pluck('id')->all();
        $scheduledByItem = [];
        $currentTrainingItemIds = [];
        $currentTrainingUsernames = [];

        if ($currentTrainingId) {
            $currentRows = DB::table('hcga_training_participants')
                ->where('training_id', $currentTrainingId)
                ->select('tna_item_id', 'employee_username')
                ->get();
            foreach ($currentRows as $cRow) {
                if (!empty($cRow->tna_item_id)) {
                    $currentTrainingItemIds[(int) $cRow->tna_item_id] = true;
                }
                if (trim((string) $cRow->employee_username) !== '') {
                    $currentTrainingUsernames[mb_strtolower(trim((string) $cRow->employee_username))] = true;
                }
            }
        }

        if (!empty($itemIds)) {
            $usageRows = DB::table('hcga_training_participants as p')
                ->join('training_hcga as t', 't.ID', '=', 'p.training_id')
                ->whereIn('p.tna_item_id', $itemIds)
                ->whereNotIn(DB::raw("UPPER(COALESCE(t.Status, ''))"), ['REJECTED', 'CANCELLED'])
                ->select('p.tna_item_id', 'p.training_id', 't.Task_No', 't.Training_Name', 't.Status')
                ->orderBy('p.training_id', 'desc')
                ->get();

            foreach ($usageRows as $uRow) {
                $itemId = (int) $uRow->tna_item_id;
                $tid = (int) $uRow->training_id;
                if ($currentTrainingId && $tid === (int) $currentTrainingId) {
                    continue;
                }
                if (!isset($scheduledByItem[$itemId])) {
                    $scheduledByItem[$itemId] = [];
                }
                $scheduledByItem[$itemId][] = [
                    'training_id' => $tid,
                    'task_no' => (string) $uRow->Task_No,
                    'training_name' => (string) $uRow->Training_Name,
                    'status' => (string) $uRow->Status,
                ];
            }
        }

        $items = [];
        $groupsMap = [];
        $allGaps = [];
        $allProgramIds = [];
        $allProgramsById = [];
        $allUnscheduledCount = 0;

        foreach ($tna->items as $item) {
            $itemId = (int) $item->id;
            $uname = trim((string) $item->employee_username);
            $unameLower = mb_strtolower($uname);
            $dept = $deptMap[$unameLower] ?? $tna->department;
            $scheduledList = $scheduledByItem[$itemId] ?? [];
            $isScheduled = count($scheduledList) > 0;
            $inCurrentTraining = $currentTrainingId
                ? (isset($currentTrainingItemIds[$itemId]) || ($unameLower !== '' && isset($currentTrainingUsernames[$unameLower])))
                : false;

            if (!$isScheduled && !$inCurrentTraining) {
                $allUnscheduledCount++;
            }

            $program = $item->program;
            $programId = $item->program_id ? (int) $item->program_id : null;
            $recTraining = trim((string) $item->recommended_training);
            $gap = trim((string) $item->competency_gap);

            if ($gap !== '') {
                $allGaps[$gap] = $gap;
            }
            if ($programId !== null) {
                $allProgramIds[$programId] = $programId;
                if ($program) {
                    $allProgramsById[$programId] = $program;
                }
            } else {
                $allProgramIds['none'] = null;
            }

            if ($programId !== null) {
                $groupKey = 'program:' . $programId;
            } else {
                $normalizedTopic = mb_strtolower(trim((string) preg_replace('/\s+/', ' ', $recTraining)));
                $groupKey = 'topic:' . ($normalizedTopic !== '' ? $normalizedTopic : 'umum');
            }

            $programArr = $program ? [
                'id' => (int) $program->id,
                'program_code' => (string) $program->program_code,
                'program_name' => (string) $program->program_name,
                'category_id' => $program->category_id !== null ? (int) $program->category_id : null,
                'training_type' => (string) $program->training_type,
                'training_method' => (string) $program->training_method,
                'default_passing_grade' => (float) $program->default_passing_grade,
                'objective' => (string) ($program->objective ?? ''),
                'competency' => (string) ($program->competency ?? ''),
                'description' => (string) ($program->description ?? ''),
            ] : null;

            $items[] = [
                'id' => $itemId,
                'tna_id' => (int) $item->tna_id,
                'employee_username' => $uname,
                'employee_name' => (string) $item->employee_name,
                'position_name' => (string) ($item->position_name ?? ''),
                'department' => (string) ($dept ?? ''),
                'competency_gap' => $gap,
                'recommended_training' => $recTraining,
                'program_id' => $programId,
                'program_name' => $program ? (string) $program->program_name : '',
                'priority' => (string) ($item->priority ?? 'MEDIUM'),
                'target_quarter' => (string) ($item->target_quarter ?? ''),
                'group_key' => $groupKey,
                'is_scheduled' => $isScheduled,
                'in_current_training' => $inCurrentTraining,
                'scheduled_trainings' => $scheduledList,
            ];

            if (!isset($groupsMap[$groupKey])) {
                $label = $program
                    ? '[' . $program->program_code . '] ' . $program->program_name
                    : (($recTraining !== '' ? $recTraining : 'Topik Umum') . ' (Tanpa Program)');
                $groupsMap[$groupKey] = [
                    'key' => $groupKey,
                    'label' => $label,
                    'program_id' => $programId,
                    'recommended_training' => $recTraining,
                    'program' => $programArr,
                    'competency_gaps' => [],
                    'total_items' => 0,
                    'unscheduled_count' => 0,
                    'item_ids' => [],
                ];
            }

            if ($gap !== '') {
                $groupsMap[$groupKey]['competency_gaps'][$gap] = $gap;
            }
            $groupsMap[$groupKey]['total_items']++;
            if (!$isScheduled && !$inCurrentTraining) {
                $groupsMap[$groupKey]['unscheduled_count']++;
            }
            $groupsMap[$groupKey]['item_ids'][] = $itemId;
        }

        $groups = [];
        foreach ($groupsMap as $group) {
            $prg = $group['program'];
            $gaps = array_values($group['competency_gaps']);
            $firstRec = $group['recommended_training'];

            $suggestedName = ($prg && trim((string) $prg['program_name']) !== '')
                ? $prg['program_name']
                : ($firstRec !== '' ? $firstRec : (string) $tna->title);
            $suggestedObjective = ($prg && trim((string) $prg['objective']) !== '')
                ? $prg['objective']
                : (string) ($tna->background ?? '');
            $suggestedCompetence = ($prg && trim((string) $prg['competency']) !== '')
                ? $prg['competency']
                : implode('; ', $gaps);

            $group['competency_gaps'] = $gaps;
            $group['suggested_name'] = $suggestedName;
            $group['suggested_objective'] = $suggestedObjective;
            $group['suggested_competence'] = $suggestedCompetence;
            $groups[] = $group;
        }

        $sharedProgram = null;
        $sharedProgramId = null;
        if (count($allProgramIds) === 1 && !array_key_exists('none', $allProgramIds)) {
            $onlyProgramId = (int) reset($allProgramIds);
            $sharedProgramId = $onlyProgramId;
            $prgModel = $allProgramsById[$onlyProgramId] ?? null;
            if ($prgModel) {
                $sharedProgram = [
                    'id' => (int) $prgModel->id,
                    'program_code' => (string) $prgModel->program_code,
                    'program_name' => (string) $prgModel->program_name,
                    'category_id' => $prgModel->category_id !== null ? (int) $prgModel->category_id : null,
                    'training_type' => (string) $prgModel->training_type,
                    'training_method' => (string) $prgModel->training_method,
                    'default_passing_grade' => (float) $prgModel->default_passing_grade,
                    'objective' => (string) ($prgModel->objective ?? ''),
                    'competency' => (string) ($prgModel->competency ?? ''),
                    'description' => (string) ($prgModel->description ?? ''),
                ];
            }
        }

        $allSuggestedName = count($groups) === 1
            ? $groups[0]['suggested_name']
            : (string) $tna->title;
        $allSuggestedObjective = ($sharedProgram && trim((string) $sharedProgram['objective']) !== '')
            ? $sharedProgram['objective']
            : (string) ($tna->background ?? '');
        $allSuggestedCompetence = ($sharedProgram && trim((string) $sharedProgram['competency']) !== '')
            ? $sharedProgram['competency']
            : implode('; ', array_values($allGaps));

        $allGroup = [
            'key' => 'ALL',
            'label' => 'Semua Item TNA (' . count($items) . ' item — Gabung 1 Jadwal)',
            'program_id' => $sharedProgramId,
            'program' => $sharedProgram,
            'suggested_name' => $allSuggestedName,
            'suggested_objective' => $allSuggestedObjective,
            'suggested_competence' => $allSuggestedCompetence,
            'total_items' => count($items),
            'unscheduled_count' => $allUnscheduledCount,
            'item_ids' => array_map(function ($it) {
                return $it['id'];
            }, $items),
        ];

        return [
            'tna' => [
                'id' => (int) $tna->id,
                'tna_no' => (string) $tna->tna_no,
                'department' => (string) $tna->department,
                'fiscal_year' => (string) $tna->fiscal_year,
                'title' => (string) $tna->title,
                'background' => (string) ($tna->background ?? ''),
                'status' => (string) $tna->status,
            ],
            'groups' => $groups,
            'all_group' => $allGroup,
            'items' => $items,
        ];
    }

    public function getLockedParticipantIds(int $trainingId): array
    {
        $rows = DB::table('hcga_training_participants')
            ->where('training_id', $trainingId)
            ->select('id', 'final_score', 'evaluated_at', 'feedback_submitted_at')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $participantIds = $rows->pluck('id')->all();
        $locked = [];

        foreach ($rows as $row) {
            if ($row->final_score !== null || $row->evaluated_at !== null || $row->feedback_submitted_at !== null) {
                $locked[(int) $row->id] = true;
            }
        }

        $feedbackIds = DB::table('hcga_training_feedbacks')
            ->where('training_id', $trainingId)
            ->whereIn('participant_id', $participantIds)
            ->pluck('participant_id')
            ->all();
        foreach ($feedbackIds as $pid) {
            $locked[(int) $pid] = true;
        }

        $certIds = DB::table('hcga_training_certificates')
            ->where('training_id', $trainingId)
            ->whereIn('participant_id', $participantIds)
            ->pluck('participant_id')
            ->all();
        foreach ($certIds as $pid) {
            $locked[(int) $pid] = true;
        }

        $coachingIds = DB::table('hcga_training_coachings')
            ->where('training_id', $trainingId)
            ->whereIn('participant_id', $participantIds)
            ->pluck('participant_id')
            ->all();
        foreach ($coachingIds as $pid) {
            $locked[(int) $pid] = true;
        }

        return array_keys($locked);
    }

    public function assertParticipantsDeletable(int $trainingId, array $participantIds): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $participantIds), function ($v) {
            return $v > 0;
        })));

        if (empty($ids)) {
            return;
        }

        $rows = HcgaTrainingParticipant::where('training_id', $trainingId)
            ->whereIn('id', $ids)
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $lockedLookup = array_flip($this->getLockedParticipantIds($trainingId));
        $lockedNames = [];

        foreach ($rows as $row) {
            if (isset($lockedLookup[(int) $row->id])) {
                $lockedNames[] = $row->employee_name . ' (' . $row->employee_username . ')';
            }
        }

        if (!empty($lockedNames)) {
            throw new \RuntimeException(
                'Peserta tidak dapat dihapus karena sudah memiliki nilai evaluasi, feedback, sertifikat, atau coaching: '
                . implode(', ', $lockedNames)
            );
        }
    }

    protected function normalizeParticipants(array $participants): array
    {
        $deduped = [];
        foreach ($participants as $p) {
            if (!is_array($p)) {
                continue;
            }
            $username = trim((string) ($p['employee_username'] ?? ''));
            if ($username === '') {
                continue;
            }
            $key = mb_strtolower($username);
            if (!isset($deduped[$key])) {
                $p['employee_username'] = $username;
                $deduped[$key] = $p;
            } elseif (empty($deduped[$key]['tna_item_id']) && !empty($p['tna_item_id'])) {
                $deduped[$key]['tna_item_id'] = $p['tna_item_id'];
            }
        }

        return array_values($deduped);
    }

    public function createOrUpdate(array $data, array $curriculums, array $participants, $user, ?int $id = null, array $options = []): HcgaTraining
    {
        return DB::transaction(function () use ($data, $curriculums, $participants, $user, $id, $options) {
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

            if (array_key_exists('program_id', $data)) {
                $program = !empty($data['program_id']) ? HcgaTrainingProgram::find((int) $data['program_id']) : null;
                $training->program_id = $program ? $program->id : null;
            } elseif ($id) {
                $program = $training->program_id ? HcgaTrainingProgram::find((int) $training->program_id) : null;
            } else {
                $program = null;
                $training->program_id = null;
            }

            if (array_key_exists('tna_id', $data)) {
                $training->tna_id = !empty($data['tna_id']) ? (int) $data['tna_id'] : null;
            } elseif (!$id) {
                $training->tna_id = null;
            }

            if (array_key_exists('trainer_id', $data)) {
                $trainer = (!empty($data['trainer_id']) && is_numeric($data['trainer_id']))
                    ? HcgaTrainingTrainer::find((int) $data['trainer_id'])
                    : null;
                $training->trainer_id = $trainer ? $trainer->ID : null;
                $training->trainer_name = $trainer
                    ? $trainer->Trainer_Name
                    : (array_key_exists('trainer_name', $data) && !empty($data['trainer_name'])
                        ? trim((string) $data['trainer_name'])
                        : ($id ? $training->trainer_name : null));
            } elseif (array_key_exists('trainer_name', $data)) {
                $training->trainer_name = !empty($data['trainer_name']) ? trim((string) $data['trainer_name']) : null;
                if (!$id) {
                    $training->trainer_id = null;
                }
            } elseif (!$id) {
                $training->trainer_id = null;
                $training->trainer_name = null;
            }

            if (!$id || array_key_exists('Training_Name', $data)) {
                $training->Training_Name = $data['Training_Name'] ?? ($program ? $program->program_name : ($id ? $training->Training_Name : ''));
            }
            if (!$id || array_key_exists('Training_Category', $data)) {
                $training->Training_Category = $data['Training_Category'] ?? ($program && $program->category_id ? (string) $program->category_id : ($id ? $training->Training_Category : ''));
            }
            if (!$id || array_key_exists('Training_Type', $data)) {
                $training->Training_Type = $data['Training_Type'] ?? ($program ? $program->training_type : ($id ? $training->Training_Type : 'INTERNAL'));
            }
            if (!$id || array_key_exists('Training_Method', $data)) {
                $training->Training_Method = $data['Training_Method'] ?? ($program ? $program->training_method : ($id ? $training->Training_Method : 'OFFLINE'));
            }
            if (!$id || array_key_exists('Training_Description', $data)) {
                $training->Training_Description = $data['Training_Description'] ?? ($program ? $program->description : ($id ? $training->Training_Description : ''));
            }
            if (!$id || array_key_exists('Training_Objective', $data)) {
                $training->Training_Objective = $data['Training_Objective'] ?? ($program ? $program->objective : ($id ? $training->Training_Objective : ''));
            }
            if (!$id || array_key_exists('Training_Competence', $data)) {
                $training->Training_Competence = $data['Training_Competence'] ?? ($program ? $program->competency : ($id ? $training->Training_Competence : ''));
            }
            if (!$id || array_key_exists('Start_Date', $data)) {
                $training->Start_Date = date('Y-m-d', strtotime($data['Start_Date']));
            }
            if (!$id || array_key_exists('End_Date', $data)) {
                $training->End_Date = date('Y-m-d', strtotime($data['End_Date']));
            }
            if (array_key_exists('passing_grade', $data) && $data['passing_grade'] !== null && $data['passing_grade'] !== '') {
                $training->passing_grade = (float) $data['passing_grade'];
            } elseif (!$id) {
                $training->passing_grade = $program ? (float) $program->default_passing_grade : 75.00;
            }
            $training->batch_type = $data['batch_type'] ?? ($training->batch_type ?: 'REGULAR');
            $training->parent_training_id = !empty($data['parent_training_id']) ? (int) $data['parent_training_id'] : $training->parent_training_id;
            if (!$id || array_key_exists('Training_Location', $data)) {
                $training->Training_Location = $data['Training_Location'] ?? ($id ? $training->Training_Location : '');
            }
            if (!$id || array_key_exists('Location', $data)) {
                $training->Location = $data['Location'] ?? ($id ? $training->Location : ($user->Location ?? 'HEAD_OFFICE'));
            }
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
                        'speaker_name' => !empty($cur['speaker_name']) ? $cur['speaker_name'] : $training->trainer_name,
                        'duration_minutes' => !empty($cur['duration_minutes']) ? (int) $cur['duration_minutes'] : 60,
                        'learning_method' => $cur['learning_method'] ?? $training->Training_Method,
                        'notes' => $cur['notes'] ?? null,
                    ]);
                    $order++;
                }
            }

            // Sinkronisasi Peserta
            $validParticipants = $this->normalizeParticipants(is_array($participants) ? $participants : []);
            $removedIds = array_values(array_unique(array_filter(array_map('intval', (array) ($options['removed_participant_ids'] ?? [])), function ($v) {
                return $v > 0;
            })));

            if ($id !== null && (count($validParticipants) > 0 || count($removedIds) > 0)) {
                if (strtoupper((string) $training->Status) === 'CLOSED') {
                    throw new \RuntimeException('Jadwal training berstatus CLOSED tidak dapat diubah daftar pesertanya.');
                }
            }

            if ($id !== null && count($removedIds) > 0) {
                $this->assertParticipantsDeletable((int) $training->ID, $removedIds);
                HcgaTrainingParticipant::where('training_id', $training->ID)
                    ->whereIn('id', $removedIds)
                    ->delete();
            }

            if (count($validParticipants) > 0) {
                $this->syncParticipants($training, $validParticipants);
            } elseif ($id === null && empty($options['skip_tna_autopopulate']) && $training->tna_id && $training->participants()->count() === 0) {
                // Auto-populate peserta dari TNA items yang belum dijadwalkan (fallback ke semua item jika sudah terjawab semua)
                $payload = $this->getTnaSchedulePayload((int) $training->tna_id);
                $unscheduled = array_values(array_filter($payload['items'], function ($item) {
                    return empty($item['is_scheduled']);
                }));
                $sourceItems = count($unscheduled) > 0 ? $unscheduled : $payload['items'];

                $tnaParticipants = [];
                foreach ($sourceItems as $item) {
                    if (trim((string) ($item['employee_username'] ?? '')) === '') {
                        continue;
                    }
                    $tnaParticipants[] = [
                        'tna_item_id' => $item['id'],
                        'employee_username' => $item['employee_username'],
                        'employee_name' => $item['employee_name'],
                        'department' => $item['department'],
                    ];
                }

                $normalizedTnaParticipants = $this->normalizeParticipants($tnaParticipants);
                if (count($normalizedTnaParticipants) > 0) {
                    $this->syncParticipants($training, $normalizedTnaParticipants);
                }
            }

            return $training->fresh(['curriculums', 'participants']);
        });
    }

    public function syncParticipants(HcgaTraining $training, array $participants): void
    {
        foreach ($participants as $p) {
            $username = trim((string) ($p['employee_username'] ?? ''));
            if ($username === '') {
                continue;
            }

            $record = HcgaTrainingParticipant::firstOrNew([
                'training_id' => $training->ID,
                'employee_username' => $username,
            ]);

            if (!$record->exists) {
                $record->tna_item_id = !empty($p['tna_item_id']) ? (int) $p['tna_item_id'] : null;
                $record->employee_name = !empty($p['employee_name']) ? $p['employee_name'] : $username;
                $record->department = $p['department'] ?? null;
                $record->attendance_status = !empty($p['attendance_status']) ? $p['attendance_status'] : 'REGISTERED';
            } else {
                if (empty($record->tna_item_id) && !empty($p['tna_item_id'])) {
                    $record->tna_item_id = (int) $p['tna_item_id'];
                }
                if (!empty($p['employee_name'])) {
                    $record->employee_name = $p['employee_name'];
                }
                if (array_key_exists('department', $p) && $p['department'] !== null && $p['department'] !== '') {
                    $record->department = $p['department'];
                }
                if (!empty($p['attendance_status'])) {
                    $record->attendance_status = $p['attendance_status'];
                }
            }

            $record->save();
        }
    }
}
