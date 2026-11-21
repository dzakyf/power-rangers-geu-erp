<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Buat Jadwal &amp; Kurikulum Training ({{ $Task_No }})</h5>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#divlistTraining').html('')">Tutup</button>
    </div>
    <div class="card-body">
        <form id="formNewTraining">
            @csrf
            <input type="hidden" name="Task_No" value="{{ $Task_No }}">
            <input type="hidden" name="participants_explicit" id="participants_explicit" value="0">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Referensi TNA (Approved)</label>
                    <select name="tna_id" id="tna_id" class="form-control" onchange="onTnaSelectChanged(this)">
                        <option value="">-- Tanpa TNA / Ad-Hoc --</option>
                        @foreach($tnas as $t)
                            <option value="{{ $t->id }}" {{ ((string) $selectedTnaId === (string) $t->id) ? 'selected' : '' }}>
                                {{ $t->tna_no }} - {{ $t->title }} ({{ $t->department }}) [{{ $t->items_count ?? 0 }} item]
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Module &amp; Program Training</label>
                    <select name="program_id" id="program_id" class="form-control" onchange="applyProgramDefaults(this)">
                        <option value="">-- Pilih Program (Opsional) --</option>
                        @foreach($programs as $prg)
                            <option value="{{ $prg->id }}"
                                data-name="{{ $prg->program_name }}"
                                data-category="{{ $prg->category_id }}"
                                data-type="{{ $prg->training_type }}"
                                data-method="{{ $prg->training_method }}"
                                data-kkm="{{ $prg->default_passing_grade }}"
                                data-obj="{{ $prg->objective }}"
                                data-comp="{{ $prg->competency }}"
                                data-desc="{{ $prg->description }}">
                                {{ $prg->program_code }} - {{ $prg->program_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 mb-3 d-none" id="tnaPreviewPanel">
                    <div class="card border-info mb-0">
                        <div class="card-header bg-info text-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <strong>Integrasi Data TNA:</strong>
                                <span id="tnaPreviewTitle">-</span>
                            </div>
                            <span class="badge bg-light text-dark" id="tnaPreviewSummary">0 item</span>
                        </div>
                        <div class="card-body py-2">
                            <div class="row align-items-end mb-2">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <label class="form-label small fw-bold mb-1">Pilih Grup Program / Topik Rekomendasi TNA</label>
                                    <select id="tnaGroupSelector" class="form-control form-control-sm" onchange="onTnaGroupChanged(this)"></select>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectTnaItemsMode('unscheduled')">Pilih Belum Dijadwalkan</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectTnaItemsMode('all')">Pilih Semua</button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="selectTnaItemsMode('none')">Kosongkan</button>
                                </div>
                            </div>
                            <div id="tnaDedupNotice" class="alert alert-info py-1 px-2 small mb-2 d-none"></div>
                            <div class="table-responsive" style="max-height: 240px; overflow-y: auto;">
                                <table class="table table-bordered table-sm mb-0" id="tblTnaItemsPreview">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:36px;" class="text-center">Pilih</th>
                                            <th>Karyawan</th>
                                            <th>Gap Kompetensi</th>
                                            <th>Rekomendasi Training</th>
                                            <th>Program</th>
                                            <th>Prioritas / Target</th>
                                            <th>Status Penjadwalan</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Training *</label>
                    <input type="text" name="Training_Name" id="Training_Name" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="Training_Category" id="Training_Category" class="form-control">
                        <option value="">-- Pilih --</option>
                        @foreach($trCategory as $cat)
                            <option value="{{ $cat->ID }}">{{ $cat->Category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Passing Grade (KKM)</label>
                    <input type="number" step="0.01" min="0" max="100" name="passing_grade" id="passing_grade" class="form-control" value="75">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tipe</label>
                    <select name="Training_Type" id="Training_Type" class="form-control">
                        <option value="INTERNAL">INTERNAL</option>
                        <option value="EXTERNAL">EXTERNAL</option>
                        <option value="IN_HOUSE">IN_HOUSE</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Metode</label>
                    <select name="Training_Method" id="Training_Method" class="form-control">
                        <option value="OFFLINE">OFFLINE</option>
                        <option value="ONLINE">ONLINE</option>
                        <option value="HYBRID">HYBRID</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Mulai *</label>
                    <input type="date" name="Start_Date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Selesai *</label>
                    <input type="date" name="End_Date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pemateri / Trainer *
                        <a href="{{ url('hrd-training-trainer-index') }}" target="_blank" class="small ms-1">(Kelola Master)</a>
                    </label>
                    <select name="trainer_id" id="new_trainer_id" class="form-control" required>
                        <option value="">-- Pilih Pemateri dari Master --</option>
                        @foreach($trainers as $tr)
                            <option value="{{ $tr->ID }}">
                                {{ $tr->Trainer_Name }} ({{ $tr->Trainer_Type }} - {{ $tr->Trainer_Company }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lokasi / Link Meeting</label>
                    <input type="text" name="Training_Location" class="form-control" placeholder="Ruang Meeting / Zoom Link">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tujuan Training</label>
                    <input type="text" name="Training_Objective" id="Training_Objective" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kompetensi Target</label>
                    <input type="text" name="Training_Competence" id="Training_Competence" class="form-control">
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Form Kurikulum / Sesi Materi</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addCurriculumRow()">+ Tambah Sesi</button>
            </div>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm" id="tblCurriculums">
                    <thead class="table-light">
                        <tr>
                            <th>Topik / Sesi Materi</th>
                            <th>Pemateri</th>
                            <th style="width:110px;">Durasi (Menit)</th>
                            <th>Metode</th>
                            <th style="width:50px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="curriculums[0][topic_title]" class="form-control form-control-sm" placeholder="Sesi 1: Pengenalan & Teori"></td>
                            <td>
                                <select name="curriculums[0][speaker_name]" class="form-control form-control-sm">
                                    <option value="">-- Ikuti Pemateri Utama --</option>
                                    @foreach($trainers as $tr)
                                        <option value="{{ $tr->Trainer_Name }}">{{ $tr->Trainer_Name }} ({{ $tr->Trainer_Type }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="curriculums[0][duration_minutes]" class="form-control form-control-sm" value="60"></td>
                            <td><input type="text" name="curriculums[0][learning_method]" class="form-control form-control-sm" value="Presentasi & Diskusi"></td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()">x</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Peserta Training <small class="text-muted">(Otomatis terisi saat TNA dipilih, atau tambah manual)</small></h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addParticipantRow()">+ Tambah Peserta</button>
            </div>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm" id="tblParticipants">
                    <thead class="table-light">
                        <tr>
                            <th>Karyawan</th>
                            <th>Departemen</th>
                            <th style="width:50px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <select name="participants[0][employee_username]" class="form-control form-control-sm part-select" onchange="syncPartInfo(this)">
                                        <option value="">-- Pilih Karyawan --</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->username }}" data-name="{{ $emp->name }}" data-dept="{{ $emp->Department }}">
                                                {{ $emp->name }} ({{ $emp->username }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="badge bg-info text-white d-none part-tna-badge">Dari TNA</span>
                                </div>
                                <input type="hidden" name="participants[0][employee_name]" class="part-name">
                                <input type="hidden" name="participants[0][tna_item_id]" class="part-tna-item-id" value="">
                            </td>
                            <td><input type="text" name="participants[0][department]" class="form-control form-control-sm part-dept" readonly></td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeParticipantRow(this)">x</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-success">Simpan Jadwal Training</button>
        </form>
    </div>
</div>

<script>
    var curIdx = 1;
    var partIdx = 1;
    var employeeOptions = @json($employees);
    var currentTnaPayload = null;
    var isManualDirty = false;
    var isApplyingTna = false;
    var previousTnaId = '';
    var previousGroupKey = '';

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function applyProgramDefaults(sel) {
        var opt = $(sel).find('option:selected');
        if (!opt.val()) return;
        $('#Training_Name').val(opt.data('name') || '');
        var catVal = opt.data('category');
        $('#Training_Category').val((catVal !== undefined && catVal !== null && String(catVal) !== '') ? String(catVal) : '');
        $('#Training_Type').val(opt.data('type') || 'INTERNAL');
        $('#Training_Method').val(opt.data('method') || 'OFFLINE');
        $('#passing_grade').val(opt.data('kkm') !== undefined ? opt.data('kkm') : 75);
        $('#Training_Objective').val(opt.data('obj') || '');
        $('#Training_Competence').val(opt.data('comp') || '');
    }

    function addCurriculumRow() {
        var row = $('#tblCurriculums tbody tr:first').clone();
        row.find('input, select').each(function() {
            var n = $(this).attr('name');
            if (n) $(this).attr('name', n.replace(/\[\d+\]/, '[' + curIdx + ']'));
            if ($(this).is('select')) {
                $(this).val('');
            } else if ($(this).attr('type') === 'text') {
                $(this).val('');
            }
        });
        $('#tblCurriculums tbody').append(row);
        curIdx++;
    }

    function buildEmployeeOptionsHtml(selectedUsername) {
        var html = '<option value="">-- Pilih Karyawan --</option>';
        var selectedLower = selectedUsername ? String(selectedUsername).toLowerCase() : '';
        var matched = false;
        for (var i = 0; i < employeeOptions.length; i++) {
            var emp = employeeOptions[i];
            var u = emp.username || '';
            var isSel = (selectedLower !== '' && u.toLowerCase() === selectedLower);
            if (isSel) matched = true;
            html += '<option value="' + escapeHtml(u) + '" data-name="' + escapeHtml(emp.name || u) + '" data-dept="' + escapeHtml(emp.Department || '') + '"' + (isSel ? ' selected' : '') + '>'
                + escapeHtml((emp.name || u) + ' (' + u + ')')
                + '</option>';
        }
        return { html: html, matched: matched };
    }

    function addParticipantRow(preset) {
        var idx = partIdx++;
        var username = (preset && preset.employee_username) ? String(preset.employee_username) : '';
        var empName = (preset && preset.employee_name) ? String(preset.employee_name) : '';
        var dept = (preset && preset.department) ? String(preset.department) : '';
        var tnaItemId = (preset && preset.tna_item_id) ? String(preset.tna_item_id) : '';

        var optResult = buildEmployeeOptionsHtml(username);
        var optionsHtml = optResult.html;
        if (username && !optResult.matched) {
            optionsHtml += '<option value="' + escapeHtml(username) + '" data-name="' + escapeHtml(empName || username) + '" data-dept="' + escapeHtml(dept) + '" selected>'
                + escapeHtml((empName || username) + ' (' + username + ')')
                + '</option>';
        }

        var badgeClass = tnaItemId ? 'badge bg-info text-white part-tna-badge' : 'badge bg-info text-white d-none part-tna-badge';
        var rowHtml = '<tr>'
            + '<td>'
            + '<div class="d-flex align-items-center gap-2">'
            + '<select name="participants[' + idx + '][employee_username]" class="form-control form-control-sm part-select" onchange="syncPartInfo(this)">' + optionsHtml + '</select>'
            + '<span class="' + badgeClass + '">Dari TNA</span>'
            + '</div>'
            + '<input type="hidden" name="participants[' + idx + '][employee_name]" class="part-name" value="' + escapeHtml(empName) + '">'
            + '<input type="hidden" name="participants[' + idx + '][tna_item_id]" class="part-tna-item-id" value="' + escapeHtml(tnaItemId) + '">'
            + '</td>'
            + '<td><input type="text" name="participants[' + idx + '][department]" class="form-control form-control-sm part-dept" value="' + escapeHtml(dept) + '" readonly></td>'
            + '<td><button type="button" class="btn btn-danger btn-sm" onclick="removeParticipantRow(this)">x</button></td>'
            + '</tr>';

        $('#tblParticipants tbody').append(rowHtml);
    }

    function removeParticipantRow(btn) {
        $(btn).closest('tr').remove();
        if (!isApplyingTna) {
            isManualDirty = true;
        }
    }

    function syncPartInfo(sel) {
        var val = $(sel).val();
        var tr = $(sel).closest('tr');
        if (val) {
            var dupFound = false;
            var valLower = String(val).toLowerCase();
            $('#tblParticipants tbody select.part-select').each(function() {
                if (this !== sel && $(this).val() && String($(this).val()).toLowerCase() === valLower) {
                    dupFound = true;
                    return false;
                }
            });
            if (dupFound) {
                alert('Karyawan tersebut sudah ada di daftar peserta.');
                $(sel).val('');
                tr.find('.part-name').val('');
                tr.find('.part-dept').val('');
                tr.find('.part-tna-item-id').val('');
                tr.find('.part-tna-badge').addClass('d-none');
                return;
            }
        }
        var opt = $(sel).find('option:selected');
        tr.find('.part-name').val(opt.data('name') || '');
        tr.find('.part-dept').val(opt.data('dept') || '');
        if (!isApplyingTna) {
            tr.find('.part-tna-item-id').val('');
            tr.find('.part-tna-badge').addClass('d-none');
            isManualDirty = true;
        }
    }

    function onTnaSelectChanged(sel) {
        var newTnaId = $(sel).val();
        if (!newTnaId) {
            currentTnaPayload = null;
            previousTnaId = '';
            previousGroupKey = '';
            $('#tnaPreviewPanel').addClass('d-none');
            $('#tblTnaItemsPreview tbody').html('');
            $('#tnaGroupSelector').html('');
            $('#tnaDedupNotice').addClass('d-none').text('');
            $('#participants_explicit').val('0');
            // Lepas link TNA tanpa menghapus isian teks & daftar peserta yang sudah ada
            $('#tblParticipants tbody tr').each(function() {
                $(this).find('.part-tna-item-id').val('');
                $(this).find('.part-tna-badge').addClass('d-none');
            });
            return;
        }

        if (isManualDirty && !confirm('Form sudah memiliki perubahan manual. Timpa field & daftar peserta dengan data dari TNA terpilih?')) {
            $(sel).val(previousTnaId);
            return;
        }

        loadTnaPayload(newTnaId, true);
    }

    function loadTnaPayload(tnaId, autoApply) {
        if (!tnaId) return;
        $.get('{{ url("hrd-training-tna-payload") }}', { tna_id: tnaId }, function(res) {
            if (!res || res.status !== 'success' || !res.data) {
                alert('Gagal memuat data TNA.');
                return;
            }
            currentTnaPayload = res.data;
            previousTnaId = String(tnaId);

            var tna = currentTnaPayload.tna;
            var groups = currentTnaPayload.groups || [];
            var allGroup = currentTnaPayload.all_group;

            $('#tnaPreviewTitle').text(tna.tna_no + ' — ' + tna.title + ' (' + tna.department + ')');
            $('#tnaPreviewSummary').text(allGroup.total_items + ' item (' + allGroup.unscheduled_count + ' belum dijadwalkan)');

            var groupOptionsHtml = '';
            var defaultGroupKey = 'ALL';

            if (groups.length === 1) {
                defaultGroupKey = groups[0].key;
            } else if (groups.length > 1) {
                defaultGroupKey = '';
                for (var i = 0; i < groups.length; i++) {
                    if (groups[i].unscheduled_count > 0) {
                        defaultGroupKey = groups[i].key;
                        break;
                    }
                }
                if (!defaultGroupKey) {
                    defaultGroupKey = 'ALL';
                }
            }

            for (var g = 0; g < groups.length; g++) {
                var grp = groups[g];
                groupOptionsHtml += '<option value="' + escapeHtml(grp.key) + '">'
                    + escapeHtml(grp.label + ' [' + grp.unscheduled_count + '/' + grp.total_items + ' belum dijadwalkan]')
                    + '</option>';
            }
            groupOptionsHtml += '<option value="ALL">' + escapeHtml(allGroup.label) + '</option>';

            $('#tnaGroupSelector').html(groupOptionsHtml).val(defaultGroupKey);
            previousGroupKey = defaultGroupKey;
            $('#tnaPreviewPanel').removeClass('d-none');

            if (autoApply) {
                applyTnaGroup(defaultGroupKey);
            }
        }).fail(function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memuat detail TNA.');
        });
    }

    function onTnaGroupChanged(sel) {
        var newGroupKey = $(sel).val();
        if (isManualDirty && !confirm('Form sudah memiliki perubahan manual. Timpa field & daftar peserta sesuai grup TNA terpilih?')) {
            $(sel).val(previousGroupKey);
            return;
        }
        previousGroupKey = newGroupKey;
        applyTnaGroup(newGroupKey);
    }

    function findTnaGroup(groupKey) {
        if (!currentTnaPayload) return null;
        if (groupKey === 'ALL') return currentTnaPayload.all_group;
        var groups = currentTnaPayload.groups || [];
        for (var i = 0; i < groups.length; i++) {
            if (groups[i].key === groupKey) return groups[i];
        }
        return currentTnaPayload.all_group;
    }

    function applyTnaGroup(groupKey) {
        if (!currentTnaPayload) return;
        var group = findTnaGroup(groupKey);
        if (!group) return;

        isApplyingTna = true;
        try {
            if (group.program_id) {
                $('#program_id').val(String(group.program_id));
                applyProgramDefaults($('#program_id')[0]);
            } else {
                $('#program_id').val('');
                $('#Training_Category').val('');
                $('#Training_Type').val('INTERNAL');
                $('#Training_Method').val('OFFLINE');
                $('#passing_grade').val('75');
            }

            $('#Training_Name').val(group.suggested_name || '');
            $('#Training_Objective').val(group.suggested_objective || '');
            $('#Training_Competence').val(group.suggested_competence || '');

            renderTnaPreviewRows(groupKey);
            syncParticipantsFromTnaPreview(true);
            isManualDirty = false;
        } finally {
            isApplyingTna = false;
        }
    }

    function renderTnaPreviewRows(groupKey) {
        if (!currentTnaPayload) return;
        var items = currentTnaPayload.items || [];
        var tbodyHtml = '';
        var visibleCount = 0;

        for (var i = 0; i < items.length; i++) {
            var it = items[i];
            if (groupKey !== 'ALL' && it.group_key !== groupKey) {
                continue;
            }
            visibleCount++;
            var checkedAttr = it.is_scheduled ? '' : ' checked';
            var statusHtml = '';
            if (it.is_scheduled && it.scheduled_trainings && it.scheduled_trainings.length > 0) {
                var taskNos = [];
                for (var s = 0; s < it.scheduled_trainings.length; s++) {
                    taskNos.push(it.scheduled_trainings[s].task_no + ' (' + it.scheduled_trainings[s].status + ')');
                }
                statusHtml = '<span class="badge bg-warning text-dark">Sudah Dijadwalkan: ' + escapeHtml(taskNos.join(', ')) + '</span>';
            } else {
                statusHtml = '<span class="badge bg-success text-white">Belum Dijadwalkan</span>';
            }

            tbodyHtml += '<tr>'
                + '<td class="text-center"><input type="checkbox" class="form-check-input tna-item-checkbox" value="' + it.id + '"' + checkedAttr + ' onchange="onTnaItemCheckboxChanged()"></td>'
                + '<td><strong>' + escapeHtml(it.employee_name) + '</strong><br><small class="text-muted">' + escapeHtml(it.employee_username) + (it.department ? ' | ' + escapeHtml(it.department) : '') + '</small></td>'
                + '<td>' + escapeHtml(it.competency_gap || '-') + '</td>'
                + '<td>' + escapeHtml(it.recommended_training || '-') + '</td>'
                + '<td>' + escapeHtml(it.program_name || '-') + '</td>'
                + '<td><span class="badge bg-secondary text-white">' + escapeHtml(it.priority || 'MEDIUM') + '</span> ' + escapeHtml(it.target_quarter || '') + '</td>'
                + '<td>' + statusHtml + '</td>'
                + '</tr>';
        }

        if (visibleCount === 0) {
            tbodyHtml = '<tr><td colspan="7" class="text-center text-muted">Tidak ada item TNA pada grup ini.</td></tr>';
        }

        $('#tblTnaItemsPreview tbody').html(tbodyHtml);
    }

    function selectTnaItemsMode(mode) {
        if (!currentTnaPayload) return;
        var itemMap = {};
        for (var i = 0; i < currentTnaPayload.items.length; i++) {
            itemMap[String(currentTnaPayload.items[i].id)] = currentTnaPayload.items[i];
        }
        $('#tblTnaItemsPreview .tna-item-checkbox').each(function() {
            var it = itemMap[String($(this).val())];
            if (mode === 'all') {
                $(this).prop('checked', true);
            } else if (mode === 'none') {
                $(this).prop('checked', false);
            } else {
                $(this).prop('checked', it ? !it.is_scheduled : false);
            }
        });
        onTnaItemCheckboxChanged();
    }

    function onTnaItemCheckboxChanged() {
        syncParticipantsFromTnaPreview(false);
    }

    function syncParticipantsFromTnaPreview(replaceAll) {
        if (!currentTnaPayload) return;
        var prevApplying = isApplyingTna;
        isApplyingTna = true;
        try {
            var itemMap = {};
            for (var i = 0; i < currentTnaPayload.items.length; i++) {
                itemMap[String(currentTnaPayload.items[i].id)] = currentTnaPayload.items[i];
            }

            var manualRows = [];
            if (!replaceAll) {
                $('#tblParticipants tbody tr').each(function() {
                    var tnaIdVal = $(this).find('.part-tna-item-id').val();
                    var unameVal = $(this).find('.part-select').val();
                    if (!tnaIdVal && unameVal) {
                        manualRows.push({
                            employee_username: unameVal,
                            employee_name: $(this).find('.part-name').val(),
                            department: $(this).find('.part-dept').val(),
                            tna_item_id: ''
                        });
                    }
                });
            }

            $('#tblParticipants tbody').html('');
            var seenUsernames = {};
            var duplicateSkipped = 0;

            $('#tblTnaItemsPreview .tna-item-checkbox:checked').each(function() {
                var it = itemMap[String($(this).val())];
                if (!it || !it.employee_username) return;
                var key = String(it.employee_username).toLowerCase();
                if (seenUsernames[key]) {
                    duplicateSkipped++;
                    return;
                }
                seenUsernames[key] = true;
                addParticipantRow({
                    employee_username: it.employee_username,
                    employee_name: it.employee_name,
                    department: it.department,
                    tna_item_id: it.id
                });
            });

            for (var m = 0; m < manualRows.length; m++) {
                var mKey = String(manualRows[m].employee_username).toLowerCase();
                if (!seenUsernames[mKey]) {
                    seenUsernames[mKey] = true;
                    addParticipantRow(manualRows[m]);
                }
            }

            if (duplicateSkipped > 0) {
                $('#tnaDedupNotice')
                    .removeClass('d-none')
                    .text(duplicateSkipped + ' item TNA untuk karyawan yang sama digabungkan menjadi 1 peserta pada jadwal ini.');
            } else {
                $('#tnaDedupNotice').addClass('d-none').text('');
            }

            $('#participants_explicit').val('1');
        } finally {
            isApplyingTna = prevApplying;
        }
    }

    $('#formNewTraining').on('input change', 'input, select', function() {
        var id = $(this).attr('id');
        if (isApplyingTna || id === 'tna_id' || id === 'tnaGroupSelector' || $(this).hasClass('tna-item-checkbox')) {
            return;
        }
        isManualDirty = true;
    });

    $('#formNewTraining').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("hrd-training-new-submit") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message);
                refreshdata();
                LoadM('divlistTraining', 'hrd-training-detail?ID=' + res.data.ID);
            },
            error: function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan jadwal.');
            }
        });
    });

    $(function() {
        var preselectedTna = $('#tna_id').val();
        if (preselectedTna) {
            loadTnaPayload(preselectedTna, true);
        }
    });
</script>
