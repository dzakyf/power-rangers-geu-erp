<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ApprovalPolicyResolver
{
    /**
     * Resolve the most specific active policy for a request context.
     *
     * Required context keys: Request_Type, Location, Company_ID, Department.
     * Optional: Amount and any field referenced by policy Conditions.
     */
    public function resolve(array $context): array
    {
        $context = $this->normalizeContext($context);
        $today = Carbon::now()->toDateString();

        $candidates = DB::table('approval_policy')
            ->where('Request_Type', $context['Request_Type'])
            ->where('Status', 'ACTIVE')
            ->where(function ($query) use ($today) {
                // NULL, string kosong, dan 0000-00-00 diperlakukan sebagai
                // "tanpa batas" agar policy tanpa tanggal berakhir tetap match.
                $query->whereNull('Effective_From')
                    ->orWhere('Effective_From', '')
                    ->orWhere('Effective_From', '0000-00-00')
                    ->orWhere('Effective_From', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('Effective_To')
                    ->orWhere('Effective_To', '')
                    ->orWhere('Effective_To', '0000-00-00')
                    ->orWhere('Effective_To', '>=', $today);
            })
            ->get();

        $matches = [];

        foreach ($candidates as $policy) {
            if (!$this->scopeMatches($policy, $context)) {
                continue;
            }

            if (!$this->amountMatches($policy, $context)) {
                continue;
            }

            $conditions = $this->decodeConditions($policy->Conditions);
            if (!$this->conditionsMatch($conditions, $context)) {
                continue;
            }

            $policy->Conditions = $conditions;
            $matches[] = [
                'policy' => $policy,
                'score' => $this->specificityScore($policy, $conditions),
            ];
        }

        if (empty($matches)) {
            $summary = 'Request_Type=' . $context['Request_Type']
                . ', Location=' . $context['Location']
                . ', Company_ID=' . $context['Company_ID']
                . ', Department=' . ($context['Department'] ?: '-')
                . (isset($context['Position_Level']) ? ', Position_Level=' . $context['Position_Level'] : '');

            throw new ApprovalWorkflowException(
                'Tidak ada policy approval aktif yang cocok untuk konteks ini (' . $summary . ').',
                'POLICY_NOT_FOUND',
                404,
                ['context' => $context]
            );
        }

        usort($matches, function ($a, $b) {
            if ($a['score'] === $b['score']) {
                $versionA = (int) $a['policy']->Version;
                $versionB = (int) $b['policy']->Version;
                if ($versionA === $versionB) {
                    return (int) $b['policy']->ID <=> (int) $a['policy']->ID;
                }

                return $versionB <=> $versionA;
            }

            return $b['score'] <=> $a['score'];
        });

        $policy = $matches[0]['policy'];
        $steps = $this->steps((int) $policy->ID);

        return [
            'policy' => $policy,
            'steps' => $steps,
            'snapshot' => $this->snapshot($policy, $steps),
        ];
    }

    public function steps(int $policyId): array
    {
        return DB::table('approval_policy_step')
            ->where('Policy_ID', $policyId)
            ->orderBy('Stage_Order', 'ASC')
            ->orderBy('Level_Number', 'ASC')
            ->orderBy('ID', 'ASC')
            ->get()
            ->all();
    }

    public function policyById(int $policyId)
    {
        return DB::table('approval_policy')->where('ID', $policyId)->first();
    }

    public function validatePolicy($policy, array $steps): void
    {
        if (empty($steps)) {
            throw new ApprovalWorkflowException(
                'Policy aktif harus memiliki minimal satu step approval.',
                'POLICY_INVALID',
                422,
                ['Policy_ID' => $policy->ID]
            );
        }

        if ((string) $policy->Rejection_Mode === 'WAIT_GROUP' && empty($policy->Rejection_Quorum)) {
            throw new ApprovalWorkflowException(
                'Policy WAIT_GROUP wajib memiliki Rejection_Quorum.',
                'POLICY_INVALID',
                422,
                ['Policy_ID' => $policy->ID]
            );
        }

        $stageModes = [];
        $stageQuorums = [];
        foreach ($steps as $step) {
            $mode = strtoupper((string) $step->Approval_Mode);
            if (!in_array($mode, ['ALL', 'ANY', 'QUORUM'], true)) {
                throw new ApprovalWorkflowException(
                    'Approval_Mode tidak dikenal: ' . $mode,
                    'POLICY_INVALID',
                    422,
                    ['Step_ID' => $step->ID]
                );
            }

            if ($mode === 'QUORUM' && (int) $step->Quorum < 1) {
                throw new ApprovalWorkflowException(
                    'Approval_Mode QUORUM wajib memiliki Quorum minimal 1.',
                    'POLICY_INVALID',
                    422,
                    ['Step_ID' => $step->ID]
                );
            }

            $stage = (int) $step->Stage_Order;
            if (isset($stageModes[$stage]) && $stageModes[$stage] !== $mode) {
                throw new ApprovalWorkflowException(
                    'Semua step dalam satu Stage_Order harus memakai Approval_Mode yang sama.',
                    'POLICY_INVALID',
                    422,
                    ['Stage_Order' => $stage]
                );
            }
            $stageModes[$stage] = $mode;

            if ($mode === 'QUORUM') {
                $quorum = (int) $step->Quorum;
                if (isset($stageQuorums[$stage]) && $stageQuorums[$stage] !== $quorum) {
                    throw new ApprovalWorkflowException(
                        'Semua step QUORUM dalam satu Stage_Order harus memakai Quorum yang sama.',
                        'POLICY_INVALID',
                        422,
                        ['Stage_Order' => $stage]
                    );
                }
                $stageQuorums[$stage] = $quorum;
            }
        }
    }

    public function snapshot($policy, array $steps): array
    {
        return [
            'Policy' => (array) $policy,
            'Steps' => array_map(function ($step) {
                return (array) $step;
            }, $steps),
            'Captured_At' => Carbon::now()->toDateTimeString(),
        ];
    }

    public function normalizeContext(array $context): array
    {
        $context['Request_Type'] = trim((string) ($context['Request_Type'] ?? ''));
        $context['Location'] = trim((string) ($context['Location'] ?? ''));
        $context['Company_ID'] = trim((string) ($context['Company_ID'] ?? ''));
        $context['Department'] = trim((string) ($context['Department'] ?? ''));
        $context['Amount'] = isset($context['Amount']) && $context['Amount'] !== '' ? (float) $context['Amount'] : 0.0;

        if ($context['Request_Type'] === '') {
            throw new ApprovalWorkflowException('Request_Type wajib diisi.', 'CONTEXT_INVALID', 422);
        }
        if ($context['Location'] === '' || $context['Company_ID'] === '') {
            throw new ApprovalWorkflowException('Location dan Company_ID wajib diisi untuk isolasi approval.', 'CONTEXT_INVALID', 422);
        }

        return $context;
    }

    private function scopeMatches($policy, array $context): bool
    {
        if (!empty($policy->Location) && (string) $policy->Location !== $context['Location']) {
            return false;
        }
        if (!empty($policy->Company_ID) && (string) $policy->Company_ID !== $context['Company_ID']) {
            return false;
        }
        if (!empty($policy->Department) && (string) $policy->Department !== $context['Department']) {
            return false;
        }

        return true;
    }

    private function amountMatches($policy, array $context): bool
    {
        $amount = (float) $context['Amount'];

        if ($policy->Min_Amount !== null && $amount < (float) $policy->Min_Amount) {
            return false;
        }
        if ($policy->Max_Amount !== null && $amount > (float) $policy->Max_Amount) {
            return false;
        }

        return true;
    }

    private function specificityScore($policy, array $conditions): int
    {
        $score = 0;
        if (!empty($policy->Location)) {
            $score += 1000;
        }
        if (!empty($policy->Company_ID)) {
            $score += 500;
        }
        if (!empty($policy->Department)) {
            $score += 300;
        }
        if ($policy->Min_Amount !== null || $policy->Max_Amount !== null) {
            $score += 200;
        }
        $score += count($conditions) * 100;

        return $score;
    }

    private function decodeConditions($conditions): array
    {
        if (is_array($conditions)) {
            return $conditions;
        }
        if (empty($conditions)) {
            return [];
        }

        $decoded = json_decode((string) $conditions, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function conditionsMatch(array $conditions, array $context): bool
    {
        foreach ($conditions as $condition) {
            if (!is_array($condition)) {
                return false;
            }

            $field = (string) ($condition['field'] ?? '');
            $operator = strtolower((string) ($condition['operator'] ?? '='));
            $expected = $condition['value'] ?? null;

            if ($field === '' || !array_key_exists($field, $context)) {
                return false;
            }

            $actual = $context[$field];

            switch ($operator) {
                case '=':
                case '==':
                    if (!$this->looseEquals($actual, $expected)) {
                        return false;
                    }
                    break;
                case '!=':
                case '<>':
                    if ($this->looseEquals($actual, $expected)) {
                        return false;
                    }
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    if (!is_numeric($actual) || !is_numeric($expected)) {
                        return false;
                    }
                    $a = (float) $actual;
                    $b = (float) $expected;
                    if ($operator === '>' && !($a > $b)) return false;
                    if ($operator === '>=' && !($a >= $b)) return false;
                    if ($operator === '<' && !($a < $b)) return false;
                    if ($operator === '<=' && !($a <= $b)) return false;
                    break;
                case 'in':
                    $values = is_array($expected) ? $expected : explode(',', (string) $expected);
                    $found = false;
                    foreach ($values as $value) {
                        if ($this->looseEquals($actual, trim((string) $value))) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) return false;
                    break;
                case 'not_in':
                    $values = is_array($expected) ? $expected : explode(',', (string) $expected);
                    foreach ($values as $value) {
                        if ($this->looseEquals($actual, trim((string) $value))) {
                            return false;
                        }
                    }
                    break;
                case 'contains':
                    if (stripos((string) $actual, (string) $expected) === false) {
                        return false;
                    }
                    break;
                case 'exists':
                    if ($actual === null || $actual === '') {
                        return false;
                    }
                    break;
                case 'not_exists':
                    if ($actual !== null && $actual !== '') {
                        return false;
                    }
                    break;
                default:
                    return false;
            }
        }

        return true;
    }

    private function looseEquals($actual, $expected): bool
    {
        if (is_numeric($actual) && is_numeric($expected)) {
            return (float) $actual === (float) $expected;
        }

        return strcasecmp(trim((string) $actual), trim((string) $expected)) === 0;
    }
}