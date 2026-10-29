@php $isEdit = $policy !== null; @endphp

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<style>
    .policy-help-content { max-height: 65vh; overflow-y: auto; padding-right: 4px; }
    .policy-help-table th { width: 28%; vertical-align: top; }
    .policy-help-table td { vertical-align: top; }
    .policy-help-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 17px;
        height: 17px;
        margin-left: 4px;
        padding: 0;
        border: 1px solid #17a2b8;
        border-radius: 50%;
        background: transparent;
        color: #17a2b8;
        font-family: inherit;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        vertical-align: text-top;
        cursor: pointer;
    }
    .policy-help-link:hover { background: #17a2b8; color: #fff; text-decoration: none; }
    .policy-help-highlight,
    .policy-help-highlight th,
    .policy-help-highlight td { background-color: #fff3cd !important; transition: background-color .3s; }
</style>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>{{ $isEdit ? 'Edit Policy #' . $policy->ID : 'New Approval Policy' }}</strong>
        <button type="button" class="btn btn-sm btn-outline-info" onclick="PolicyHelp();">Panduan Pengisian</button>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ url('hrd-approval-policy-save') }}">
            @csrf
            <input type="hidden" name="ID" value="{{ $isEdit ? $policy->ID : 0 }}" />

            <div class="form-row">
                <div class="form-group col-md-3">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Policy Code *', 'helpKey' => 'Policy_Code'])
                    <input type="text" name="Policy_Code" class="form-control" value="{{ old('Policy_Code', $policy->Policy_Code ?? '') }}" required />
                </div>
                <div class="form-group col-md-5">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Policy Name *', 'helpKey' => 'Policy_Name'])
                    <input type="text" name="Policy_Name" class="form-control" value="{{ old('Policy_Name', $policy->Policy_Name ?? '') }}" required />
                </div>
                <div class="form-group col-md-2">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Version', 'helpKey' => 'Version'])
                    <input type="number" name="Version" class="form-control" min="1" value="{{ old('Version', $policy->Version ?? 1) }}" />
                </div>
                <div class="form-group col-md-2">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Status', 'helpKey' => 'Status'])
                    <select name="Status" class="form-control">
                        @foreach(['DRAFT','ACTIVE','INACTIVE'] as $status)
                        <option value="{{ $status }}" {{ old('Status', $policy->Status ?? 'DRAFT') === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Request Type *', 'helpKey' => 'Request_Type'])
                    <input type="text" name="Request_Type" class="form-control" value="{{ old('Request_Type', $policy->Request_Type ?? '') }}" required />
                </div>
                <div class="form-group col-md-2">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Location', 'helpKey' => 'Location'])
                    <input type="text" name="Location" class="form-control" value="{{ old('Location', $policy->Location ?? '') }}" />
                </div>
                <div class="form-group col-md-2">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Company ID', 'helpKey' => 'Company_ID'])
                    <input type="text" name="Company_ID" class="form-control" value="{{ old('Company_ID', $policy->Company_ID ?? '') }}" />
                </div>
                <div class="form-group col-md-2">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Department', 'helpKey' => 'Scope_Department'])
                    <input type="text" name="Department" class="form-control" value="{{ old('Department', $policy->Department ?? '') }}" />
                </div>
                <div class="form-group col-md-1">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Min Amount', 'helpKey' => 'Amount'])
                    <input type="number" step="0.01" name="Min_Amount" class="form-control" value="{{ old('Min_Amount', $policy->Min_Amount ?? '') }}" />
                </div>
                <div class="form-group col-md-1">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Max Amount', 'helpKey' => 'Amount'])
                    <input type="number" step="0.01" name="Max_Amount" class="form-control" value="{{ old('Max_Amount', $policy->Max_Amount ?? '') }}" />
                </div>
            </div>

            @php
                $oldFields = old('Condition_Field');
                if (is_array($oldFields)) {
                    $conditionRowsView = [];
                    foreach ($oldFields as $index => $field) {
                        $conditionRowsView[] = [
                            'field' => $field,
                            'operator' => old('Condition_Operator.' . $index, '='),
                            'value' => old('Condition_Value.' . $index, ''),
                        ];
                    }
                } else {
                    $conditionRowsView = $conditionRows ?? [];
                }
                if (empty($conditionRowsView)) {
                    $conditionRowsView = [['field' => '', 'operator' => '=', 'value' => '']];
                }
            @endphp

            <div class="form-row">
                <div class="form-group col-md-12">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Conditions', 'helpKey' => 'Conditions'])
                    <p class="text-muted mb-2">
                        Tentukan kapan policy ini berlaku. Semua condition harus terpenuhi.
                        Kosongkan jika policy berlaku untuk semua.
                    </p>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-2">
                            <thead>
                                <tr>
                                    <th style="width:28%">Field</th>
                                    <th style="width:28%">Operator</th>
                                    <th>Nilai</th>
                                    <th style="width:80px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="PolicyConditionBody">
                                @foreach($conditionRowsView as $row)
                                <tr>
                                    <td>
                                        <select name="Condition_Field[]" class="form-control">
                                            <option value="">-- Pilih Field --</option>
                                            @foreach($conditionFields as $fieldValue => $fieldLabel)
                                            <option value="{{ $fieldValue }}" {{ (string) ($row['field'] ?? '') === (string) $fieldValue ? 'selected' : '' }}>{{ $fieldLabel }}</option>
                                            @endforeach
                                            @if(!empty($row['field']) && !array_key_exists($row['field'], $conditionFields))
                                            <option value="{{ $row['field'] }}" selected>{{ $row['field'] }} (custom)</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td>
                                        <select name="Condition_Operator[]" class="form-control policy-condition-operator" onchange="PolicyConditionToggleValue(this);">
                                            @foreach($conditionOperators as $operatorValue => $operatorLabel)
                                            <option value="{{ $operatorValue }}" {{ (string) ($row['operator'] ?? '=') === (string) $operatorValue ? 'selected' : '' }}>{{ $operatorLabel }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="Condition_Value[]" class="form-control policy-condition-value" value="{{ $row['value'] ?? '' }}" placeholder="Isi nilai" />
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="PolicyConditionRemove(this);">Hapus</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-sm btn-success" onclick="PolicyConditionAdd();">Tambah Condition</button>
                    <small class="form-text text-muted">
                        Contoh: pilih <strong>Level Posisi</strong>, operator <strong>Tidak termasuk</strong>, nilai <code>1,2,3</code>.
                        Untuk operator <strong>Termasuk salah satu</strong> / <strong>Tidak termasuk</strong>, pisahkan nilai dengan koma.<br />
                        <strong>Level Posisi</strong> memakai level jabatan yang diminta; <strong>Departemen Posisi</strong> memakai departemen jabatan target.
                    </small>
                </div>
            </div>

            <template id="PolicyConditionTemplate">
                <tr>
                    <td>
                        <select name="Condition_Field[]" class="form-control">
                            <option value="">-- Pilih Field --</option>
                            @foreach($conditionFields as $fieldValue => $fieldLabel)
                            <option value="{{ $fieldValue }}">{{ $fieldLabel }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="Condition_Operator[]" class="form-control policy-condition-operator" onchange="PolicyConditionToggleValue(this);">
                            @foreach($conditionOperators as $operatorValue => $operatorLabel)
                            <option value="{{ $operatorValue }}">{{ $operatorLabel }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text" name="Condition_Value[]" class="form-control policy-condition-value" value="" placeholder="Isi nilai" />
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger" onclick="PolicyConditionRemove(this);">Hapus</button>
                    </td>
                </tr>
            </template>

            <div class="form-row">
                <div class="form-group col-md-3">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Rejection Mode', 'helpKey' => 'Rejection_Mode'])
                    <select name="Rejection_Mode" class="form-control">
                        @foreach(['RETURN_TO_CREATOR','STOP','WAIT_GROUP'] as $mode)
                        <option value="{{ $mode }}" {{ old('Rejection_Mode', $policy->Rejection_Mode ?? 'RETURN_TO_CREATOR') === $mode ? 'selected' : '' }}>{{ $mode }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Rejection Quorum', 'helpKey' => 'Rejection_Quorum'])
                    <input type="number" min="0" name="Rejection_Quorum" class="form-control" value="{{ old('Rejection_Quorum', $policy->Rejection_Quorum ?? '') }}" />
                </div>
                <div class="form-group col-md-2">
                    @include('humanresources.approval.partials.help-label', ['label' => 'On Vacancy', 'helpKey' => 'On_Vacancy'])
                    <select name="On_Vacancy" class="form-control">
                        @foreach(['FALLBACK','HOLD','BLOCK'] as $value)
                        <option value="{{ $value }}" {{ old('On_Vacancy', $policy->On_Vacancy ?? 'FALLBACK') === $value ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    @include('humanresources.approval.partials.help-label', ['label' => 'On No Higher Level', 'helpKey' => 'On_No_Higher_Level'])
                    <select name="On_No_Higher_Level" class="form-control">
                        @foreach(['AUTO_APPROVE','HOLD','BLOCK'] as $value)
                        <option value="{{ $value }}" {{ old('On_No_Higher_Level', $policy->On_No_Higher_Level ?? 'AUTO_APPROVE') === $value ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Effective From', 'helpKey' => 'Effective_From'])
                    <input type="date" name="Effective_From" class="form-control" value="{{ old('Effective_From', $policy->Effective_From ?? '') }}" />
                </div>
                <div class="form-group col-md-2">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Effective To', 'helpKey' => 'Effective_To'])
                    <input type="date" name="Effective_To" class="form-control" value="{{ old('Effective_To', $policy->Effective_To ?? '') }}" />
                    <small class="form-text text-muted">Kosongkan Effective To bila policy tidak memiliki batas waktu.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="Allow_Delegation" value="1" class="form-check-input" id="AllowDelegation" {{ old('Allow_Delegation', $policy->Allow_Delegation ?? 1) ? 'checked' : '' }} />
                        @include('humanresources.approval.partials.help-label', ['label' => 'Allow Delegation', 'helpKey' => 'Allow_Delegation', 'for' => 'AllowDelegation', 'class' => 'form-check-label'])
                    </div>
                </div>
                <div class="form-group col-md-9">
                    <small class="text-muted d-block mt-4">Self-approval selalu diblokir. Hanya wildcard admin yang dapat memberikan exception per request melalui Approval Inbox.</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Policy' : 'Create Policy' }}</button>
            @if($isEdit)
            <a href="hrd-approval-policy" class="btn btn-secondary">Back to List</a>
            @endif
        </form>
    </div>
</div>

<div id="PolicyHelpContent" style="display:none;">
    <div class="policy-help-content">
        <p class="text-muted">Panduan singkat mengisi form policy approval. Field bertanda <strong>*</strong> wajib diisi. Klik ikon <strong>?</strong> di samping field untuk langsung membuka penjelasan field tersebut.</p>

        <h6 class="mt-3">1. Identitas Policy</h6>
        <table class="table table-sm table-bordered policy-help-table">
            <tbody>
                <tr id="PolicyHelp_Policy_Code">
                    <th>Policy Code *</th>
                    <td>Kode unik policy. Gunakan huruf besar dan tanda hubung, contoh <code>PTK-STAFF-3-HCGA</code>. Tidak boleh sama dengan policy lain.</td>
                </tr>
                <tr id="PolicyHelp_Policy_Name">
                    <th>Policy Name *</th>
                    <td>Nama yang mudah dipahami, misalnya <em>Approval PTK Staff (Manager Dept + Manager HCGA)</em>.</td>
                </tr>
                <tr id="PolicyHelp_Version">
                    <th>Version</th>
                    <td>Versi policy. Naikkan angkanya jika ada perubahan aturan, misalnya dari 1 menjadi 2.</td>
                </tr>
                <tr id="PolicyHelp_Status">
                    <th>Status</th>
                    <td><strong>DRAFT</strong> = belum dipakai. <strong>ACTIVE</strong> = dipakai engine. <strong>INACTIVE</strong> = tidak dipakai. Policy baru harus DRAFT dulu, lalu tambah step, baru ACTIVE.</td>
                </tr>
            </tbody>
        </table>

        <h6 class="mt-3">2. Cakupan Policy (Scope)</h6>
        <table class="table table-sm table-bordered policy-help-table">
            <tbody>
                <tr id="PolicyHelp_Request_Type">
                    <th>Request Type *</th>
                    <td>Jenis pengajuan yang memakai policy ini. Untuk PTK isi <code>REQUISITION</code>.</td>
                </tr>
                <tr id="PolicyHelp_Location">
                    <th>Location</th>
                    <td>Batasi policy untuk lokasi tertentu. Kosongkan = semua lokasi. Contoh <code>GEU-HO</code>.</td>
                </tr>
                <tr id="PolicyHelp_Company_ID">
                    <th>Company ID</th>
                    <td>Batasi untuk company tertentu. Kosongkan = semua company. Contoh <code>GEU</code>.</td>
                </tr>
                <tr id="PolicyHelp_Scope_Department">
                    <th>Department</th>
                    <td>Batasi untuk departemen posisi target. Kosongkan = semua departemen. Contoh <code>PROC</code>.</td>
                </tr>
                <tr id="PolicyHelp_Amount">
                    <th>Min Amount / Max Amount</th>
                    <td>Batas nominal pengajuan. Kosongkan jika tidak memakai nominal. Untuk PTK biasanya dikosongkan karena Amount = 0.</td>
                </tr>
            </tbody>
        </table>

        <h6 class="mt-3" id="PolicyHelp_Conditions">3. Conditions</h6>
        <p>Condition menentukan kapan policy ini dipakai. Semua baris condition harus terpenuhi (DAN). Kosongkan jika policy berlaku untuk semua.</p>
        <table class="table table-sm table-bordered policy-help-table">
            <tbody>
                <tr>
                    <th>Field</th>
                    <td>Data yang dicek. Contoh <strong>Level Posisi</strong> untuk level jabatan yang diminta, <strong>Departemen Posisi</strong> untuk departemen jabatan target.</td>
                </tr>
                <tr>
                    <th>Operator</th>
                    <td>Perbandingan. Contoh <strong>Tidak termasuk</strong> untuk mengecualikan nilai; <strong>Termasuk salah satu</strong> untuk memilih beberapa nilai.</td>
                </tr>
                <tr>
                    <th>Nilai</th>
                    <td>Pembanding. Untuk <strong>Termasuk salah satu</strong> / <strong>Tidak termasuk</strong>, pisahkan dengan koma: <code>1,2,3</code>. Untuk <strong>Terisi / tidak kosong</strong>, nilai tidak perlu diisi.</td>
                </tr>
            </tbody>
        </table>
        <div class="alert alert-info py-2 mb-0">
            Contoh staff: <strong>Level Posisi</strong> - <strong>Tidak termasuk</strong> - <code>1,2,3</code>.<br />
            Contoh manager: <strong>Level Posisi</strong> - <strong>Termasuk salah satu</strong> - <code>1,2,3</code>.
        </div>

        <h6 class="mt-3">4. Perilaku Approval</h6>
        <table class="table table-sm table-bordered policy-help-table">
            <tbody>
                <tr id="PolicyHelp_Rejection_Mode">
                    <th>Rejection Mode</th>
                    <td><strong>RETURN_TO_CREATOR</strong> = dikembalikan ke pembuat untuk diperbaiki. <strong>STOP</strong> = proses berhenti. <strong>WAIT_GROUP</strong> = menunggu quorum penolakan.</td>
                </tr>
                <tr id="PolicyHelp_Rejection_Quorum">
                    <th>Rejection Quorum</th>
                    <td>Jumlah minimal penolakan untuk mode WAIT_GROUP. Kosongkan untuk mode lain.</td>
                </tr>
                <tr id="PolicyHelp_On_Vacancy">
                    <th>On Vacancy</th>
                    <td>Jika posisi approver kosong: <strong>FALLBACK</strong> = cari atasan/level di atasnya, <strong>HOLD</strong> = tahan, <strong>BLOCK</strong> = blokir.</td>
                </tr>
                <tr id="PolicyHelp_On_No_Higher_Level">
                    <th>On No Higher Level</th>
                    <td>Jika tidak ada level atasan: <strong>AUTO_APPROVE</strong> = otomatis setuju, <strong>HOLD</strong> = tahan, <strong>BLOCK</strong> = blokir.</td>
                </tr>
                <tr id="PolicyHelp_Allow_Delegation">
                    <th>Allow Delegation</th>
                    <td>Izinkan approver mengalihkan approval ke delegate yang aktif.</td>
                </tr>
            </tbody>
        </table>
        <p class="text-muted mb-0">Self-approval selalu diblokir. Exception hanya bisa diberikan admin melalui Approval Inbox.</p>

        <h6 class="mt-3">5. Effective Date</h6>
        <table class="table table-sm table-bordered policy-help-table">
            <tbody>
                <tr id="PolicyHelp_Effective_From">
                    <th>Effective From</th>
                    <td>Tanggal policy mulai berlaku. Kosongkan jika ingin selalu berlaku.</td>
                </tr>
                <tr id="PolicyHelp_Effective_To">
                    <th>Effective To</th>
                    <td>Tanggal policy berakhir. Kosongkan jika tidak ada batas waktu. Jika diisi, tidak boleh lebih awal dari Effective From.</td>
                </tr>
            </tbody>
        </table>

        <h6 class="mt-3">6. Step Approval</h6>
        <p>Step ditambahkan setelah policy disimpan. Urutan approval mengikuti Stage.</p>
        <table class="table table-sm table-bordered policy-help-table">
            <tbody>
                <tr id="PolicyHelp_Step_Stage">
                    <th>Stage</th>
                    <td>Urutan approval. Stage 1 dijalankan lebih dulu, lalu stage 2, dan seterusnya.</td>
                </tr>
                <tr id="PolicyHelp_Step_Level">
                    <th>Level</th>
                    <td>Level jabatan approver. Contoh Level 3 = Manager, Level 2 = VP.</td>
                </tr>
                <tr id="PolicyHelp_Step_Position">
                    <th>Position</th>
                    <td>Posisi spesifik approver. Kosongkan = cari berdasarkan Level dan Department.</td>
                </tr>
                <tr id="PolicyHelp_Step_Department">
                    <th>Department</th>
                    <td>Batasi approver pada departemen tertentu. Kosongkan = ikut departemen posisi target.</td>
                </tr>
                <tr id="PolicyHelp_Step_Mode">
                    <th>Mode</th>
                    <td><strong>ALL</strong> = semua approver di stage harus setuju. <strong>ANY</strong> = cukup satu. <strong>QUORUM</strong> = jumlah tertentu.</td>
                </tr>
                <tr id="PolicyHelp_Step_Quorum">
                    <th>Quorum</th>
                    <td>Jumlah minimal approval untuk mode QUORUM.</td>
                </tr>
                <tr id="PolicyHelp_Step_Required">
                    <th>Required</th>
                    <td>Ya = step wajib dilalui. Tidak = step opsional.</td>
                </tr>
                <tr id="PolicyHelp_Step_Label">
                    <th>Label</th>
                    <td>Nama stage yang muncul di inbox, misalnya <em>Manager departemen pemohon</em>.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@if($isEdit)
<div class="card mt-3">
    <div class="card-header"><strong>Policy Steps</strong></div>
    <div class="card-body">
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Stage</th>
                    <th>Level</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Mode</th>
                    <th>Quorum</th>
                    <th>Required</th>
                    <th>Label</th>
                    <th style="width:140px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($steps as $step)
                <tr>
                    <td>{{ $step->Stage_Order }}</td>
                    <td>{{ $step->Level_Number }}</td>
                    <td>{{ $step->Position_Code ?: 'By Level' }}</td>
                    <td>{{ $step->Department ?: '-' }}</td>
                    <td>{{ $step->Approval_Mode }}</td>
                    <td>{{ $step->Quorum ?: '-' }}</td>
                    <td>{{ $step->Is_Required ? 'Yes' : 'No' }}</td>
                    <td>{{ $step->Label ?: '-' }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary"
                            data-id="{{ $step->ID }}"
                            data-stage="{{ $step->Stage_Order }}"
                            data-level="{{ $step->Level_Number }}"
                            data-position="{{ $step->Position_ID }}"
                            data-department="{{ $step->Department }}"
                            data-mode="{{ $step->Approval_Mode }}"
                            data-quorum="{{ $step->Quorum }}"
                            data-required="{{ $step->Is_Required }}"
                            data-label="{{ $step->Label }}"
                            onclick="ApprovalStepEdit(this);">Edit</button>
                        <form method="POST" action="{{ url('hrd-approval-policy-step-delete') }}" style="display:inline;" onsubmit="return confirm('Hapus step ini?');">
                            @csrf
                            <input type="hidden" name="Step_ID" value="{{ $step->ID }}" />
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center">Belum ada step.</td></tr>
                @endforelse
            </tbody>
        </table>

        <hr>
        <h5 id="ApprovalStepFormTitle">Add Step</h5>
        <form method="POST" action="{{ url('hrd-approval-policy-step-save') }}" id="ApprovalStepForm">
            @csrf
            <input type="hidden" name="Policy_ID" value="{{ $policy->ID }}" />
            <input type="hidden" name="Step_ID" id="ApprovalStepId" value="" />
            <div class="form-row">
                <div class="form-group col-md-1">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Stage *', 'helpKey' => 'Step_Stage'])
                    <input type="number" name="Stage_Order" id="ApprovalStepStage" class="form-control" min="1" value="1" required />
                </div>
                <div class="form-group col-md-1">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Level *', 'helpKey' => 'Step_Level'])
                    <input type="number" name="Level_Number" id="ApprovalStepLevel" class="form-control" min="1" value="3" required />
                </div>
                <div class="form-group col-md-4">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Position (optional)', 'helpKey' => 'Step_Position'])
                    <select name="Position_ID" id="ApprovalStepPosition" class="form-control">
                        <option value="">-- By Level --</option>
                        @foreach($positions as $position)
                        <option value="{{ $position->ID }}">{{ $position->Initial }} - {{ $position->Position }} (Level {{ $position->Level }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Department', 'helpKey' => 'Step_Department'])
                    <input type="text" name="Department" id="ApprovalStepDepartment" class="form-control" />
                </div>
                <div class="form-group col-md-2">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Mode', 'helpKey' => 'Step_Mode'])
                    <select name="Approval_Mode" id="ApprovalStepMode" class="form-control">
                        <option value="ALL">ALL</option>
                        <option value="ANY">ANY</option>
                        <option value="QUORUM">QUORUM</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Quorum', 'helpKey' => 'Step_Quorum'])
                    <input type="number" name="Quorum" id="ApprovalStepQuorum" class="form-control" min="1" />
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Label', 'helpKey' => 'Step_Label'])
                    <input type="text" name="Label" id="ApprovalStepLabel" class="form-control" />
                </div>
                <div class="form-group col-md-2">
                    @include('humanresources.approval.partials.help-label', ['label' => 'Required', 'helpKey' => 'Step_Required'])
                    <select name="Is_Required" id="ApprovalStepRequired" class="form-control">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-success">Save Step</button>
            <button type="button" class="btn btn-secondary" onclick="ApprovalStepReset();">Reset</button>
        </form>
    </div>
</div>
@endif

<script>
function ApprovalStepEdit(button) {
    document.getElementById('ApprovalStepFormTitle').innerText = 'Edit Step #' + button.dataset.id;
    document.getElementById('ApprovalStepId').value = button.dataset.id;
    document.getElementById('ApprovalStepStage').value = button.dataset.stage;
    document.getElementById('ApprovalStepLevel').value = button.dataset.level;
    document.getElementById('ApprovalStepPosition').value = button.dataset.position || '';
    document.getElementById('ApprovalStepDepartment').value = button.dataset.department || '';
    document.getElementById('ApprovalStepMode').value = button.dataset.mode || 'ALL';
    document.getElementById('ApprovalStepQuorum').value = button.dataset.quorum || '';
    document.getElementById('ApprovalStepLabel').value = button.dataset.label || '';
    document.getElementById('ApprovalStepRequired').value = button.dataset.required || '1';
}

function ApprovalStepReset() {
    document.getElementById('ApprovalStepFormTitle').innerText = 'Add Step';
    document.getElementById('ApprovalStepForm').reset();
    document.getElementById('ApprovalStepId').value = '';
}

function PolicyConditionAdd() {
    var template = document.getElementById('PolicyConditionTemplate');
    var body = document.getElementById('PolicyConditionBody');
    if (!template || !body) return;

    var clone = template.content
        ? document.importNode(template.content, true).firstElementChild
        : template.firstElementChild.cloneNode(true);
    body.appendChild(clone);
}

function PolicyConditionRemove(button) {
    var row = $(button).closest('tr');
    if (row.length) {
        row.remove();
    }
}

function PolicyConditionToggleValue(select) {
    var input = $(select).closest('tr').find('.policy-condition-value');
    if (!input.length) return;

    if (select.value === 'exists') {
        input.val('');
        input.prop('readonly', true);
        input.attr('placeholder', 'Tidak perlu diisi');
        return;
    }

    input.prop('readonly', false);

    if (select.value === 'in' || select.value === 'not_in') {
        input.attr('placeholder', 'Contoh: 1,2,3');
    } else if (['>', '>=', '<', '<='].indexOf(select.value) !== -1) {
        input.attr('placeholder', 'Contoh: 8');
    } else {
        input.attr('placeholder', 'Isi nilai');
    }
}

function PolicyHelpClick(event, section) {
    event = event || window.event;
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    PolicyHelp(section);
    return false;
}

function PolicyHelp(section) {
    if (typeof AppModal === 'undefined') {
        alert('Panduan pengisian belum siap. Silakan muat ulang halaman.');
        return;
    }

    AppModal.open({
        title: 'Panduan Pengisian Policy',
        body: $('#PolicyHelpContent').html(),
        size: 'lg',
        actions: [
            { label: 'Tutup', class: 'btn-secondary' }
        ]
    });

    if (!section) return;

    setTimeout(function () {
        var modalBody = document.getElementById('appModalBody');
        var target = modalBody ? modalBody.querySelector('#PolicyHelp_' + section) : null;
        if (!target) {
            target = document.getElementById('PolicyHelp_' + section);
        }
        if (!target) return;

        var container = document.querySelector('#appModalBody .policy-help-content');
        if (container) {
            var targetRect = target.getBoundingClientRect();
            var containerRect = container.getBoundingClientRect();
            container.scrollTop += targetRect.top - containerRect.top - 8;
        } else if (modalBody) {
            modalBody.scrollTop = target.offsetTop - 8;
        }

        $(target).addClass('policy-help-highlight');
        setTimeout(function () {
            $(target).removeClass('policy-help-highlight');
        }, 1800);
    }, 250);
}

setTimeout(function () {
    $('#PolicyConditionBody .policy-condition-operator').each(function () {
        PolicyConditionToggleValue(this);
    });
}, 0);
</script>