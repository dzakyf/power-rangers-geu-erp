<form id="formEmployeeUserAccount">
    @csrf

    @if(empty($items))
        <div class="alert alert-warning mb-0">
            Tidak ada data karyawan yang ditemukan untuk diproses.
        </div>
    @elseif($isSingle)
        @php
            $row = $items[0];
            $emp = $row['employee'];
            $status = $row['status_code'];
            $activeUser = $row['active_user'];
            $candMatch = $row['candidate_match'];
            $candUser = $candMatch ? $candMatch['user'] : null;
            $candUsername = $candUser ? $candUser->username : '';
            $companyEmail = $row['company_email'];
            $empNik = $row['emp_nik'];
            $defaultMode = $candUser ? 'keep' : ($companyEmail !== '' ? 'company_email' : 'nik');
            $defaultUsername = $candUser ? $candUsername : ($companyEmail !== '' ? $companyEmail : $empNik);
        @endphp

        <input type="hidden" name="items[0][employment_id]" value="{{ $emp->ID }}">
        <input type="hidden" name="items[0][employee_key]" value="{{ $row['employee_key'] }}">
        <input type="hidden" name="items[0][selected]" value="1">
        <input type="hidden" name="items[0][candidate_user_id]" id="singleCandidateUserId" value="{{ $candUser ? $candUser->id : '' }}">
        <input type="hidden" name="items[0][is_manual_candidate]" id="singleIsManualCandidate" value="0">
        <input type="hidden" name="items[0][ignore_candidate_match]" id="singleIgnoreCandidate" value="0">

        <div class="card border bg-light mb-3">
            <div class="card-body py-2 px-3">
                <div class="row g-2 small">
                    <div class="col-md-4">
                        <div class="text-muted">Nama Karyawan</div>
                        <div class="fw-bold text-dark">{{ $emp->Name }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted">NIK / Task ID</div>
                        <div class="fw-semibold">{{ $empNik ?: '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted">Posisi / Dept</div>
                        <div class="fw-semibold">{{ $emp->Position ?: '-' }} ({{ $emp->Department ?: '-' }})</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted">Email Perusahaan</div>
                        <div class="fw-semibold">{{ $companyEmail !== '' ? $companyEmail : ($row['personal_email'] ?: '-') }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if(in_array($status, ['HAS_EMPLOYEE_ACCOUNT', 'INACTIVE_EMPLOYEE_ACCOUNT'], true) && $activeUser)
            @php $isInactiveAcc = ($status === 'INACTIVE_EMPLOYEE_ACCOUNT'); @endphp
            <div class="alert {{ $isInactiveAcc ? 'alert-warning' : 'alert-success' }} py-2 px-3 mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <i class="fa {{ $isInactiveAcc ? 'fa-exclamation-triangle' : 'fa-check-circle' }} me-1"></i>
                        <strong>{{ $isInactiveAcc ? 'Akun Karyawan Nonaktif (INACTIVE):' : 'Sudah Memiliki Akun Karyawan Aktif:' }}</strong>
                        <code>{{ $activeUser->username }}</code> (Emp_ID: <strong>{{ $activeUser->Emp_ID }}</strong>, Priv_ID: <span class="badge {{ $isInactiveAcc ? 'bg-warning text-dark' : 'bg-success text-white' }}">{{ $activeUser->Priv_ID ?: '-' }}</span>)
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="singleSyncExisting" name="items[0][sync_existing]" value="1">
                        <label class="form-check-label small fw-semibold" for="singleSyncExisting">
                            {{ $isInactiveAcc ? 'Aktifkan Kembali & Sinkronkan Role' : 'Sinkronkan Role & Emp_ID' }}
                        </label>
                    </div>
                </div>
            </div>
        @else
            <div id="singleCandidateBanner" class="alert {{ $candUser ? 'alert-info' : 'alert-secondary' }} py-2 px-3 mb-3">
                @if($candUser)
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <i class="fa fa-user-check me-1"></i>
                            <strong>Terdeteksi Akun Kandidat</strong> (via <code>{{ $candMatch['matched_by'] }}</code>):
                            <span class="fw-bold">{{ $candUser->Name }}</span> — Username: <code>{{ $candUser->username }}</code> ({{ $candUser->email ?: '-' }}).
                            <div class="small mt-1 text-dark">
                                Akun kandidat akan <strong>dikonversi/di-upgrade</strong> menjadi akun karyawan. Password kandidat tetap dipertahankan.
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSingleCandidate()">
                            Lepas Tautan
                        </button>
                    </div>
                @else
                    <div>
                        <i class="fa fa-info-circle me-1"></i>
                        <strong>Belum ada akun user terdeteksi.</strong> Sistem akan membuat akun karyawan baru, atau Anda dapat menautkan akun kandidat secara manual di bawah ini.
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Hubungkan Akun Kandidat Secara Manual (Opsional)</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                    <input type="text" id="singleCandSearchInput" class="form-control" placeholder="Ketik nama, Task_ID, username, email, atau KTP kandidat...">
                    <button type="button" class="btn btn-outline-primary" onclick="searchSingleCandidates()">Cari Kandidat</button>
                </div>
                <div id="singleCandSearchResults" class="list-group mt-1 shadow-sm" style="display:none; max-height: 170px; overflow-y: auto;"></div>
            </div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Format Username *</label>
                <select name="items[0][username_mode]" id="singleUsernameMode" class="form-select form-select-sm"
                    data-cand-username="{{ $candUsername }}"
                    data-company-email="{{ $companyEmail }}"
                    data-nik="{{ $empNik }}">
                    @if($candUser)
                        <option value="keep" selected>Pertahankan Username Kandidat ({{ $candUsername }})</option>
                    @endif
                    <option value="company_email" {{ !$candUser && $companyEmail !== '' ? 'selected' : '' }}>
                        Email Perusahaan ({{ $companyEmail !== '' ? $companyEmail : 'Kosong - fallback NIK' }})
                    </option>
                    <option value="nik" {{ !$candUser && $companyEmail === '' ? 'selected' : '' }}>
                        NIK Karyawan ({{ $empNik ?: '-' }})
                    </option>
                    <option value="custom">Custom Username</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Username Login *</label>
                <input type="text" name="items[0][username]" id="singleUsernameInput" class="form-control form-control-sm"
                    value="{{ $defaultUsername }}" required {{ $activeUser ? 'readonly' : '' }}>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Spatie Role (Hak Akses Menu) *</label>
                <select name="items[0][role_id]" id="singleRoleSelect" class="form-select form-select-sm">
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}"
                            data-role-name="{{ $role->name }}"
                            {{ ($row['suggested_role_id'] === (int) $role->id) ? 'selected' : '' }}>
                            {{ $role->department ? ($role->name . ' - ' . $role->department) : $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Priv_ID (Legacy Level/Privilege) *</label>
                <select name="items[0][priv_id]" id="singlePrivSelect" class="form-select form-select-sm">
                    @foreach($privOptions as $pOpt)
                        <option value="{{ $pOpt }}" {{ strtoupper($row['suggested_priv_id']) === strtoupper($pOpt) ? 'selected' : '' }}>
                            {{ $pOpt }}
                        </option>
                    @endforeach
                    @if(!$privOptions->contains($row['suggested_priv_id']))
                        <option value="{{ $row['suggested_priv_id'] }}" selected>{{ $row['suggested_priv_id'] }}</option>
                    @endif
                </select>
                <div class="form-text small">Auto-mapping: <code>{{ $row['matched_by'] }}</code></div>
            </div>

            @if(!$activeUser)
                <div class="col-md-12" id="singlePasswordSection">
                    <div id="singleResetPasswordCheckWrap" class="form-check mb-2 {{ $candUser ? '' : 'd-none' }}">
                        <input class="form-check-input" type="checkbox" name="items[0][reset_password]" id="singleResetPasswordCheck" value="1">
                        <label class="form-check-label small fw-semibold" for="singleResetPasswordCheck">
                            Reset password akun kandidat (biarkan tidak dicentang untuk mempertahankan password lama kandidat)
                        </label>
                    </div>
                    <div id="singlePasswordInputWrap" class="{{ $candUser ? 'd-none' : '' }}">
                        <label class="form-label small fw-semibold">Password Awal</label>
                        <input type="text" name="items[0][password]" id="singlePasswordInput" class="form-control form-control-sm"
                            value="12345678" minlength="6" placeholder="Default: 12345678">
                        <div class="form-text small">Minimal 6 karakter. Default: <code>12345678</code>.</div>
                    </div>
                </div>
            @endif
        </div>
    @else
        {{-- BATCH MODE --}}
        <div class="card border bg-light mb-3">
            <div class="card-body py-2 px-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold mb-1">Mode Username Akun Kandidat</label>
                        <div class="input-group input-group-sm">
                            <select id="batchGlobalCandUsernameMode" class="form-select">
                                <option value="keep">Pertahankan Username Lama</option>
                                <option value="company_email">Email Perusahaan (fallback NIK)</option>
                                <option value="nik">NIK Karyawan</option>
                            </select>
                            <button type="button" class="btn btn-outline-primary" onclick="applyBatchCandUsernameMode()">Terapkan</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold mb-1">Mode Username Akun Baru</label>
                        <div class="input-group input-group-sm">
                            <select id="batchGlobalNewUsernameMode" class="form-select">
                                <option value="company_email">Email Perusahaan (fallback NIK)</option>
                                <option value="nik">NIK Karyawan</option>
                            </select>
                            <button type="button" class="btn btn-outline-primary" onclick="applyBatchNewUsernameMode()">Terapkan</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold mb-1">Override Role Massal (Opsional)</label>
                        <div class="input-group input-group-sm">
                            <select id="batchGlobalRoleId" class="form-select">
                                <option value="">-- Ikuti Auto-Mapping --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" data-role-name="{{ $role->name }}">
                                        {{ $role->department ? ($role->name . ' - ' . $role->department) : $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary" onclick="applyBatchRole()">Terapkan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light sticky-top">
                    <tr class="small">
                        <th style="width: 34px;" class="text-center">
                            <input type="checkbox" class="form-check-input" id="batchCheckAll" checked>
                        </th>
                        <th>Karyawan</th>
                        <th>Status / Tautan Kandidat</th>
                        <th style="width: 220px;">Username Target</th>
                        <th style="width: 210px;">Role & Priv_ID</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $idx => $row)
                        @php
                            $emp = $row['employee'];
                            $status = $row['status_code'];
                            $activeUser = $row['active_user'];
                            $candMatch = $row['candidate_match'];
                            $candUser = $candMatch ? $candMatch['user'] : null;
                            $companyEmail = $row['company_email'];
                            $empNik = $row['emp_nik'];
                            $defaultMode = $candUser ? 'keep' : ($companyEmail !== '' ? 'company_email' : 'nik');
                            $isChecked = !in_array($status, ['HAS_EMPLOYEE_ACCOUNT', 'INACTIVE_EMPLOYEE_ACCOUNT'], true);
                        @endphp
                        <tr class="batch-emp-row" data-idx="{{ $idx }}"
                            data-is-cand="{{ $candUser ? '1' : '0' }}"
                            data-has-active="{{ $activeUser ? '1' : '0' }}"
                            data-cand-username="{{ $candUser ? $candUser->username : '' }}"
                            data-company-email="{{ $companyEmail }}"
                            data-nik="{{ $empNik }}">
                            <td class="text-center">
                                <input type="hidden" name="items[{{ $idx }}][employment_id]" value="{{ $emp->ID }}">
                                <input type="hidden" name="items[{{ $idx }}][employee_key]" value="{{ $row['employee_key'] }}">
                                <input type="hidden" name="items[{{ $idx }}][candidate_user_id]" class="batch-cand-id" value="{{ $candUser ? $candUser->id : '' }}">
                                <input type="hidden" name="items[{{ $idx }}][is_manual_candidate]" class="batch-is-manual" value="0">
                                <input type="hidden" name="items[{{ $idx }}][ignore_candidate_match]" class="batch-ignore-cand" value="0">
                                <input type="checkbox" class="form-check-input batch-row-check" name="items[{{ $idx }}][selected]" value="1" {{ $isChecked ? 'checked' : '' }}>
                            </td>
                            <td class="small">
                                <div class="fw-bold text-dark">{{ $emp->Name }}</div>
                                <div class="text-muted">NIK: {{ $empNik ?: '-' }} | {{ $emp->Position ?: '-' }}</div>
                                <div class="text-muted">{{ $companyEmail !== '' ? $companyEmail : ($row['personal_email'] ?: 'Email kosong') }}</div>
                            </td>
                            <td class="small">
                                @if($activeUser)
                                    @if($status === 'INACTIVE_EMPLOYEE_ACCOUNT')
                                        <span class="badge bg-warning text-dark">Nonaktif: {{ $activeUser->username }}</span>
                                    @else
                                        <span class="badge bg-success text-white">Aktif: {{ $activeUser->username }}</span>
                                    @endif
                                    <div class="form-check mt-1 mb-0">
                                        <input class="form-check-input" type="checkbox" name="items[{{ $idx }}][sync_existing]" value="1" id="sync_{{ $idx }}">
                                        <label class="form-check-label small" for="sync_{{ $idx }}">{{ $status === 'INACTIVE_EMPLOYEE_ACCOUNT' ? 'Aktifkan & Sync' : 'Sync Role' }}</label>
                                    </div>
                                @else
                                    <div class="batch-cand-status">
                                        @if($candUser)
                                            <span class="badge bg-info text-white">Kandidat ({{ $candMatch['matched_by'] }})</span>
                                            <div class="mt-1"><code>{{ $candUser->username }}</code></div>
                                        @else
                                            <span class="badge bg-secondary text-white">Buat Akun Baru</span>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-link btn-sm p-0 mt-1 text-decoration-none" onclick="openBatchCandPicker({{ $idx }})">
                                        <i class="fa fa-link me-1"></i> Pilih/Ganti Kandidat Manual
                                    </button>
                                @endif
                            </td>
                            <td>
                                @if($activeUser)
                                    <code class="small">{{ $activeUser->username }}</code>
                                @else
                                    <select name="items[{{ $idx }}][username_mode]" class="form-select form-select-sm batch-username-mode">
                                        @if($candUser)
                                            <option value="keep" selected>Lama ({{ $candUser->username }})</option>
                                        @endif
                                        <option value="company_email" {{ !$candUser && $companyEmail !== '' ? 'selected' : '' }}>
                                            Email ({{ $companyEmail !== '' ? $companyEmail : 'Fallback NIK' }})
                                        </option>
                                        <option value="nik" {{ !$candUser && $companyEmail === '' ? 'selected' : '' }}>
                                            NIK ({{ $empNik ?: '-' }})
                                        </option>
                                    </select>
                                @endif
                            </td>
                            <td>
                                <select name="items[{{ $idx }}][role_id]" class="form-select form-select-sm batch-role-select mb-1">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" data-role-name="{{ $role->name }}"
                                            {{ ($row['suggested_role_id'] === (int) $role->id) ? 'selected' : '' }}>
                                            {{ $role->department ? ($role->name . ' - ' . $role->department) : $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="items[{{ $idx }}][priv_id]" class="form-control form-control-sm batch-priv-input"
                                    value="{{ $row['suggested_priv_id'] }}" placeholder="Priv_ID">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Inline Candidate Picker for Batch Row --}}
        <div id="batchCandPickerBox" class="card border-primary mt-2" style="display:none;">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-primary">Pilih Akun Kandidat Manual untuk Baris #<span id="batchPickerRowLabel"></span></span>
                    <button type="button" class="btn-close btn-sm" onclick="$('#batchCandPickerBox').hide();"></button>
                </div>
                <div class="input-group input-group-sm mb-2">
                    <input type="text" id="batchCandSearchInput" class="form-control" placeholder="Cari nama / Task_ID / email / KTP kandidat...">
                    <button type="button" class="btn btn-primary" onclick="searchBatchCandidates()">Cari</button>
                </div>
                <div id="batchCandSearchResults" class="list-group" style="max-height: 150px; overflow-y: auto;"></div>
            </div>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
        <span class="small text-muted">
            Default password akun baru: <code>12345678</code>. Password kandidat eksisting dipertahankan.
        </span>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
            <button type="submit" id="btnSubmitEmpUserAccount" class="btn btn-success btn-sm text-white px-3" {{ empty($items) ? 'disabled' : '' }}>
                <i class="fa fa-check me-1"></i> Proses Akun User
            </button>
        </div>
    </div>
</form>

<script>
    var empCandSearchUrl = '{{ url("hrd-employee-user-account-candidates") }}';
    var activeBatchPickerIdx = null;

    function updateSingleUsernamePreview() {
        var $mode = $('#singleUsernameMode');
        if (!$mode.length) return;
        var mode = $mode.val();
        var candU = $mode.attr('data-cand-username') || '';
        var compE = $mode.attr('data-company-email') || '';
        var nik = $mode.attr('data-nik') || '';
        if (mode === 'keep') {
            $('#singleUsernameInput').val(candU);
        } else if (mode === 'company_email') {
            $('#singleUsernameInput').val(compE !== '' ? compE : nik);
        } else if (mode === 'nik') {
            $('#singleUsernameInput').val(nik);
        }
    }

    $('#singleUsernameMode').on('change', updateSingleUsernamePreview);
    $('#singleUsernameInput').on('input', function() {
        $('#singleUsernameMode').val('custom');
    });

    $('#singleRoleSelect').on('change', function() {
        var rName = $(this).find('option:selected').data('role-name');
        if (rName && $('#singlePrivSelect option[value="' + rName + '"]').length) {
            $('#singlePrivSelect').val(rName);
        }
    });

    $('#singleResetPasswordCheck').on('change', function() {
        if ($(this).is(':checked')) {
            $('#singlePasswordInputWrap').removeClass('d-none');
        } else {
            $('#singlePasswordInputWrap').addClass('d-none');
        }
    });

    function clearSingleCandidate() {
        $('#singleCandidateUserId').val('');
        $('#singleIsManualCandidate').val('0');
        $('#singleIgnoreCandidate').val('1');
        $('#singleUsernameMode option[value="keep"]').remove();
        var compE = $('#singleUsernameMode').attr('data-company-email') || '';
        $('#singleUsernameMode').val(compE !== '' ? 'company_email' : 'nik').trigger('change');
        $('#singleResetPasswordCheckWrap').addClass('d-none');
        $('#singleResetPasswordCheck').prop('checked', false);
        $('#singlePasswordInputWrap').removeClass('d-none');
        $('#singleCandidateBanner')
            .removeClass('alert-info')
            .addClass('alert-secondary')
            .html('<i class="fa fa-info-circle me-1"></i> Tautan kandidat dilepas. Sistem akan membuat akun baru (atau pilih kandidat lain secara manual).');
    }

    function searchSingleCandidates() {
        var q = $('#singleCandSearchInput').val() || '';
        var $box = $('#singleCandSearchResults');
        $box.show().html('<div class="list-group-item small text-muted">Mencari akun kandidat...</div>');
        $.getJSON(empCandSearchUrl, { q: q }, function(res) {
            var rows = (res && res.results) ? res.results : [];
            if (!rows.length) {
                $box.html('<div class="list-group-item small text-muted">Tidak ditemukan akun kandidat aktif.</div>');
                return;
            }
            $box.empty();
            rows.forEach(function(item) {
                var $a = $('<button type="button" class="list-group-item list-group-item-action small py-1"></button>');
                $a.text(item.text);
                $a.on('click', function() {
                    attachSingleCandidate(item);
                    $box.hide();
                });
                $box.append($a);
            });
        }).fail(function() {
            $box.html('<div class="list-group-item small text-danger">Gagal mencari data kandidat.</div>');
        });
    }

    function attachSingleCandidate(item) {
        $('#singleCandidateUserId').val(item.id);
        $('#singleIsManualCandidate').val('1');
        $('#singleIgnoreCandidate').val('0');
        var $mode = $('#singleUsernameMode');
        $mode.attr('data-cand-username', item.username);
        $mode.find('option[value="keep"]').remove();
        $mode.prepend('<option value="keep">Pertahankan Username Kandidat (' + item.username + ')</option>');
        $mode.val('keep').trigger('change');
        $('#singleResetPasswordCheckWrap').removeClass('d-none');
        $('#singleResetPasswordCheck').prop('checked', false);
        $('#singlePasswordInputWrap').addClass('d-none');
        $('#singleCandidateBanner')
            .removeClass('alert-secondary')
            .addClass('alert-info')
            .html('<div class="d-flex justify-content-between align-items-start gap-2"><div><i class="fa fa-user-check me-1"></i> <strong>Ditautkan Manual ke Akun Kandidat:</strong> ' + $('<div>').text(item.text).html() + '<div class="small mt-1">Akun kandidat akan di-upgrade menjadi akun karyawan & password lama dipertahankan.</div></div><button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSingleCandidate()">Lepas Tautan</button></div>');
    }

    $('#batchCheckAll').on('change', function() {
        $('.batch-row-check').prop('checked', $(this).is(':checked'));
    });

    function applyBatchCandUsernameMode() {
        var mode = $('#batchGlobalCandUsernameMode').val();
        $('.batch-emp-row[data-is-cand="1"]').each(function() {
            $(this).find('.batch-username-mode').val(mode);
        });
    }

    function applyBatchNewUsernameMode() {
        var mode = $('#batchGlobalNewUsernameMode').val();
        $('.batch-emp-row[data-is-cand="0"][data-has-active="0"]').each(function() {
            $(this).find('.batch-username-mode').val(mode);
        });
    }

    function applyBatchRole() {
        var roleId = $('#batchGlobalRoleId').val();
        if (!roleId) return;
        var roleName = $('#batchGlobalRoleId option:selected').data('role-name') || '';
        $('.batch-emp-row').each(function() {
            $(this).find('.batch-role-select').val(roleId);
            if (roleName) {
                $(this).find('.batch-priv-input').val(roleName);
            }
        });
    }

    function openBatchCandPicker(idx) {
        activeBatchPickerIdx = idx;
        $('#batchPickerRowLabel').text(idx + 1);
        $('#batchCandPickerBox').show();
        $('#batchCandSearchInput').val('').focus();
        searchBatchCandidates();
    }

    function searchBatchCandidates() {
        var q = $('#batchCandSearchInput').val() || '';
        var $box = $('#batchCandSearchResults');
        $box.html('<div class="list-group-item small text-muted">Memuat daftar kandidat...</div>');
        $.getJSON(empCandSearchUrl, { q: q }, function(res) {
            var rows = (res && res.results) ? res.results : [];
            if (!rows.length) {
                $box.html('<div class="list-group-item small text-muted">Tidak ditemukan akun kandidat aktif.</div>');
                return;
            }
            $box.empty();
            rows.forEach(function(item) {
                var $a = $('<button type="button" class="list-group-item list-group-item-action small py-1"></button>');
                $a.text(item.text);
                $a.on('click', function() {
                    var $tr = $('.batch-emp-row[data-idx="' + activeBatchPickerIdx + '"]');
                    $tr.attr('data-is-cand', '1').attr('data-cand-username', item.username);
                    $tr.find('.batch-cand-id').val(item.id);
                    $tr.find('.batch-is-manual').val('1');
                    $tr.find('.batch-ignore-cand').val('0');
                    $tr.find('.batch-cand-status').html('<span class="badge bg-info text-white">Kandidat (Manual)</span><div class="mt-1"><code>' + $('<div>').text(item.username).html() + '</code></div>');
                    var $sel = $tr.find('.batch-username-mode');
                    $sel.find('option[value="keep"]').remove();
                    $sel.prepend('<option value="keep">Lama (' + item.username + ')</option>');
                    $sel.val('keep');
                    $tr.find('.batch-row-check').prop('checked', true);
                    $('#batchCandPickerBox').hide();
                });
                $box.append($a);
            });
        });
    }

    $('#formEmployeeUserAccount').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitEmpUserAccount');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses...');

        $.ajax({
            url: '{{ url("hrd-employee-user-account-save") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message || 'Proses akun user selesai.');
                if (window.AppModal && typeof window.AppModal.close === 'function') {
                    window.AppModal.close();
                } else {
                    $('.modal').modal('hide');
                }
                if (typeof buildEmployeeGrid === 'function' && typeof EmpActiveCols !== 'undefined') {
                    buildEmployeeGrid(EmpActiveCols);
                }
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memproses akun user.';
                alert(msg);
                $btn.prop('disabled', false).html('<i class="fa fa-check me-1"></i> Proses Akun User');
            }
        });
    });
</script>
