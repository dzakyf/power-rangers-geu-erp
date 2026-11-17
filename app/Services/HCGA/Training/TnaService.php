<?php

namespace App\Services\HCGA\Training;

use App\Models\HCGA\Training\HcgaTnaItem;
use App\Models\HCGA\Training\HcgaTnaRequest;
use Illuminate\Support\Facades\DB;

class TnaService
{
    public function generateTnaNo(): string
    {
        $prefix = 'TNA/' . date('Y/m') . '/';
        $count = HcgaTnaRequest::where('tna_no', 'like', $prefix . '%')->count() + 1;
        return $prefix . sprintf('%04d', $count);
    }

    public function listTna(array $filters = [])
    {
        $query = HcgaTnaRequest::withCount('items')->orderBy('id', 'desc');
        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['fiscal_year'])) {
            $query->where('fiscal_year', $filters['fiscal_year']);
        }
        return $query->get();
    }

    public function createOrUpdate(array $data, array $items, $user, ?int $id = null): HcgaTnaRequest
    {
        return DB::transaction(function () use ($data, $items, $user, $id) {
            if ($id) {
                $tna = HcgaTnaRequest::findOrFail($id);
                if (!in_array($tna->status, ['DRAFT', 'REJECTED'], true)) {
                    throw new \RuntimeException('TNA yang sudah diajukan/disetujui tidak dapat diubah.');
                }
            } else {
                $tna = new HcgaTnaRequest();
                $tna->tna_no = $this->generateTnaNo();
                $tna->requested_by = $user->username ?? 'system';
                $tna->requester_user_id = $user->id ?? ($user->ID ?? null);
                $tna->status = 'DRAFT';
            }

            $tna->department = $data['department'];
            $tna->fiscal_year = $data['fiscal_year'] ?? date('Y');
            $tna->title = $data['title'];
            $tna->background = $data['background'] ?? null;
            $tna->save();

            // Replace items
            $tna->items()->delete();
            foreach ($items as $item) {
                if (empty($item['employee_username']) && empty($item['recommended_training'])) {
                    continue;
                }
                HcgaTnaItem::create([
                    'tna_id' => $tna->id,
                    'employee_username' => $item['employee_username'] ?? '',
                    'employee_name' => $item['employee_name'] ?? ($item['employee_username'] ?? ''),
                    'position_name' => $item['position_name'] ?? null,
                    'competency_gap' => $item['competency_gap'] ?? '',
                    'recommended_training' => $item['recommended_training'] ?? '',
                    'program_id' => !empty($item['program_id']) ? (int) $item['program_id'] : null,
                    'priority' => $item['priority'] ?? 'MEDIUM',
                    'target_quarter' => $item['target_quarter'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $tna->fresh('items');
        });
    }
}
