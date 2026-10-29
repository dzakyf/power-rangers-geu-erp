<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ApprovalPolicyController extends Controller
{
    const CONDITION_FIELDS = [
        'Position_Level' => 'Level Posisi',
        'Position_Code' => 'Kode Posisi',
        'Department' => 'Departemen Posisi',
        'Target_Department' => 'Departemen Target',
        'Location' => 'Lokasi',
        'Company_ID' => 'Company',
        'Amount' => 'Nilai Pengajuan',
        'Request_Type' => 'Tipe Request',
    ];

    const CONDITION_OPERATORS = [
        '=' => 'Sama dengan',
        '!=' => 'Tidak sama dengan',
        '>' => 'Lebih besar dari',
        '>=' => 'Lebih besar atau sama dengan',
        '<' => 'Lebih kecil dari',
        '<=' => 'Lebih kecil atau sama dengan',
        'in' => 'Termasuk salah satu',
        'not_in' => 'Tidak termasuk',
        'contains' => 'Mengandung teks',
        'exists' => 'Terisi / tidak kosong',
    ];

    public function index()
    {
        $this->authorizePolicyManagement();

        return view('humanresources.approval.policy');
    }

    public function data()
    {
        $this->authorizePolicyManagement();

        if (!headers_sent()) {
            header('Content-type: text/xml; charset=UTF-8');
        }

        $policies = DB::table('approval_policy as p')
            ->select('p.*', DB::raw('(SELECT COUNT(*) FROM approval_policy_step s WHERE s.Policy_ID = p.ID) AS Step_Count'))
            ->orderBy('p.Request_Type', 'ASC')
            ->orderBy('p.Version', 'DESC')
            ->orderBy('p.ID', 'DESC')
            ->get();

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rows>';

        $no = 1;
        foreach ($policies as $policy) {
            echo '<row id="' . (int) $policy->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($policy->Policy_Code) . '</cell>';
            echo '<cell>' . $this->xml($policy->Policy_Name) . '</cell>';
            echo '<cell>' . $this->xml($policy->Request_Type) . '</cell>';
            echo '<cell>' . $this->xml($policy->Location) . '</cell>';
            echo '<cell>' . $this->xml($policy->Company_ID) . '</cell>';
            echo '<cell>' . $this->xml($policy->Department) . '</cell>';
            echo '<cell>' . (int) $policy->Version . '</cell>';
            echo '<cell>' . $this->xml($policy->Rejection_Mode) . '</cell>';
            echo '<cell>' . $this->xml($policy->On_Vacancy) . '</cell>';
            echo '<cell>' . $this->xml($policy->On_No_Higher_Level) . '</cell>';
            echo '<cell>' . (int) $policy->Step_Count . '</cell>';
            echo '<cell>' . $this->xml($policy->Status) . '</cell>';
            echo '</row>';
            $no++;
        }

        echo '</rows>';
    }

    public function detail(Request $request)
    {
        $this->authorizePolicyManagement();

        $policyId = (int) $request->input('ID');
        $policy = $policyId ? DB::table('approval_policy')->where('ID', $policyId)->first() : null;

        $steps = $policyId
            ? DB::table('approval_policy_step')->where('Policy_ID', $policyId)->orderBy('Stage_Order', 'ASC')->orderBy('ID', 'ASC')->get()
            : collect();

        $positions = DB::table('master_position')
            ->orderBy('Level', 'ASC')
            ->orderBy('Position', 'ASC')
            ->get();

        $conditionRows = [];
        if ($policy && !empty($policy->Conditions)) {
            $decoded = json_decode($policy->Conditions, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                if (isset($decoded['field'])) {
                    $decoded = [$decoded];
                }
                foreach ($decoded as $condition) {
                    if (!is_array($condition)) {
                        continue;
                    }
                    $value = $condition['value'] ?? '';
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    $operator = strtolower((string) ($condition['operator'] ?? '='));
                    if ($operator === '==') {
                        $operator = '=';
                    } elseif ($operator === '<>') {
                        $operator = '!=';
                    }

                    $conditionRows[] = [
                        'field' => (string) ($condition['field'] ?? ''),
                        'operator' => $operator,
                        'value' => (string) $value,
                    ];
                }
            }
        }

        $conditionFields = self::CONDITION_FIELDS;
        $conditionOperators = self::CONDITION_OPERATORS;

        return view('humanresources.approval.policy-detail', compact(
            'policy',
            'steps',
            'positions',
            'conditionRows',
            'conditionFields',
            'conditionOperators'
        ));
    }

    public function save(Request $request)
    {
        $this->authorizePolicyManagement();

        $id = (int) $request->input('ID');
        $data = $this->validatePolicy($request);

        $now = now();
        $username = Auth::user()->username;

        if ($id > 0) {
            $exists = DB::table('approval_policy')->where('ID', $id)->exists();
            if (!$exists) {
                return redirect('hrd-approval-policy')->with('error', 'Policy tidak ditemukan.');
            }

            $duplicate = DB::table('approval_policy')
                ->where('Policy_Code', $data['Policy_Code'])
                ->where('ID', '!=', $id)
                ->exists();

            if ($duplicate) {
                return redirect('hrd-approval-policy')->with('error', 'Policy Code sudah digunakan.');
            }

            if ($data['Status'] === 'ACTIVE' && DB::table('approval_policy_step')->where('Policy_ID', $id)->count() === 0) {
                return redirect('hrd-approval-policy')->with('error', 'Policy harus memiliki minimal satu step sebelum diaktifkan.');
            }

            $data['Updated_By'] = $username;
            $data['Updated_At'] = $now;
            DB::table('approval_policy')->where('ID', $id)->update($data);

            return redirect('hrd-approval-policy')->with('success', 'Policy berhasil diperbarui.');
        }

        $duplicate = DB::table('approval_policy')->where('Policy_Code', $data['Policy_Code'])->exists();
        if ($duplicate) {
            return redirect('hrd-approval-policy')->with('error', 'Policy Code sudah digunakan.');
        }

        if ($data['Status'] === 'ACTIVE') {
            return redirect('hrd-approval-policy')->with('error', 'Policy baru harus disimpan sebagai DRAFT, lalu tambahkan step sebelum diaktifkan.');
        }

        $data['Created_By'] = $username;
        $data['Created_At'] = $now;
        $newId = DB::table('approval_policy')->insertGetId($data);

        return redirect('hrd-approval-policy')->with('success', 'Policy berhasil dibuat.');
    }

    public function saveStep(Request $request)
    {
        $this->authorizePolicyManagement();

        $policyId = (int) $request->input('Policy_ID');
        if (!DB::table('approval_policy')->where('ID', $policyId)->exists()) {
            return redirect('hrd-approval-policy')->with('error', 'Policy tidak ditemukan.');
        }

        $stage = (int) $request->input('Stage_Order');
        $level = (int) $request->input('Level_Number');
        $mode = strtoupper(trim((string) $request->input('Approval_Mode', 'ALL')));
        $quorum = $request->input('Quorum');

        if ($stage < 1 || $level < 1) {
            return redirect('hrd-approval-policy')->with('error', 'Stage dan Level harus lebih besar dari 0.');
        }
        if (!in_array($mode, ['ALL', 'ANY', 'QUORUM'], true)) {
            return redirect('hrd-approval-policy')->with('error', 'Approval mode tidak valid.');
        }
        if ($mode === 'QUORUM' && (int) $quorum < 1) {
            return redirect('hrd-approval-policy')->with('error', 'Quorum wajib diisi untuk mode QUORUM.');
        }

        $positionId = $request->input('Position_ID') ? (int) $request->input('Position_ID') : null;
        $positionCode = null;
        if ($positionId) {
            $position = DB::table('master_position')->where('ID', $positionId)->first();
            if (!$position) {
                return redirect('hrd-approval-policy')->with('error', 'Position tidak ditemukan.');
            }
            $positionCode = $position->Initial;
        }

        $data = [
            'Policy_ID' => $policyId,
            'Stage_Order' => $stage,
            'Level_Number' => $level,
            'Position_ID' => $positionId,
            'Position_Code' => $positionCode,
            'Department' => $request->input('Department') ?: null,
            'Approval_Mode' => $mode,
            'Quorum' => $mode === 'QUORUM' ? (int) $quorum : null,
            'Is_Required' => $request->input('Is_Required', 1) ? 1 : 0,
            'Label' => $request->input('Label') ?: null,
        ];

        $stepId = (int) $request->input('Step_ID');
        if ($stepId > 0) {
            $step = DB::table('approval_policy_step')->where('ID', $stepId)->where('Policy_ID', $policyId)->first();
            if (!$step) {
                return redirect('hrd-approval-policy')->with('error', 'Step tidak ditemukan.');
            }

            DB::table('approval_policy_step')->where('ID', $stepId)->update($data);
            $message = 'Step approval berhasil diperbarui.';
        } else {
            $data['Created_At'] = now();
            DB::table('approval_policy_step')->insert($data);
            $message = 'Step approval berhasil ditambahkan.';
        }

        DB::table('approval_policy')->where('ID', $policyId)->update([
            'Updated_By' => Auth::user()->username,
            'Updated_At' => now(),
        ]);

        return redirect('hrd-approval-policy')->with('success', $message);
    }

    public function deleteStep(Request $request)
    {
        $this->authorizePolicyManagement();

        $stepId = (int) $request->input('Step_ID');
        $step = DB::table('approval_policy_step')->where('ID', $stepId)->first();

        if (!$step) {
            return redirect('hrd-approval-policy')->with('error', 'Step tidak ditemukan.');
        }

        $policyId = (int) $step->Policy_ID;
        DB::table('approval_policy_step')->where('ID', $stepId)->delete();

        if (DB::table('approval_policy_step')->where('Policy_ID', $policyId)->count() === 0) {
            DB::table('approval_policy')->where('ID', $policyId)->update([
                'Status' => 'DRAFT',
                'Updated_By' => Auth::user()->username,
                'Updated_At' => now(),
            ]);
        }

        return redirect('hrd-approval-policy')->with('success', 'Step approval berhasil dihapus.');
    }

    public function toggle(Request $request)
    {
        $this->authorizePolicyManagement();

        $id = (int) $request->input('ID');
        $policy = DB::table('approval_policy')->where('ID', $id)->first();
        if (!$policy) {
            return redirect('hrd-approval-policy')->with('error', 'Policy tidak ditemukan.');
        }

        $newStatus = strtoupper((string) $policy->Status) === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';

        if ($newStatus === 'ACTIVE' && DB::table('approval_policy_step')->where('Policy_ID', $id)->count() === 0) {
            return redirect('hrd-approval-policy')->with('error', 'Policy harus memiliki minimal satu step sebelum diaktifkan.');
        }

        DB::table('approval_policy')->where('ID', $id)->update([
            'Status' => $newStatus,
            'Updated_By' => Auth::user()->username,
            'Updated_At' => now(),
        ]);

        return redirect('hrd-approval-policy')->with('success', 'Status policy diubah menjadi ' . $newStatus . '.');
    }

    private function validatePolicy(Request $request): array
    {
        $code = trim((string) $request->input('Policy_Code'));
        $name = trim((string) $request->input('Policy_Name'));
        $type = trim((string) $request->input('Request_Type'));

        if ($code === '' || $name === '' || $type === '') {
            throw \Illuminate\Validation\ValidationException::withMessages(['Policy_Code' => 'Policy Code, Nama, dan Request Type wajib diisi.']);
        }

        $rejection = strtoupper(trim((string) $request->input('Rejection_Mode', 'RETURN_TO_CREATOR')));
        $vacancy = strtoupper(trim((string) $request->input('On_Vacancy', 'FALLBACK')));
        $noHigher = strtoupper(trim((string) $request->input('On_No_Higher_Level', 'AUTO_APPROVE')));
        $status = strtoupper(trim((string) $request->input('Status', 'DRAFT')));

        if (!in_array($rejection, ['RETURN_TO_CREATOR', 'STOP', 'WAIT_GROUP'], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['Rejection_Mode' => 'Rejection mode tidak valid.']);
        }
        if (!in_array($vacancy, ['FALLBACK', 'HOLD', 'BLOCK'], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['On_Vacancy' => 'On Vacancy tidak valid.']);
        }
        if (!in_array($noHigher, ['AUTO_APPROVE', 'HOLD', 'BLOCK'], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['On_No_Higher_Level' => 'On No Higher Level tidak valid.']);
        }
        if (!in_array($status, ['DRAFT', 'ACTIVE', 'INACTIVE'], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['Status' => 'Status policy tidak valid.']);
        }
        $effectiveFrom = $request->input('Effective_From') ?: null;
        $effectiveTo = $request->input('Effective_To') ?: null;

        if ($effectiveFrom && $effectiveTo && strtotime($effectiveTo) < strtotime($effectiveFrom)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['Effective_To' => 'Effective To tidak boleh lebih awal dari Effective From.']);
        }

        $conditions = $this->validateConditions($request);

        return [
            'Policy_Code' => $code,
            'Policy_Name' => $name,
            'Request_Type' => $type,
            'Location' => $request->input('Location') ?: null,
            'Company_ID' => $request->input('Company_ID') ?: null,
            'Department' => $request->input('Department') ?: null,
            'Min_Amount' => $request->input('Min_Amount') !== null && $request->input('Min_Amount') !== '' ? (float) $request->input('Min_Amount') : null,
            'Max_Amount' => $request->input('Max_Amount') !== null && $request->input('Max_Amount') !== '' ? (float) $request->input('Max_Amount') : null,
            'Conditions' => $conditions,
            'Rejection_Mode' => $rejection,
            'Rejection_Quorum' => $request->input('Rejection_Quorum') ? (int) $request->input('Rejection_Quorum') : null,
            'On_No_Higher_Level' => $noHigher,
            'On_Vacancy' => $vacancy,
            'Allow_Self_Approval' => $request->input('Allow_Self_Approval') ? 1 : 0,
            'Allow_Delegation' => $request->input('Allow_Delegation') ? 1 : 0,
            'Status' => $status,
            'Version' => max(1, (int) $request->input('Version', 1)),
            'Effective_From' => $effectiveFrom,
            'Effective_To' => $effectiveTo,
        ];
    }

    private function validateConditions(Request $request): ?string
    {
        $rawJson = trim((string) $request->input('Conditions', ''));
        if ($rawJson !== '') {
            return $this->validateConditionsJson($rawJson);
        }

        return $this->validateConditionRows($request);
    }

    private function validateConditionsJson(string $raw): ?string
    {
        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'Conditions' => 'Format condition tidak valid.',
            ]);
        }

        // Dukung satu object tunggal agar input lama tetap terbaca.
        if (isset($decoded['field'])) {
            $decoded = [$decoded];
        }

        $allowedOperators = array_merge(array_keys(self::CONDITION_OPERATORS), ['==', '<>']);
        $normalized = [];

        foreach (array_values($decoded) as $index => $condition) {
            if (!is_array($condition)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'Conditions' => 'Condition #' . ($index + 1) . ' harus berupa object.',
                ]);
            }

            $field = trim((string) ($condition['field'] ?? ''));
            $operator = strtolower(trim((string) ($condition['operator'] ?? '=')));

            if ($field === '') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'Conditions' => 'Condition #' . ($index + 1) . ' wajib memiliki field.',
                ]);
            }

            if (!in_array($operator, $allowedOperators, true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'Conditions' => 'Operator condition #' . ($index + 1) . ' tidak dikenal: ' . $operator,
                ]);
            }

            if (!array_key_exists('value', $condition) && $operator !== 'exists') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'Conditions' => 'Condition #' . ($index + 1) . ' wajib memiliki value.',
                ]);
            }

            $value = $condition['value'] ?? null;

            if (in_array($operator, ['in', 'not_in'], true)) {
                if (is_string($value)) {
                    $value = array_values(array_filter(array_map('trim', explode(',', $value)), function ($item) {
                        return $item !== '';
                    }));
                }

                if (!is_array($value)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'Conditions' => 'Value condition #' . ($index + 1) . ' harus berupa array untuk operator ' . $operator . '.',
                    ]);
                }
            }

            if (in_array($operator, ['>', '>=', '<', '<='], true) && !is_numeric($value)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'Conditions' => 'Value condition #' . ($index + 1) . ' harus angka untuk operator ' . $operator . '.',
                ]);
            }

            $normalized[] = [
                'field' => $field,
                'operator' => $operator,
                'value' => $value,
            ];
        }

        if (empty($normalized)) {
            return null;
        }

        return json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function validateConditionRows(Request $request): ?string
    {
        $fields = $request->input('Condition_Field', []);
        $operators = $request->input('Condition_Operator', []);
        $values = $request->input('Condition_Value', []);

        if (!is_array($fields)) {
            $fields = [$fields];
        }
        if (!is_array($operators)) {
            $operators = [$operators];
        }
        if (!is_array($values)) {
            $values = [$values];
        }

        $count = max(count($fields), count($operators), count($values));
        $normalized = [];

        for ($i = 0; $i < $count; $i++) {
            $field = trim((string) ($fields[$i] ?? ''));
            $operator = strtolower(trim((string) ($operators[$i] ?? '=')));
            $rawValue = $values[$i] ?? '';
            $valueText = is_array($rawValue) ? implode(',', $rawValue) : trim((string) $rawValue);

            // Baris kosong dari form diabaikan.
            if ($field === '' && $valueText === '' && $operator === '=') {
                continue;
            }

            $rowNumber = $i + 1;

            if ($field === '') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'Conditions' => 'Baris condition #' . $rowNumber . ': pilih field terlebih dahulu.',
                ]);
            }

            if (!array_key_exists($field, self::CONDITION_FIELDS)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'Conditions' => 'Baris condition #' . $rowNumber . ': field tidak dikenal.',
                ]);
            }

            if (!array_key_exists($operator, self::CONDITION_OPERATORS)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'Conditions' => 'Baris condition #' . $rowNumber . ': operator tidak dikenal.',
                ]);
            }

            if ($operator === 'exists') {
                $value = null;
            } elseif (in_array($operator, ['in', 'not_in'], true)) {
                $items = is_array($rawValue) ? $rawValue : explode(',', $valueText);
                $items = array_map(function ($item) {
                    $item = trim((string) $item);
                    if ($item === '') {
                        return '';
                    }
                    if (is_numeric($item)) {
                        return strpos($item, '.') !== false ? (float) $item : (int) $item;
                    }

                    return $item;
                }, $items);
                $items = array_values(array_filter($items, function ($item) {
                    return $item !== '';
                }));

                if (empty($items)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'Conditions' => 'Baris condition #' . $rowNumber . ': isi minimal satu nilai, pisahkan dengan koma.',
                    ]);
                }

                $value = $items;
            } else {
                if ($valueText === '') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'Conditions' => 'Baris condition #' . $rowNumber . ': nilai wajib diisi.',
                    ]);
                }

                if (in_array($operator, ['>', '>=', '<', '<='], true) && !is_numeric($valueText)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'Conditions' => 'Baris condition #' . $rowNumber . ': nilai harus berupa angka.',
                    ]);
                }

                $value = $valueText;
            }

            $normalized[] = [
                'field' => $field,
                'operator' => $operator,
                'value' => $value,
            ];
        }

        if (empty($normalized)) {
            return null;
        }

        return json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    private function authorizePolicyManagement(): void
    {
        if (!Gate::allows('approval.policy.manage')) {
            abort(403, 'Hanya wildcard admin atau pemegang izin approval.policy.manage yang dapat mengelola policy.');
        }
    }

    private function xml($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}