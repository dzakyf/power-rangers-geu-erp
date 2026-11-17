<?php

namespace App\Services\Survey;

use Illuminate\Support\Facades\DB;

class SurveyScoringService
{
    public function activeVersion()
    {
        return DB::table('survey_form_versions')
            ->where('Status', 'ACTIVE')
            ->orderByDesc('ID')
            ->first();
    }

    public function versionWithItems($versionId)
    {
        $versionId = (int) $versionId;
        if (!$versionId) {
            return null;
        }

        $version = DB::table('survey_form_versions')->where('ID', $versionId)->first();
        if (!$version) {
            return null;
        }

        $version->items = DB::table('survey_form_items')
            ->where('Form_Version_ID', $versionId)
            ->where('Is_Active', 1)
            ->orderBy('Section_Code')
            ->orderBy('Sort_Order')
            ->orderBy('ID')
            ->get();

        $version->Max_Score = $this->calculateMaxScore($version->items, $version);

        return $version;
    }

    public function activeVersionWithItems()
    {
        $version = $this->activeVersion();

        return $version ? $this->versionWithItems($version->ID) : null;
    }

    public function calculate($version, $items, array $answers): array
    {
        $items = $items instanceof \Illuminate\Support\Collection ? $items : collect($items);
        $sectionScores = ['C' => 0.0, 'D' => 0.0, 'E' => 0.0, 'F' => 0.0];
        $sectionMax = ['C' => 0.0, 'D' => 0.0, 'E' => 0.0, 'F' => 0.0];
        $sectionMetrics = ['F' => 0.0];
        $normalized = [];

        foreach ($items as $item) {
            $code = (string) $item->Item_Code;
            $section = strtoupper((string) $item->Section_Code);
            $answer = $answers[$code] ?? [];
            $config = $this->decodeConfig($item->Score_Config);
            $value = $this->answerValue($answer, 'value');
            $score = 0.0;
            $itemMax = 0.0;
            $answerJson = ['value' => $value];

            if ($item->Input_Type === 'boolean_photo' || $item->Input_Type === 'boolean_checklist') {
                $score = ((string) $value === '1')
                    ? (float) ($config['yes'] ?? 0)
                    : (float) ($config['no'] ?? 0);
                $itemMax = (float) ($config['yes'] ?? 0);
            } elseif ($item->Input_Type === 'rating') {
                $options = $config['options'] ?? [];
                foreach ($options as $option) {
                    $itemMax = max($itemMax, (float) ($option['score'] ?? 0));
                    if ((string) ($option['value'] ?? '') === (string) $value) {
                        $score = (float) ($option['score'] ?? 0);
                    }
                }
            } elseif ($item->Input_Type === 'estimation') {
                $sourceValue = (float) $this->answerValue($answer, 'source_value');
                $ucoEstimation = (float) $this->answerValue($answer, 'uco_estimation');
                $answerJson = [
                    'source_value' => $sourceValue,
                    'uco_estimation' => $ucoEstimation,
                    'total' => $sourceValue * $ucoEstimation,
                ];
                $sectionMetrics['F'] += $ucoEstimation;
            }

            $sectionScores[$section] = ($sectionScores[$section] ?? 0) + $score;
            $sectionMax[$section] = ($sectionMax[$section] ?? 0) + $itemMax;

            $normalized[$code] = [
                'item' => $item,
                'value' => $value,
                'score' => $score,
                'answer_json' => $answerJson,
                'notes' => $this->answerValue($answer, 'notes'),
            ];
        }

        $hasFItems = $items->where('Section_Code', 'F')->count() > 0;
        $sectionScores['F'] = $hasFItems ? $this->scoreFBuckets($version, $sectionMetrics['F']) : 0;
        $sectionMax['F'] = $hasFItems ? $this->fMaxScore($version) : 0;

        $maxScore = array_sum($sectionMax);
        if ($maxScore <= 0 && $version) {
            $maxScore = (float) $version->Max_Score;
        }
        $total = array_sum($sectionScores);
        $percentage = $maxScore > 0 ? round(($total / $maxScore) * 100, 2) : 0;

        return [
            'items' => $normalized,
            'section_scores' => $sectionScores,
            'section_max' => $sectionMax,
            'section_metrics' => $sectionMetrics,
            'total' => round($total, 2),
            'max_score' => round($maxScore, 2),
            'percentage' => $percentage,
            'recommendation' => $this->recommendation($version, $percentage),
        ];
    }

    public function calculateMaxScore($items, $version = null): float
    {
        $items = $items instanceof \Illuminate\Support\Collection ? $items : collect($items);
        $max = 0.0;

        foreach ($items as $item) {
            $config = $this->decodeConfig($item->Score_Config);
            if ($item->Input_Type === 'boolean_photo' || $item->Input_Type === 'boolean_checklist') {
                $max += (float) ($config['yes'] ?? 0);
            } elseif ($item->Input_Type === 'rating') {
                $itemMax = 0.0;
                foreach (($config['options'] ?? []) as $option) {
                    $itemMax = max($itemMax, (float) ($option['score'] ?? 0));
                }
                $max += $itemMax;
            }
        }

        if ($items->where('Section_Code', 'F')->count() > 0) {
            $max += $this->fMaxScore($version);
        }

        return round($max, 2);
    }

    public function recommendation($version, float $percentage): string
    {
        $pass = $version ? (float) $version->Pass_Threshold_Pct : 80;
        $conditional = $version ? (float) $version->Conditional_Threshold_Pct : 70;

        if ($percentage >= $pass) {
            return 'Layak';
        }
        if ($percentage >= $conditional) {
            return 'Layak Bersyarat';
        }

        return 'Tidak Layak';
    }

    public function fMaxScore($version): float
    {
        $config = $version ? $this->decodeConfig($version->Score_Config) : [];
        if (isset($config['f_max'])) {
            return (float) $config['f_max'];
        }

        $max = 0.0;
        foreach (($config['f_buckets'] ?? []) as $bucket) {
            $max = max($max, (float) ($bucket['score'] ?? 0));
        }

        return $max > 0 ? $max : 15;
    }

    private function scoreFBuckets($version, float $metric): float
    {
        $config = $version ? $this->decodeConfig($version->Score_Config) : [];
        $buckets = $config['f_buckets'] ?? [];
        usort($buckets, function ($a, $b) {
            $aValue = isset($a['gt']) ? (float) $a['gt'] : (float) ($a['gte'] ?? 0);
            $bValue = isset($b['gt']) ? (float) $b['gt'] : (float) ($b['gte'] ?? 0);

            return $bValue <=> $aValue;
        });

        foreach ($buckets as $bucket) {
            if (isset($bucket['gt']) && $metric > (float) $bucket['gt']) {
                return (float) ($bucket['score'] ?? 0);
            }
            if (isset($bucket['gte']) && $metric >= (float) $bucket['gte']) {
                return (float) ($bucket['score'] ?? 0);
            }
        }

        return 0.0;
    }

    private function answerValue(array $answer, string $key)
    {
        return $answer[$key] ?? null;
    }

    private function decodeConfig($config): array
    {
        if (is_array($config)) {
            return $config;
        }
        if (is_string($config) && $config !== '') {
            $decoded = json_decode($config, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
