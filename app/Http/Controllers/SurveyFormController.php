<?php

namespace App\Http\Controllers;

use App\Services\Survey\SurveyScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyFormController extends Controller
{
    protected $scoring;

    public function __construct(SurveyScoringService $scoring)
    {
        $this->scoring = $scoring;
    }

    public function index()
    {
        $versions = DB::table('survey_form_versions')->orderByDesc('ID')->get();
        $active = DB::table('survey_form_versions')->where('Status', 'ACTIVE')->orderByDesc('ID')->first();

        return view('partner.survey.form.index', compact('versions', 'active'));
    }

    public function builder(Request $r)
    {
        $versionId = (int) $r->query('version');
        $version = $versionId
            ? $this->scoring->versionWithItems($versionId)
            : $this->scoring->activeVersionWithItems();

        if (!$version) {
            return redirect('partner-survey-form-index')->with('error', 'Form survey belum tersedia.');
        }

        $sections = ['C' => 'C. Dokumentasi Foto Lokasi', 'D' => 'D. Checklist Kriteria Lokasi', 'E' => 'E. Kondisi Lingkungan', 'F' => 'F. Estimasi Pengumpulan UCO'];
        $items = $version->items->groupBy('Section_Code');
        $config = $this->decodeConfig($version->Score_Config);

        return view('partner.survey.form.builder', compact('version', 'sections', 'items', 'config'));
    }

    public function createVersion(Request $r)
    {
        $source = $r->input('source_version')
            ? $this->scoring->versionWithItems((int) $r->input('source_version'))
            : $this->scoring->activeVersionWithItems();

        if (!$source) {
            return redirect('partner-survey-form-index')->with('error', 'Form survey belum tersedia.');
        }

        $newId = $this->cloneVersion($source, 'DRAFT');

        return redirect('partner-survey-form-builder?version=' . $newId)->with('success', 'Draft versi baru dibuat. Silakan sesuaikan lalu publish.');
    }

    public function save(Request $r)
    {
        $r->validate([
            'name' => 'required|string|max:255',
            'pass_threshold' => 'required|numeric|min:0|max:100',
            'conditional_threshold' => 'required|numeric|min:0|max:100',
            'f_max' => 'required|numeric|min:0',
        ]);

        $versionId = (int) $r->input('version_id');
        $source = $this->scoring->versionWithItems($versionId);
        if (!$source) {
            return redirect('partner-survey-form-index')->with('error', 'Versi form tidak ditemukan.');
        }

        $version = $source;
        if ($source->Status === 'ACTIVE') {
            $version = $this->scoring->versionWithItems($this->cloneVersion($source, 'DRAFT'));
        }

        DB::transaction(function () use ($r, $version, $source) {
            if ($source->ID !== $version->ID) {
                DB::table('survey_form_items')->where('Form_Version_ID', $version->ID)->delete();
            }

            $config = [
                'f_max' => (float) $r->input('f_max', 15),
                'f_buckets' => $this->bucketInput($r->input('f_buckets', [])),
            ];

            DB::table('survey_form_versions')->where('ID', $version->ID)->update([
                'Name' => $r->input('name', $version->Name),
                'Pass_Threshold_Pct' => (float) $r->input('pass_threshold', 80),
                'Conditional_Threshold_Pct' => (float) $r->input('conditional_threshold', 70),
                'Score_Config' => json_encode($config),
                'updated_at' => now(),
            ]);

            $this->saveItems($version->ID, $r->input('items', []));

            $items = DB::table('survey_form_items')->where('Form_Version_ID', $version->ID)->get();
            $versionRow = DB::table('survey_form_versions')->where('ID', $version->ID)->first();
            $max = $this->scoring->calculateMaxScore($items, $versionRow);
            DB::table('survey_form_versions')->where('ID', $version->ID)->update(['Max_Score' => $max]);
        });

        $msg = $source->Status === 'ACTIVE'
            ? 'Perubahan disimpan sebagai draft versi baru. Publish untuk menjadikannya form aktif.'
            : 'Draft form berhasil disimpan.';

        return redirect('partner-survey-form-builder?version=' . $version->ID)->with('success', $msg);
    }

    public function publish(Request $r)
    {
        $versionId = (int) $r->input('version_id');
        $version = DB::table('survey_form_versions')->where('ID', $versionId)->first();
        if (!$version) {
            return redirect('partner-survey-form-index')->with('error', 'Versi form tidak ditemukan.');
        }

        DB::transaction(function () use ($versionId) {
            DB::table('survey_form_versions')->where('Status', 'ACTIVE')->where('ID', '!=', $versionId)->update([
                'Status' => 'ARCHIVED',
                'Archived_At' => now(),
                'updated_at' => now(),
            ]);

            DB::table('survey_form_versions')->where('ID', $versionId)->update([
                'Status' => 'ACTIVE',
                'Published_At' => now(),
                'Archived_At' => null,
                'updated_at' => now(),
            ]);
        });

        return redirect('partner-survey-form-index')->with('success', 'Form survey berhasil dipublish. Survey yang sudah ada tetap memakai versinya masing-masing.');
    }

    private function saveItems(int $versionId, array $items): void
    {
        $existing = DB::table('survey_form_items')->where('Form_Version_ID', $versionId)->pluck('ID')->map(function ($id) {
            return (int) $id;
        })->all();
        $kept = [];
        $sort = ['C' => 0, 'D' => 0, 'E' => 0, 'F' => 0];

        foreach ($items as $key => $item) {
            if (!is_array($item)) {
                continue;
            }

            $section = strtoupper($item['section_code'] ?? '');
            if (!in_array($section, ['C', 'D', 'E', 'F'], true)) {
                continue;
            }

            $sort[$section]++;
            $row = [
                'Section_Code' => $section,
                'Item_Code' => trim($item['item_code'] ?? ''),
                'Sort_Order' => $sort[$section],
                'Input_Type' => $item['input_type'] ?? $this->defaultInputType($section),
                'Criteria' => $item['criteria'] ?? null,
                'Keterangan' => $item['keterangan'] ?? null,
                'Minimum_Standard' => $item['minimum_standard'] ?? null,
                'Parameter_Evaluation' => $item['parameter_evaluation'] ?? null,
                'Score_Config' => json_encode($this->itemScoreConfig($item, $section)),
                'File_Required' => !empty($item['file_required']) ? 1 : 0,
                'Notes_Enabled' => !empty($item['notes_enabled']) ? 1 : 0,
                'Is_Active' => 1,
                'updated_at' => now(),
            ];

            if ($row['Item_Code'] === '') {
                continue;
            }

            $id = (int) ($item['id'] ?? 0);
            if ($id && in_array($id, $existing, true)) {
                DB::table('survey_form_items')->where('ID', $id)->update($row);
                $kept[] = $id;
            } else {
                $row['Form_Version_ID'] = $versionId;
                $row['created_at'] = now();
                $kept[] = DB::table('survey_form_items')->insertGetId($row);
            }
        }

        $toDelete = array_diff($existing, $kept);
        if ($toDelete) {
            DB::table('survey_form_items')->whereIn('ID', $toDelete)->delete();
        }
    }

    private function itemScoreConfig(array $item, string $section): array
    {
        if ($section === 'C' || $section === 'D') {
            return [
                'yes' => (float) ($item['yes_score'] ?? 0),
                'no' => (float) ($item['no_score'] ?? 0),
            ];
        }

        if ($section === 'E') {
            $options = [];
            foreach (($item['options'] ?? []) as $option) {
                if (!is_array($option) || !isset($option['label'])) {
                    continue;
                }
                $options[] = [
                    'label' => $option['label'],
                    'value' => (int) ($option['value'] ?? (count($options) + 1)),
                    'score' => (float) ($option['score'] ?? 0),
                ];
            }
            if (!$options) {
                $options = [
                    ['label' => 'Kurang', 'value' => 1, 'score' => 1],
                    ['label' => 'Sedang', 'value' => 2, 'score' => 3],
                    ['label' => 'Tinggi', 'value' => 3, 'score' => 5],
                ];
            }
            return ['options' => $options];
        }

        return [];
    }

    private function bucketInput($buckets): array
    {
        $result = [];
        foreach ((array) $buckets as $bucket) {
            if (!is_array($bucket)) {
                continue;
            }
            $row = ['score' => (float) ($bucket['score'] ?? 0)];
            if (isset($bucket['gt']) && $bucket['gt'] !== '') {
                $row['gt'] = (float) $bucket['gt'];
            } elseif (isset($bucket['gte']) && $bucket['gte'] !== '') {
                $row['gte'] = (float) $bucket['gte'];
            } else {
                $row['gte'] = 0;
            }
            $result[] = $row;
        }

        return $result ?: [
            ['gt' => 15, 'score' => 15],
            ['gte' => 10, 'score' => 12],
            ['gte' => 6, 'score' => 8],
            ['gte' => 4, 'score' => 5],
            ['gte' => 0, 'score' => 2],
        ];
    }

    private function cloneVersion($source, string $status): int
    {
        $nextNo = (int) DB::table('survey_form_versions')->max('Version_No') + 1;
        $id = DB::table('survey_form_versions')->insertGetId([
            'Version_No' => $nextNo,
            'Name' => $source->Name . ' (copy)',
            'Status' => $status,
            'Max_Score' => $source->Max_Score,
            'Pass_Threshold_Pct' => $source->Pass_Threshold_Pct,
            'Conditional_Threshold_Pct' => $source->Conditional_Threshold_Pct,
            'Score_Config' => $source->Score_Config,
            'Created_By' => optional(auth()->user())->username,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($source->items as $item) {
            DB::table('survey_form_items')->insert([
                'Form_Version_ID' => $id,
                'Section_Code' => $item->Section_Code,
                'Item_Code' => $item->Item_Code,
                'Sort_Order' => $item->Sort_Order,
                'Input_Type' => $item->Input_Type,
                'Criteria' => $item->Criteria,
                'Keterangan' => $item->Keterangan,
                'Minimum_Standard' => $item->Minimum_Standard,
                'Parameter_Evaluation' => $item->Parameter_Evaluation,
                'Score_Config' => $item->Score_Config,
                'File_Required' => $item->File_Required,
                'Notes_Enabled' => $item->Notes_Enabled,
                'Is_Active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $id;
    }

    private function decodeConfig($config): array
    {
        if (is_array($config)) {
            return $config;
        }
        $decoded = is_string($config) ? json_decode($config, true) : [];
        return is_array($decoded) ? $decoded : [];
    }

    private function defaultInputType(string $section): string
    {
        return [
            'C' => 'boolean_photo',
            'D' => 'boolean_checklist',
            'E' => 'rating',
            'F' => 'estimation',
        ][$section];
    }
}
