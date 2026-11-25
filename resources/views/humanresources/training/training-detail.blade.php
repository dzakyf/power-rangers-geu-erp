<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Detail Jadwal: {{ $training->Training_Name }} ({{ $training->Task_No }})</h5>
            <small class="text-muted">Batch: <span class="badge bg-secondary text-white">{{ $training->batch_type ?? 'REGULAR' }}</span> | Status: <span class="badge bg-info text-white">{{ $training->Status }}</span> | KKM: {{ $training->passing_grade }}</small>
        </div>
        <div>
            @if(in_array(strtoupper($training->Status), ['DRAFT', 'CREATED', 'REJECTED']))
                <button type="button" class="btn btn-primary btn-sm" onclick="submitTrainingApproval({{ $training->ID }})">Ajukan Approval</button>
            @endif
            <a href="{{ url('hrd-training-evaluation-score') }}?training_id={{ $training->ID }}" class="btn btn-warning btn-sm">Input Nilai</a>
            <a href="{{ url('hrd-training-summary-detail') }}?training_id={{ $training->ID }}" class="btn btn-success btn-sm">Summary &amp; Sertifikat</a>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#divlistTraining').html('')">Tutup</button>
        </div>
    </div>
    <div class="card-body">
        <form id="formEditTraining">
            @csrf
            <input type="hidden" name="ID" value="{{ $training->ID }}">
            <div id="removedParticipantInputs"></div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Referensi TNA</label>
                    @if($isClosed)
                        <input type="hidden" name="tna_id" value="{{ $training->tna_id }}">
                    @endif
                    <select name="tna_id" id="detail_tna_id" class="form-control" {{ $isClosed ? 'disabled' : '' }} onchange="onDetailTnaChanged(this)">
                        <option value="">-- Tanpa TNA / Ad-Hoc --</option>
                        @foreach($tnas as $t)
                            <option value="{{ $t->id }}" {{ (int) $training->tna_id === (int) $t->id ? 'selected' : '' }}>
                                {{ $t->tna_no }} - {{ $t->title }} ({{ $t->department }}) [{{ $t->items_count ?? 0 }} item]
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Module &amp; Program Training</label>
                    @if($isClosed)
                        <input type="hidden" name="program_id" value="{{ $training->program_id }}">
                    @endif
                    <select name="program_id" id="detail_program_id" class="form-control" {{ $isClosed ? 'disabled' : '' }}>
                        <option value="">-- Pilih Program (Opsional) --</option>
                        @foreach($programs as $prg)
                            <option value="{{ $prg->id }}" {{ (int) $training->program_id === (int) $prg->id ? 'selected' : '' }}>
                                {{ $prg->program_code }} - {{ $prg->program_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Training *</label>
                    <input type="text" name="Training_Name" class="form-control" value="{{ $training->Training_Name }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kategori</label>
                    @if($isClosed)
                        <input type="hidden" name="Training_Category" value="{{ $training->Training_Category }}">
                    @endif
                    <select name="Training_Category" class="form-control" {{ $isClosed ? 'disabled' : '' }}>
                        <option value="">-- Pilih --</option>
                        @foreach($trCategory as $cat)
                            <option value="{{ $cat->ID }}" {{ (string) $training->Training_Category === (string) $cat->ID ? 'selected' : '' }}>{{ $cat->Category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Passing Grade (KKM)</label>
                    <input type="number" step="0.01" name="passing_grade" class="form-control" value="{{ $training->passing_grade }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Pemateri / Trainer *</label>
                    <select name="trainer_id" id="edit_trainer_id" class="form-control" required>
                        <option value="">-- Pilih Pemateri dari Master --</option>
                        @foreach($trainers as $tr)
                            <option value="{{ $tr->ID }}" {{ (int) $training->trainer_id === (int) $tr->ID ? 'selected' : '' }}>
                                {{ $tr->Trainer_Name }} ({{ $tr->Trainer_Type }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tipe</label>
                    <select name="Training_Type" class="form-control">
                        @foreach(['INTERNAL','EXTERNAL','IN_HOUSE'] as $t)
                            <option value="{{ $t }}" {{ $training->Training_Type == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Metode</label>
                    <select name="Training_Method" class="form-control">
                        @foreach(['OFFLINE','ONLINE','HYBRID'] as $m)
                            <option value="{{ $m }}" {{ $training->Training_Method == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="Training_Location" class="form-control" value="{{ $training->Training_Location }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="Start_Date" class="form-control" value="{{ $training->Start_Date }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="End_Date" class="form-control" value="{{ $training->End_Date }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kompetensi Target</label>
                    <input type="text" name="Training_Competence" class="form-control" value="{{ $training->Training_Competence }}">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Tujuan</label>
                    <input type="text" name="Training_Objective" class="form-control" value="{{ $training->Training_Objective }}">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Kurikulum Sesi ({{ $training->curriculums->count() }})</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addDetailCurriculumRow()">+ Tambah Sesi</button>
            </div>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm" id="tblDetailCurriculums">
                    <thead class="table-light">
                        <tr>
                            <th>Sesi</th>
                            <th>Topik Materi</th>
                            <th>Pemateri</th>
                            <th>Durasi (Menit)</th>
                            <th>Metode</th>
                            <th style="width:50px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($training->curriculums as $idx => $cur)
                            @php $spName = (string) ($cur->speaker_name ?? ''); @endphp
                            <tr>
                                <td>{{ $cur->session_order }}</td>
                                <td><input type="text" name="curriculums[{{ $idx }}][topic_title]" class="form-control form-control-sm" value="{{ $cur->topic_title }}"></td>
                                <td>
                                    <select name="curriculums[{{ $idx }}][speaker_name]" class="form-control form-control-sm">
                                        <option value="">-- Ikuti Pemateri Utama --</option>
                                        @foreach($trainers as $tr)
                                            <option value="{{ $tr->Trainer_Name }}" {{ $spName === $tr->Trainer_Name ? 'selected' : '' }}>
                                                {{ $tr->Trainer_Name }} ({{ $tr->Trainer_Type }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="curriculums[{{ $idx }}][duration_minutes]" class="form-control form-control-sm" value="{{ $cur->duration_minutes }}"></td>
                                <td><input type="text" name="curriculums[{{ $idx }}][learning_method]" class="form-control form-control-sm" value="{{ $cur->learning_method }}"></td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()">x</button></td>
                            </tr>
                        @empty
                            <tr class="empty-cur-row"><td colspan="6" class="text-center text-muted">Belum ada kurikulum.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <h6 class="mb-0">Daftar Peserta ({{ $training->participants->count() }})</h6>
                @if(!$isClosed)
                    <div class="d-flex gap-2">
                        <button type="button"
                            id="btnDetailSyncTna"
                            class="btn btn-sm btn-info {{ empty($training->tna_id) ? 'd-none' : '' }}"
                            onclick="openDetailTnaSyncPanel()">
                            Tarik Peserta dari TNA
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addDetailParticipantRow()">+ Tambah Peserta</button>
                    </div>
                @endif
            </div>

            @if(!$isClosed)
                <div id="detailTnaSyncPanel" class="card border-info mb-3 d-none">
                    <div class="card-header bg-info text-white py-2 d-flex justify-content-between align-items-center">
                        <strong>Tarik Peserta dari Referensi TNA</strong>
                        <button type="button" class="btn btn-sm btn-light" onclick="$('#detailTnaSyncPanel').addClass('d-none')">Tutup</button>
                    </div>
                    <div class="card-body py-2">
                        <div class="row align-items-end mb-2">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <label class="form-label small fw-bold mb-1">Filter Grup Program / Topik TNA</label>
                                <select id="detailTnaGroupSelect" class="form-control form-control-sm" onchange="renderDetailTnaSyncRows(this.value)"></select>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <button type="button" class="btn btn-success btn-sm" onclick="applyDetailTnaParticipants()">Masukkan Peserta Terpilih</button>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                            <table class="table table-bordered table-sm mb-0" id="tblDetailTnaSync">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:36px;" class="text-center">Pilih</th>
                                        <th>Karyawan</th>
                                        <th>Rekomendasi / Program</th>
                                        <th>Status Penjadwalan</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm" id="tblDetailParticipants">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Karyawan</th>
                            <th>Departemen</th>
                            <th>Kehadiran</th>
                            <th>Skor Akhir</th>
                            <th>Status</th>
                            <th style="width:90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($training->participants as $idx => $p)
                            @php $isLocked = in_array($p->id, $lockedParticipantIds ?? [], true); @endphp
                            <tr data-username="{{ strtolower($p->employee_username) }}" data-participant-id="{{ $p->id }}">
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    {{ $p->employee_name }} ({{ $p->employee_username }})
                                    @if($p->tna_item_id)
                                        <span class="badge bg-info text-white ms-1">Dari TNA</span>
                                    @endif
                                </td>
                                <td>{{ $p->department ?? '-' }}</td>
                                <td>{{ $p->attendance_status }}</td>
                                <td>{{ $p->final_score !== null ? number_format($p->final_score, 2) : '-' }}</td>
                                <td>
                                    @if($p->is_passed === true)
                                        <span class="badge bg-success text-white">LULUS</span>
                                    @elseif($p->is_passed === false)
                                        <span class="badge bg-danger text-white">TIDAK LULUS</span>
                                    @else
                                        <span class="badge bg-secondary text-white">BELUM DINILAI</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($isClosed)
                                        <span class="badge bg-secondary text-white">Closed</span>
                                    @elseif($isLocked)
                                        <button type="button" class="btn btn-secondary btn-sm" disabled title="Terkunci: peserta sudah memiliki nilai evaluasi, feedback, sertifikat, atau coaching">Terkunci</button>
                                    @else
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeExistingParticipant(this, {{ $p->id }}, '{{ e($p->employee_name) }}')">x</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-part-row"><td colspan="7" class="text-center text-muted">Belum ada peserta.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-success">Update Jadwal</button>
        </form>
    </div>
</div>

<script>
    var detailCurIdx = {{ $training->curriculums->count() }};
    var detailPartIdx = 0;
    var detailEmployeeOptions = @json($employees);
    var detailTnaPayload = null;

    function escapeDetailHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function onDetailTnaChanged(sel) {
        if ($(sel).val()) {
            $('#btnDetailSyncTna').removeClass('d-none');
        } else {
            $('#btnDetailSyncTna').addClass('d-none');
            $('#detailTnaSyncPanel').addClass('d-none');
        }
    }

    function addDetailCurriculumRow() {
        $('#tblDetailCurriculums tbody tr.empty-cur-row').remove();
        var idx = detailCurIdx++;
        var optionsHtml = '<option value="">-- Ikuti Pemateri Utama --</option>'
            @foreach($trainers as $tr)
            + '<option value="{{ e($tr->Trainer_Name) }}">{{ e($tr->Trainer_Name) }} ({{ e($tr->Trainer_Type) }})</option>'
            @endforeach
            ;

        var row = '<tr>'
            + '<td>' + (idx + 1) + '</td>'
            + '<td><input type="text" name="curriculums[' + idx + '][topic_title]" class="form-control form-control-sm" placeholder="Topik sesi"></td>'
            + '<td><select name="curriculums[' + idx + '][speaker_name]" class="form-control form-control-sm">' + optionsHtml + '</select></td>'
            + '<td><input type="number" name="curriculums[' + idx + '][duration_minutes]" class="form-control form-control-sm" value="60"></td>'
            + '<td><input type="text" name="curriculums[' + idx + '][learning_method]" class="form-control form-control-sm" value="Presentasi & Diskusi"></td>'
            + '<td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest(\'tr\').remove()">x</button></td>'
            + '</tr>';
        $('#tblDetailCurriculums tbody').append(row);
    }

    function getDetailUsedUsernames(excludeSelect) {
        var used = {};
        $('#tblDetailParticipants tbody tr[data-username]').each(function() {
            var u = $(this).attr('data-username');
            if (u) used[String(u).toLowerCase()] = true;
        });
        $('#tblDetailParticipants tbody select.detail-part-select').each(function() {
            if (excludeSelect && this === excludeSelect) return;
            var v = $(this).val();
            if (v) used[String(v).toLowerCase()] = true;
        });
        return used;
    }

    function addDetailParticipantRow() {
        $('#tblDetailParticipants tbody tr.empty-part-row').remove();
        var idx = detailPartIdx++;
        var optsHtml = '<option value="">-- Pilih Karyawan --</option>';
        for (var i = 0; i < detailEmployeeOptions.length; i++) {
            var emp = detailEmployeeOptions[i];
            optsHtml += '<option value="' + escapeDetailHtml(emp.username) + '" data-name="' + escapeDetailHtml(emp.name) + '" data-dept="' + escapeDetailHtml(emp.Department || '') + '">'
                + escapeDetailHtml((emp.name || emp.username) + ' (' + emp.username + ')')
                + '</option>';
        }

        var row = '<tr class="new-participant-row">'
            + '<td><span class="badge bg-primary text-white">Baru</span></td>'
            + '<td>'
            + '<select name="participants[' + idx + '][employee_username]" class="form-control form-control-sm detail-part-select" onchange="syncDetailPartInfo(this)">' + optsHtml + '</select>'
            + '<input type="hidden" name="participants[' + idx + '][employee_name]" class="detail-part-name">'
            + '<input type="hidden" name="participants[' + idx + '][tna_item_id]" class="detail-part-tna-item-id" value="">'
            + '</td>'
            + '<td><input type="text" name="participants[' + idx + '][department]" class="form-control form-control-sm detail-part-dept" readonly></td>'
            + '<td>REGISTERED</td>'
            + '<td>-</td>'
            + '<td><span class="badge bg-secondary text-white">BELUM DINILAI</span></td>'
            + '<td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="removeNewDetailParticipant(this)">x</button></td>'
            + '</tr>';
        $('#tblDetailParticipants tbody').append(row);
    }

    function syncDetailPartInfo(sel) {
        var val = $(sel).val();
        var tr = $(sel).closest('tr');
        if (val) {
            var used = getDetailUsedUsernames(sel);
            if (used[String(val).toLowerCase()]) {
                alert('Karyawan tersebut sudah terdaftar pada jadwal ini.');
                $(sel).val('');
                tr.attr('data-username', '');
                tr.find('.detail-part-name').val('');
                tr.find('.detail-part-dept').val('');
                return;
            }
            tr.attr('data-username', String(val).toLowerCase());
        } else {
            tr.attr('data-username', '');
        }
        var opt = $(sel).find('option:selected');
        tr.find('.detail-part-name').val(opt.data('name') || '');
        tr.find('.detail-part-dept').val(opt.data('dept') || '');
    }

    function removeExistingParticipant(btn, participantId, empName) {
        if (!confirm('Hapus peserta ' + empName + ' dari jadwal ini?')) return;
        $('#removedParticipantInputs').append('<input type="hidden" name="removed_participant_ids[]" value="' + participantId + '">');
        $(btn).closest('tr').remove();
        if ($('#tblDetailParticipants tbody tr').length === 0) {
            $('#tblDetailParticipants tbody').html('<tr class="empty-part-row"><td colspan="7" class="text-center text-muted">Belum ada peserta.</td></tr>');
        }
    }

    function removeNewDetailParticipant(btn) {
        $(btn).closest('tr').remove();
        if ($('#tblDetailParticipants tbody tr').length === 0) {
            $('#tblDetailParticipants tbody').html('<tr class="empty-part-row"><td colspan="7" class="text-center text-muted">Belum ada peserta.</td></tr>');
        }
    }

    function openDetailTnaSyncPanel() {
        var tnaId = $('#detail_tna_id').val();
        if (!tnaId) {
            alert('Pilih Referensi TNA terlebih dahulu.');
            return;
        }
        $.get('{{ url("hrd-training-tna-payload") }}', {
            tna_id: tnaId,
            training_id: {{ $training->ID }}
        }, function(res) {
            if (!res || res.status !== 'success' || !res.data) {
                alert('Gagal memuat data peserta TNA.');
                return;
            }
            detailTnaPayload = res.data;
            var groups = detailTnaPayload.groups || [];
            var allGroup = detailTnaPayload.all_group;
            var opts = '<option value="ALL">' + escapeDetailHtml(allGroup.label) + '</option>';
            for (var i = 0; i < groups.length; i++) {
                opts += '<option value="' + escapeDetailHtml(groups[i].key) + '">'
                    + escapeDetailHtml(groups[i].label + ' [' + groups[i].unscheduled_count + '/' + groups[i].total_items + ' belum dijadwalkan]')
                    + '</option>';
            }
            $('#detailTnaGroupSelect').html(opts).val('ALL');
            renderDetailTnaSyncRows('ALL');
            $('#detailTnaSyncPanel').removeClass('d-none');
        }).fail(function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memuat data TNA.');
        });
    }

    function renderDetailTnaSyncRows(groupKey) {
        if (!detailTnaPayload) return;
        var items = detailTnaPayload.items || [];
        var usedUsernames = getDetailUsedUsernames(null);
        var html = '';
        var count = 0;

        for (var i = 0; i < items.length; i++) {
            var it = items[i];
            if (groupKey !== 'ALL' && it.group_key !== groupKey) continue;
            count++;

            var alreadyHere = !!it.in_current_training || !!usedUsernames[String(it.employee_username || '').toLowerCase()];
            var statusHtml = '';
            var checkboxHtml = '';

            if (alreadyHere) {
                statusHtml = '<span class="badge bg-secondary text-white">Sudah di Jadwal Ini</span>';
                checkboxHtml = '<input type="checkbox" class="form-check-input" disabled>';
            } else if (it.is_scheduled && it.scheduled_trainings && it.scheduled_trainings.length > 0) {
                var tasks = [];
                for (var s = 0; s < it.scheduled_trainings.length; s++) {
                    tasks.push(it.scheduled_trainings[s].task_no);
                }
                statusHtml = '<span class="badge bg-warning text-dark">Sudah Dijadwalkan: ' + escapeDetailHtml(tasks.join(', ')) + '</span>';
                checkboxHtml = '<input type="checkbox" class="form-check-input detail-tna-sync-check" value="' + it.id + '">';
            } else {
                statusHtml = '<span class="badge bg-success text-white">Belum Dijadwalkan</span>';
                checkboxHtml = '<input type="checkbox" class="form-check-input detail-tna-sync-check" value="' + it.id + '" checked>';
            }

            html += '<tr>'
                + '<td class="text-center">' + checkboxHtml + '</td>'
                + '<td><strong>' + escapeDetailHtml(it.employee_name) + '</strong><br><small class="text-muted">' + escapeDetailHtml(it.employee_username) + ' | ' + escapeDetailHtml(it.department || '-') + '</small></td>'
                + '<td>' + escapeDetailHtml(it.recommended_training || '-') + (it.program_name ? '<br><small class="text-muted">' + escapeDetailHtml(it.program_name) + '</small>' : '') + '</td>'
                + '<td>' + statusHtml + '</td>'
                + '</tr>';
        }

        if (count === 0) {
            html = '<tr><td colspan="4" class="text-center text-muted">Tidak ada item TNA pada grup ini.</td></tr>';
        }
        $('#tblDetailTnaSync tbody').html(html);
    }

    function applyDetailTnaParticipants() {
        if (!detailTnaPayload) return;
        var itemMap = {};
        for (var i = 0; i < detailTnaPayload.items.length; i++) {
            itemMap[String(detailTnaPayload.items[i].id)] = detailTnaPayload.items[i];
        }

        var used = getDetailUsedUsernames(null);
        var addedCount = 0;

        $('#tblDetailTnaSync .detail-tna-sync-check:checked').each(function() {
            var it = itemMap[String($(this).val())];
            if (!it || !it.employee_username) return;
            var key = String(it.employee_username).toLowerCase();
            if (used[key]) return;

            used[key] = true;
            $('#tblDetailParticipants tbody tr.empty-part-row').remove();
            var idx = detailPartIdx++;

            var row = '<tr class="new-participant-row" data-username="' + escapeDetailHtml(key) + '">'
                + '<td><span class="badge bg-primary text-white">Baru</span></td>'
                + '<td>'
                + escapeDetailHtml(it.employee_name + ' (' + it.employee_username + ')')
                + ' <span class="badge bg-info text-white ms-1">Dari TNA</span>'
                + '<input type="hidden" name="participants[' + idx + '][employee_username]" value="' + escapeDetailHtml(it.employee_username) + '">'
                + '<input type="hidden" name="participants[' + idx + '][employee_name]" value="' + escapeDetailHtml(it.employee_name) + '">'
                + '<input type="hidden" name="participants[' + idx + '][tna_item_id]" value="' + escapeDetailHtml(it.id) + '">'
                + '</td>'
                + '<td><input type="text" name="participants[' + idx + '][department]" class="form-control form-control-sm" value="' + escapeDetailHtml(it.department || '') + '" readonly></td>'
                + '<td>REGISTERED</td>'
                + '<td>-</td>'
                + '<td><span class="badge bg-secondary text-white">BELUM DINILAI</span></td>'
                + '<td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="removeNewDetailParticipant(this)">x</button></td>'
                + '</tr>';
            $('#tblDetailParticipants tbody').append(row);
            addedCount++;
        });

        if (addedCount === 0) {
            alert('Tidak ada peserta baru yang dipilih atau semua peserta terpilih sudah terdaftar di jadwal ini.');
            return;
        }

        $('#detailTnaSyncPanel').addClass('d-none');
    }

    $('#formEditTraining').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("hrd-training-detail-submit") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message);
                refreshdata();
                LoadM('divlistTraining', 'hrd-training-detail?ID={{ $training->ID }}');
            },
            error: function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal update.');
            }
        });
    });

    function submitTrainingApproval(id) {
        if (!confirm('Ajukan Jadwal Training ini ke Approval Inbox?')) return;
        $.post('{{ url("hrd-training-submit-approval") }}', {
            _token: '{{ csrf_token() }}',
            ID: id
        }, function(res) {
            alert(res.message);
            refreshdata();
            LoadM('divlistTraining', 'hrd-training-detail?ID=' + id);
        }).fail(function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengajukan approval.');
        });
    }
</script>
