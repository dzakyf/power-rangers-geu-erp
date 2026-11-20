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
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Training *</label>
                    <input type="text" name="Training_Name" class="form-control" value="{{ $training->Training_Name }}" required>
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
                    <label class="form-label">Passing Grade (KKM)</label>
                    <input type="number" step="0.01" name="passing_grade" class="form-control" value="{{ $training->passing_grade }}">
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
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="Start_Date" class="form-control" value="{{ $training->Start_Date }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="End_Date" class="form-control" value="{{ $training->End_Date }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="Training_Location" class="form-control" value="{{ $training->Training_Location }}">
                </div>
                <div class="col-md-6 mb-3">
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

            <h6>Daftar Peserta ({{ $training->participants->count() }})</h6>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Karyawan</th>
                            <th>Departemen</th>
                            <th>Kehadiran</th>
                            <th>Skor Akhir</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($training->participants as $idx => $p)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $p->employee_name }} ({{ $p->employee_username }})</td>
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
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Belum ada peserta.</td></tr>
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

    $('#formEditTraining').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("hrd-training-detail-submit") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message);
                refreshdata();
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
