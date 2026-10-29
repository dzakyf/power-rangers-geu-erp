<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OrganizationChartController extends Controller
{
    public function index()
    {
        $departments = DB::table('organization_structure')
            ->whereNotNull('Department')
            ->where('Department', '<>', '')
            ->distinct()
            ->orderBy('Department')
            ->pluck('Department');

        $directorats = DB::table('organization_structure')
            ->whereNotNull('Directorat')
            ->where('Directorat', '<>', '')
            ->distinct()
            ->orderBy('Directorat')
            ->pluck('Directorat');

        $canManage = Gate::allows('approval.policy.manage');

        return view('humanresources.organization-chart', compact('departments', 'directorats', 'canManage'));
    }

    public function data(Request $request)
    {
        $department     = trim((string) $request->query('department', ''));
        $directorat     = trim((string) $request->query('directorat', ''));
        $includeSystem  = (int) $request->query('include_system', 0) === 1;
        $masterPosition = DB::table('master_position')->get()->pluck('Position', 'Initial');

        $query = DB::table('organization_structure');

        if ($department !== '') {
            $query->where('Department', $department);
        }
        if ($directorat !== '') {
            $query->where('Directorat', $directorat);
        }
        if (!$includeSystem) {
            $query->where('Is_System', 0);
        }

        $rawNodes = $query->orderBy('Sort_Order', 'asc')->orderBy('ID', 'asc')->get();

        $nodes = [];
        $roots = [];
        $issues = [];
        $nikCounts = [];
        $loadedIds = [];

        foreach ($rawNodes as $n) {
            $name = trim((string) ($n->Name ?? ''));
            $nik = trim((string) ($n->NIK ?? ''));
            $isVacant = $nik === '' || strtoupper($name) === 'VACANT';
            $parentId = !empty($n->Reports_To_Position_ID) ? (int) $n->Reports_To_Position_ID : null;
            $level = (int) ($n->Level ?? 8);

            $nodes[] = [
                'id'          => (int) $n->ID,
                'position_id' => (int) ($n->Position_ID ?? 0),
                'nik'         => $nik !== '' ? $nik : '-',
                'name'        => $isVacant ? 'VACANT KOSONG' : $name,
                'position'    => $n->Position ? $masterPosition[$n->Position] : '-',
                'department'  => $n->Department,
                'dept_name'   => $n->Dept_Name ?: $n->Department,
                'directorat'  => $n->Directorat ?: '-',
                'email'       => $n->Email ?: '-',
                'level'       => $level,
                'level_name'  => org_chart_level_name($level),
                'parent_id'   => $parentId,
                'is_vacant'   => $isVacant,
                'is_system'   => (int) $n->Is_System,
                'sort_order'  => (int) $n->Sort_Order,
            ];

            $loadedIds[(int) $n->ID] = true;

            if (!$isVacant && $nik !== '') {
                $nikCounts[$nik] = ($nikCounts[$nik] ?? 0) + 1;
            }
        }

        foreach ($nodes as $node) {
            if ($node['parent_id'] === null || !isset($loadedIds[$node['parent_id']])) {
                $roots[] = (int) $node['id'];
                if ($node['parent_id'] !== null) {
                    $issues[] = [
                        'type' => 'MISSING_PARENT',
                        'message' => $node['position'] . ' menunjuk atasan yang tidak ada di filter bagan.',
                    ];
                }
            }
        }

        foreach ($nikCounts as $nik => $count) {
            if ($count > 1) {
                $issues[] = ['type' => 'DUPLICATE_NIK', 'message' => "NIK $nik dipakai $count baris"];
            }
        }

        if (count($roots) > 1) {
            $issues[] = ['type' => 'MULTI_ROOT', 'message' => count($roots) . ' node tidak punya atasan (multi-root)'];
        }

        foreach ($nodes as $node) {
            if ($node['position'] === '-') {
                $issues[] = ['type' => 'NO_POSITION', 'message' => $node['name'] . ' belum punya jabatan'];
            }
        }

        return response()->json([
            'status' => 'success',
            'nodes'  => $nodes,
            'roots'  => array_values($roots),
            'issues' => $issues,
        ]);
    }

    public function saveParent(Request $request)
    {
        if (!Gate::allows('approval.policy.manage')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya admin yang dapat mengubah struktur organisasi.',
            ], 403);
        }

        $id       = (int) $request->input('ID');
        $parentId = $request->input('Parent_ID');
        $parentId = ($parentId === null || $parentId === '' || (int) $parentId === 0) ? null : (int) $parentId;

        $child = DB::table('organization_structure')->where('ID', $id)->first();
        if (!$child) {
            return response()->json(['status' => 'error', 'message' => 'Node bagan tidak ditemukan.'], 404);
        }

        if ($parentId !== null && $parentId === $id) {
            return response()->json(['status' => 'error', 'message' => 'Posisi atasan tidak boleh dirinya sendiri.'], 422);
        }

        // Cegah siklus posisi (looping atasan-bawahan)
        if ($parentId !== null && $this->isDescendant($id, $parentId)) {
            return response()->json(['status' => 'error', 'message' => 'Posisi atasan tidak valid (membentuk siklus turunan).'], 422);
        }

        $parent = null;
        if ($parentId !== null) {
            $parent = DB::table('organization_structure')->where('ID', $parentId)->first();
            if (!$parent) {
                return response()->json(['status' => 'error', 'message' => 'Posisi atasan tidak ditemukan.'], 422);
            }
        }

        DB::transaction(function () use ($id, $parentId, $child, $parent) {
            DB::table('organization_structure')
                ->where('ID', $id)
                ->update(['Reports_To_Position_ID' => $parentId]);

            // Jaga agar fallback approval mengikuti hierarki bagan.
            if (!empty($child->Position_ID)) {
                DB::table('master_position')
                    ->where('ID', (int) $child->Position_ID)
                    ->update([
                        'Parent_Position_ID' => ($parent && !empty($parent->Position_ID)) ? (int) $parent->Position_ID : null,
                        'Updated_By' => 'organization-chart',
                        'Updated_Date' => now(),
                    ]);
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Atasan posisi berhasil diperbarui.',
        ]);
    }

    private function isDescendant($parentId, $childId)
    {
        $current = DB::table('organization_structure')->where('ID', $childId)->first();
        while ($current && $current->Reports_To_Position_ID) {
            if ((int) $current->Reports_To_Position_ID === (int) $parentId) {
                return true;
            }
            $current = DB::table('organization_structure')->where('ID', $current->Reports_To_Position_ID)->first();
        }
        return false;
    }
}