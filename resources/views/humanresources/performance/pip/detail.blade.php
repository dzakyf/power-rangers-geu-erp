@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header mb-3">
    <h1>Performance Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-performance-pip-index') }}">PIP Plan</a></li>
        <li class="breadcrumb-item active"><span class="active">Dokumen {{ $pip->pip_no }}</span></li>
    </ol>
</div>

<!-- Header Card Status PIP -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h4 class="mb-1">{{ $pip->user->Name ?? '-' }} <span class="badge bg-danger text-white ms-2">{{ $pip->pip_no }}</span></h4>
                <p class="text-muted mb-0">
                    <strong>Departemen:</strong> {{ $pip->user->Department ?? '-' }} | 
                    <strong>Jabatan:</strong> {{ $pip->user->Position ?? '-' }} | 
                    <strong>Mentor / Atasan:</strong> {{ $pip->mentor->Name ?? '-' }}
                </p>
                <p class="text-muted mb-0">
                    <strong>Durasi Program:</strong> {{ $pip->duration_days }} Hari ({{ date('d M Y', strtotime($pip->start_date)) }} s/d {{ date('d M Y', strtotime($pip->end_date)) }}) | 
                    <strong>Asal Review:</strong> {{ $pip->review->review_no ?? 'Manual' }}
                </p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <span class="badge bg-{{ $pip->outcome_status === 'PASSED' ? 'success' : ($pip->outcome_status === 'FAILED' ? 'danger' : 'warning') }} p-2 fs-6">
                    Hasil: {{ $pip->outcome_status }}
                </span>
                <div class="mt-2">
                    <span class="h5 font-weight-bold text-primary">Skor Akhir PIP: {{ $pip->final_score !== null ? number_format($pip->final_score, 1) : '-' }}</span>
                </div>
                <div class="mt-2">
                    @if($pip->status !== 'COMPLETED')
                        <button type="button" class="btn btn-primary me-1" onclick="openEvaluateModal()">
                            <i class="feather-check-square me-1"></i> Evaluasi Kelulusan PIP
                        </button>
                    @endif
                    <a href="{{ url('hrd-performance-pip-index') }}" class="btn btn-secondary">
                        <i class="feather-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Rangkuman Masalah & Target Harapan -->
    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0 text-danger"><i class="feather-alert-triangle me-1"></i> Latar Belakang & Area Masalah Kinerja</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $pip->performance_issue_summary }}</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0 text-success"><i class="feather-target me-1"></i> Standar Hasil yang Diharapkan (Outcome)</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $pip->expected_outcome }}</p>
            </div>
        </div>

        <!-- Penugasan Modul Pelatihan Internal -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <h5 class="card-title mb-0 text-primary"><i class="feather-book-open me-1"></i> Pelatihan Pendukung (Training)</h5>
                @if($pip->status !== 'COMPLETED')
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="openTrainingModal()">
                        <i class="feather-plus me-1"></i> Tugaskan
                    </button>
                @endif
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @forelse($pip->trainings as $t)
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $t->training_title }}</strong>
                                <span class="badge bg-info text-white">{{ $t->status }}</span>
                            </div>
                            <div class="text-muted small">Kompetensi: {{ $t->target_competency ?? '-' }}</div>
                        </div>
                    @empty
                        <div class="text-muted small">Belum ada pelatihan yang ditugaskan.</div>
                    @endforelse
                </div>
            </div>
        </div>

        @if($pip->final_notes)
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Catatan Hasil Evaluasi Mentor/HCGA</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted">{{ $pip->final_notes }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Sesi Mentoring Mingguan & Action Plan -->
    <div class="col-md-7">
        <!-- Mentoring Mingguan -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Log Sesi Mentoring Mingguan</h5>
                @if($pip->status !== 'COMPLETED')
                    <button type="button" class="btn btn-sm btn-success" onclick="openCheckinModal()">
                        <i class="feather-plus me-1"></i> Catat Sesi Minggu Ini
                    </button>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;">Minggu</th>
                                <th style="width: 100px;">Tanggal</th>
                                <th>Topik Bimbingan & Arahan</th>
                                <th>Tindakan Disepakati</th>
                                <th style="width: 80px;" class="text-center">Hadir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pip->checkins as $chk)
                                <tr>
                                    <td class="text-center font-weight-bold">W-{{ $chk->week_number }}</td>
                                    <td>{{ date('d/m/Y', strtotime($chk->checkin_date)) }}</td>
                                    <td class="small">{{ $chk->discussion_notes }}</td>
                                    <td class="small text-primary font-weight-bold">{{ $chk->action_agreed }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $chk->attendance_status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada catatan mentoring mingguan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Action Plan -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Rencana Aksi & Milestone Perbaikan</h5>
                <span class="badge bg-primary">{{ $pip->actions->count() }} Kegiatan</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Rencana Tindakan</th>
                                <th>Standar Keberhasilan</th>
                                <th style="width: 100px;">Tenggat</th>
                                <th style="width: 100px;" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pip->actions as $act)
                                <tr>
                                    <td><strong>{{ $act->action_title }}</strong></td>
                                    <td class="small">{{ $act->expected_standard ?? '-' }}</td>
                                    <td class="small">{{ date('d/m/Y', strtotime($act->due_date)) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $act->status === 'ACHIEVED' ? 'success' : ($act->status === 'IN_PROGRESS' ? 'info' : 'secondary') }}">
                                            {{ $act->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada rincian action plan terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Evaluasi Akhir PIP -->
<div class="modal fade" id="modalEvaluatePip" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Evaluasi & Penentuan Kelulusan PIP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEvaluatePip">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Skor Akhir Evaluasi (0 - 100) <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" name="final_score" id="pipFinalScore" class="form-control" value="75" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Hasil Akhir Program PIP <span class="text-danger">*</span></label>
                        <select name="outcome_status" id="pipOutcomeStatus" class="form-select form-control" required>
                            <option value="PASSED">PASSED (Lulus / Performa Kembali Sesuai Standar)</option>
                            <option value="EXTENDED">EXTENDED (Diperpanjang Tambahan 30 Hari)</option>
                            <option value="FAILED">FAILED (Tidak Lulus / Direkomendasikan Eskalasi HCGA)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Catatan Evaluator / HCGA</label>
                        <textarea name="final_notes" class="form-control" rows="3" placeholder="Alasan keputusan dan rekomendasi tindak lanjut..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="savePipEvaluation()">
                    <i class="feather-check me-1"></i> Simpan Hasil Evaluasi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Sesi Mentoring Mingguan -->
<div class="modal fade" id="modalCheckin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Catat Sesi Mentoring Mingguan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCheckin">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Tanggal Pertemuan <span class="text-danger">*</span></label>
                        <input type="date" name="checkin_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Catatan Bimbingan & Kendala <span class="text-danger">*</span></label>
                        <textarea name="discussion_notes" class="form-control" rows="3" placeholder="Hal yang dibahas dan evaluasi mingguan..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Tindakan Perbaikan yang Disepakati <span class="text-danger">*</span></label>
                        <textarea name="action_agreed" class="form-control" rows="2" placeholder="Komitmen karyawan untuk 7 hari ke depan..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" onclick="saveCheckin()">
                    <i class="feather-check me-1"></i> Simpan Sesi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Penugasan Training -->
<div class="modal fade" id="modalTraining" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tugaskan Pelatihan Internal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTraining">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Pilih Modul Pelatihan Tersedia</label>
                        <select name="training_id" class="form-select form-control" onchange="fillTrainingTitle(this)">
                            <option value="">-- Pilih dari Katalog Pelatihan --</option>
                            @foreach($availableTrainings as $at)
                                <option value="{{ $at->id }}" data-title="{{ $at->training_name }}">{{ $at->training_name }} ({{ $at->category ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Judul / Topik Pelatihan <span class="text-danger">*</span></label>
                        <input type="text" name="training_title" id="inputTrainingTitle" class="form-control" placeholder="Contoh: Refreshment SOP & Akurasi Kerja" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Kompetensi yang Ditargetkan</label>
                        <input type="text" name="target_competency" class="form-control" placeholder="Contoh: Execution & Result Oriented">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="saveTraining()">
                    <i class="feather-plus me-1"></i> Tugaskan Pelatihan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openEvaluateModal() {
        var myModal = new bootstrap.Modal(document.getElementById('modalEvaluatePip'));
        myModal.show();
    }

    function openCheckinModal() {
        var myModal = new bootstrap.Modal(document.getElementById('modalCheckin'));
        myModal.show();
    }

    function openTrainingModal() {
        var myModal = new bootstrap.Modal(document.getElementById('modalTraining'));
        myModal.show();
    }

    function fillTrainingTitle(sel) {
        var opt = sel.options[sel.selectedIndex];
        if (opt && opt.getAttribute('data-title')) {
            document.getElementById('inputTrainingTitle').value = opt.getAttribute('data-title');
        }
    }

    function savePipEvaluation() {
        var form = document.getElementById('formEvaluatePip');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        var formData = new FormData(form);
        fetch("{{ url('hrd-performance-pip-evaluate', $pip->id) }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        })
        .catch(err => Swal.fire('Error', err.message, 'error'));
    }

    function saveCheckin() {
        var form = document.getElementById('formCheckin');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        var formData = new FormData(form);
        fetch("{{ url('hrd-performance-pip-checkin', $pip->id) }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        })
        .catch(err => Swal.fire('Error', err.message, 'error'));
    }

    function saveTraining() {
        var form = document.getElementById('formTraining');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        var formData = new FormData(form);
        fetch("{{ url('hrd-performance-pip-training', $pip->id) }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        })
        .catch(err => Swal.fire('Error', err.message, 'error'));
    }
</script>
@endsection
