@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
@include('humanresources.performance.partials.page-header', [
    'title'       => 'Detail Sasaran Kerja (KPI / OKR)',
    'parentLabel' => 'KPI & OKR',
    'parentUrl'   => url('hrd-performance-kpi-index'),
    'current'     => $goal->goal_no,
    'icon'        => 'fa-bullseye',
    'badgeText'   => 'Goal Sheet Detail',
])

<!-- Header Card Status -->
@php
    $goalSt = strtoupper((string) $goal->status);
    $stBadge = in_array($goalSt, ['APPROVED', 'COMPLETED']) ? 'bg-success text-white' : ($goalSt === 'REJECTED' ? 'bg-danger text-white' : ($goalSt === 'WAITING_APPROVAL' ? 'bg-warning text-dark' : 'bg-secondary text-white'));
    $stIcon = in_array($goalSt, ['APPROVED', 'COMPLETED']) ? 'fa-circle-check' : ($goalSt === 'REJECTED' ? 'fa-circle-xmark' : ($goalSt === 'WAITING_APPROVAL' ? 'fa-clock' : 'fa-file-lines'));
    $pct = (float) $goal->overall_progress_percent;
@endphp
<div class="card shadow-sm border mb-3">
    <div class="card-body">
        <div class="row align-items-center g-3">
            <div class="col-md-7">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="fa {{ $goal->scope_type === 'POSITION' ? 'fa-briefcase' : 'fa-user-tie' }} text-primary fs-18"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="mb-0 fw-bold text-dark">
                                {{ $goal->scope_type === 'POSITION' ? ('Template Posisi: ' . ($goal->position ?? '-')) : ($goal->user->Name ?? '-') }}
                            </h4>
                            <span class="badge bg-primary text-white">{{ $goal->goal_no }}</span>
                            @if(($goal->goal_category ?? 'REGULAR') === 'PROBATION')
                                <span class="badge bg-warning text-dark">PROBATION {{ $goal->probation_months ? $goal->probation_months . ' Bln' : '' }}</span>
                            @endif
                            @if(!empty($goal->is_addon))
                                <span class="badge bg-info text-dark">KPI TAMBAHAN (ADD-ON)</span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-3 text-muted small mt-1">
                            <span><i class="fa fa-building me-1"></i><strong>Dept:</strong> {{ $goal->department ?? '-' }}</span>
                            <span><i class="fa fa-id-badge me-1"></i><strong>Jabatan:</strong> {{ $goal->position ?? '-' }}</span>
                            <span><i class="fa fa-calendar me-1"></i><strong>Periode:</strong> {{ $goal->period->name ?? '-' }} ({{ $goal->period->period_code ?? '-' }})</span>
                        </div>
                        <div class="d-flex flex-wrap gap-3 text-muted small mt-1">
                            <span><strong>Tipe:</strong> {{ $goal->goal_type }}</span>
                            <span><strong>Level:</strong> {{ $goal->level }}</span>
                            @if(!empty($goal->probation_end_date))
                                <span class="text-primary"><strong>Batas Evaluasi:</strong> {{ date('d M Y', strtotime($goal->probation_end_date)) }}</span>
                            @endif
                        </div>
                        @if(!empty($goal->reschedule_reason))
                            <div class="small text-warning mt-1">
                                <i class="fa fa-info-circle me-1"></i><strong>Catatan Perubahan Jadwal:</strong> {{ $goal->reschedule_reason }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-5 text-md-end">
                <div class="mb-2">
                    <span class="badge {{ $stBadge }} px-3 py-2 fs-12">
                        <i class="fa {{ $stIcon }} me-1"></i> Status: {{ $goal->status }}
                    </span>
                </div>
                <div class="mb-2">
                    <div class="d-flex justify-content-md-end align-items-center gap-2 mb-1">
                        <span class="small text-muted fw-bold">Progres Keseluruhan:</span>
                        <span class="fw-bold text-primary fs-15">{{ number_format($pct, 1) }}%</span>
                    </div>
                    <div class="progress ms-md-auto" style="height: 8px; max-width: 240px;">
                        <div class="progress-bar bg-{{ $pct >= 100 ? 'success' : ($pct >= 50 ? 'primary' : 'info') }}" role="progressbar" style="width: {{ min(100, $pct) }}%;"></div>
                    </div>
                </div>
                <div class="d-flex flex-wrap justify-content-md-end gap-1 mt-2">
                    @if(in_array($goal->status, ['DRAFT', 'REJECTED']) && (!isset($canManageGoal) || $canManageGoal))
                        <a href="{{ url('hrd-performance-kpi-edit', $goal->id) }}" class="btn btn-sm btn-warning text-dark">
                            <i class="fa fa-pen me-1"></i> Edit Target
                        </a>
                        <button type="button" class="btn btn-sm btn-success" onclick="submitToApproval({{ $goal->id }})">
                            <i class="fa fa-paper-plane me-1"></i> Ajukan Approval
                        </button>
                    @endif
                    <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalRescheduleGoal">
                        <i class="fa fa-calendar-alt me-1"></i> Ubah Waktu
                    </button>
                    <a href="{{ url('hrd-performance-kpi-index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@if($goal->status === 'REJECTED' && $goal->rejection_reason)
<div class="alert alert-danger mb-3">
    <strong><i class="feather-alert-triangle me-1"></i> Ditolak pada Approval:</strong> {{ $goal->rejection_reason }}
    <div class="small mt-1">Silakan klik <strong>Edit / Revisi Target</strong> untuk memperbaiki indikator, lalu ajukan ulang approval.</div>
</div>
@endif

@php
    $displayStages = isset($approvalStages) && $approvalStages->count() > 0 ? $approvalStages : ($approvalTasks ?? collect());
@endphp
@if($displayStages->count() > 0)
<div class="card shadow-sm border mb-3">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center bg-white py-2">
        <h6 class="card-title mb-0 fw-bold">
            <i class="fa fa-sitemap text-primary me-2"></i>Alur Persetujuan (Atasan Langsung &amp; HCGA)
        </h6>
        @if(!empty($isCurrentUserApprover))
            <a href="{{ url('hrd-approval-inbox') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-inbox me-1"></i> Proses di Approval Inbox
            </a>
        @endif
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            @foreach($displayStages as $task)
                @php
                    $st = strtoupper((string) ($task->Status ?? 'WAITING_PREVIOUS'));
                    $borderClass = $st === 'APPROVED' ? 'border-success bg-success-transparent' : ($st === 'REJECTED' ? 'border-danger bg-danger-transparent' : ($st === 'PENDING' ? 'border-warning' : 'border-secondary bg-light'));
                    $badgeClass = $st === 'APPROVED' ? 'bg-success text-white' : ($st === 'REJECTED' ? 'bg-danger text-white' : ($st === 'PENDING' ? 'bg-warning text-dark' : 'bg-secondary text-white'));
                    $iconClass = $st === 'APPROVED' ? 'fa-circle-check text-success' : ($st === 'REJECTED' ? 'fa-circle-xmark text-danger' : ($st === 'PENDING' ? 'fa-clock text-warning' : 'fa-hourglass-half text-secondary'));
                    $statusText = $st === 'WAITING_PREVIOUS' ? 'MENUNGGU STAGE SEBELUMNYA' : $st;
                @endphp
                <div class="col-md-6">
                    <div class="border {{ $borderClass }} rounded p-3 h-100 d-flex justify-content-between align-items-start shadow-xs">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa {{ $iconClass }} fs-18 mt-1"></i>
                            <div>
                                <div class="fw-bold text-dark">
                                    Stage {{ $task->Stage_Order }}: {{ $task->Role_Label ?? $task->Label }}
                                </div>
                                @if(!empty($task->Label) && !empty($task->Role_Label) && $task->Label !== $task->Role_Label)
                                    <div class="small text-muted">{{ $task->Label }}</div>
                                @endif
                                <div class="small text-muted mt-1">
                                    <strong>Approver:</strong>
                                    @if(!empty($task->approver_names))
                                        {{ $task->approver_names }}
                                    @elseif($st === 'WAITING_PREVIOUS')
                                        Akan ditentukan setelah Stage sebelumnya disetujui
                                    @else
                                        Menunggu Resolusi
                                    @endif
                                </div>
                                @if(!empty($task->Completed_At))
                                    <div class="small text-success mt-1">
                                        <i class="fa fa-calendar-check me-1"></i>Diproses: {{ date('d/m/Y H:i', strtotime($task->Completed_At)) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <span class="badge {{ $badgeClass }} px-2 py-1">{{ $statusText }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        @if(isset($approvalLogs) && $approvalLogs->count() > 0)
            <div class="border-top pt-2">
                <div class="small fw-bold text-muted mb-2"><i class="fa fa-history me-1"></i>Riwayat Aktivitas Approval</div>
                <div class="d-flex flex-column gap-1">
                    @foreach($approvalLogs->take(5) as $log)
                        @php
                            $act = strtoupper((string) $log->Action);
                            $actBadge = $act === 'APPROVED' ? 'bg-success' : ($act === 'REJECTED' ? 'bg-danger' : ($act === 'SUBMITTED' ? 'bg-primary' : 'bg-secondary'));
                        @endphp
                        <div class="d-flex flex-wrap align-items-center gap-2 small py-1 border-bottom border-light">
                            <span class="text-muted" style="min-width: 115px;">{{ date('d/m/Y H:i', strtotime($log->Created_At)) }}</span>
                            <span class="badge {{ $actBadge }} text-white">{{ $log->Action }}</span>
                            <strong>{{ $log->actor_name ?? 'System' }}</strong>
                            @if(!empty($log->Note))
                                <span class="text-muted">— {{ $log->Note }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endif

<!-- Tabel Items & Progres -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Indikator Kinerja & Pencapaian Aktual</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">Tipe</th>
                        <th>Sasaran / Indikator</th>
                        <th style="width: 80px;" class="text-center">Satuan</th>
                        <th style="width: 100px;" class="text-end">Target</th>
                        <th style="width: 100px;" class="text-end">Aktual</th>
                        <th style="width: 80px;" class="text-end">Bobot</th>
                        <th style="width: 160px;">Progres (%)</th>
                        <th style="width: 90px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($goal->items as $item)
                        <tr>
                            <td>
                                <span class="badge bg-{{ $item->item_type === 'KPI' ? 'info' : ($item->item_type === 'OBJECTIVE' ? 'primary' : 'secondary') }} text-white">
                                    {{ $item->item_type }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $item->title }}</strong>
                                @if($item->description)
                                    <div class="text-muted small">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->kpi_unit ?? '-' }}</td>
                            <td class="text-end">{{ number_format($item->target_value, 2) }}</td>
                            <td class="text-end font-weight-bold text-success">{{ number_format($item->actual_value, 2) }}</td>
                            <td class="text-end">{{ $item->weight_percent > 0 ? number_format($item->weight_percent, 1) . '%' : '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 14px;">
                                        <div class="progress-bar bg-{{ $item->progress_percent >= 100 ? 'success' : ($item->progress_percent > 50 ? 'primary' : 'warning') }}" 
                                             role="progressbar" 
                                             style="width: {{ min(100, $item->progress_percent) }}%;">
                                        </div>
                                    </div>
                                    <span class="small font-weight-bold">{{ number_format($item->progress_percent, 1) }}%</span>
                                </div>
                            </td>
                            <td class="text-center">
                                @if(in_array($goal->status, ['APPROVED', 'EVALUATION']))
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="openProgressModal({{ $item->id }}, '{{ addslashes($item->title) }}', {{ $item->actual_value }}, '{{ $item->kpi_unit }}')">
                                        <i class="feather-edit-3 me-1"></i> Check-in
                                    </button>
                                @else
                                    <span class="badge bg-secondary text-white">Menunggu Approved</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada item sasaran kerja terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Check-in Progress -->
<div class="modal fade" id="modalCheckInProgress" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProgressTitle">Check-in Progres Target</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCheckInProgress">
                    <input type="hidden" id="modalItemId" name="item_id">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Target / Indikator</label>
                        <p id="modalItemName" class="text-primary mb-0 font-weight-bold"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Check-in <span class="text-danger">*</span></label>
                        <input type="date" name="check_in_date" id="modalCheckInDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nilai Realisasi Aktual Baru (<span id="modalItemUnit"></span>) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="new_value" id="modalNewValue" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Pencapaian / Keterangan</label>
                        <textarea name="comment" id="modalComment" class="form-control" rows="2" placeholder="Jelaskan bukti atau ringkasan capaian..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="saveCheckInProgress()">
                    <i class="feather-check me-1"></i> Simpan Capaian
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function submitToApproval(goalId) {
        Swal.fire({
            title: 'Ajukan Approval?',
            text: 'Sasaran kerja akan diajukan ke Approval Workflow Engine untuk disetujui atasan/manajemen.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ajukan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("{{ url('hrd-performance-kpi-submit-approval') }}/" + goalId, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
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
        });
    }

    function openProgressModal(itemId, title, currentVal, unit) {
        document.getElementById('modalItemId').value = itemId;
        document.getElementById('modalItemName').innerText = title;
        document.getElementById('modalNewValue').value = currentVal;
        document.getElementById('modalItemUnit').innerText = unit || '%';
        var myModal = new bootstrap.Modal(document.getElementById('modalCheckInProgress'));
        myModal.show();
    }

    function saveCheckInProgress() {
        var itemId = document.getElementById('modalItemId').value;
        var form = document.getElementById('formCheckInProgress');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        var formData = new FormData(form);
        fetch("{{ url('hrd-performance-kpi-record-progress') }}/" + itemId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                } else {
                    alert(res.message);
                    location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') { Swal.fire('Gagal', res.message, 'error'); } else { alert(res.message); }
            }
        })
        .catch(err => {
            if (typeof Swal !== 'undefined') { Swal.fire('Error', err.message, 'error'); } else { alert(err.message); }
        });
    }

    function applyReschedulePreset() {
        var preset = document.getElementById('reschedulePreset').value;
        var startInput = document.getElementById('rescheduleStartDate');
        var endInput = document.getElementById('rescheduleEndDate');
        var hiddenMonths = document.getElementById('rescheduleMonths');
        if (preset === 'custom') {
            hiddenMonths.value = '0';
            return;
        }
        var startVal = startInput.value || new Date().toISOString().slice(0, 10);
        startInput.value = startVal;
        var d = new Date(startVal + 'T00:00:00');
        if (isNaN(d.getTime())) return;
        if (preset.endsWith('w')) {
            d.setDate(d.getDate() + (parseInt(preset, 10) * 7));
            hiddenMonths.value = '0';
        } else if (preset.endsWith('m')) {
            var m = parseInt(preset, 10);
            d.setMonth(d.getMonth() + m);
            hiddenMonths.value = m;
        }
        var yyyy = d.getFullYear();
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var dd = String(d.getDate()).padStart(2, '0');
        endInput.value = yyyy + '-' + mm + '-' + dd;
    }

    function submitRescheduleGoal() {
        var form = document.getElementById('formRescheduleGoal');
        if (!form.checkValidity()) { form.reportValidity(); return; }
        fetch("{{ url('hrd-performance-kpi-reschedule', $goal->id) }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: new FormData(form)
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                } else {
                    alert(res.message);
                    location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') { Swal.fire('Gagal', res.message, 'error'); } else { alert(res.message); }
            }
        })
        .catch(err => {
            if (typeof Swal !== 'undefined') { Swal.fire('Error', err.message, 'error'); } else { alert(err.message); }
        });
    }
</script>

<!-- Modal Ubah Waktu / Durasi KPI -->
<div class="modal fade" id="modalRescheduleGoal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Waktu / Durasi Penilaian KPI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRescheduleGoal">
                    <div class="mb-2">
                        <label class="form-label fw-bold">Preset Waktu Penilaian (Mingguan / Bulanan / Custom)</label>
                        <select id="reschedulePreset" class="form-select form-select-sm" onchange="applyReschedulePreset()">
                            <option value="custom" {{ empty($goal->probation_months) ? 'selected' : '' }}>Custom Tanggal Bebas</option>
                            <option value="1w">Per 1 Minggu (7 Hari)</option>
                            <option value="2w">Per 2 Minggu (14 Hari)</option>
                            <option value="3w">Per 3 Minggu (21 Hari)</option>
                            <option value="1m" {{ (int) ($goal->probation_months ?? 0) === 1 ? 'selected' : '' }}>1 Bulan</option>
                            <option value="2m" {{ (int) ($goal->probation_months ?? 0) === 2 ? 'selected' : '' }}>2 Bulan</option>
                            <option value="3m" {{ (int) ($goal->probation_months ?? 0) === 3 ? 'selected' : '' }}>3 Bulan</option>
                            <option value="6m" {{ (int) ($goal->probation_months ?? 0) === 6 ? 'selected' : '' }}>6 Bulan</option>
                            <option value="12m" {{ (int) ($goal->probation_months ?? 0) === 12 ? 'selected' : '' }}>12 Bulan (1 Tahun)</option>
                        </select>
                        <input type="hidden" name="probation_months" id="rescheduleMonths" value="{{ (int) ($goal->probation_months ?? 0) }}">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-bold">Tanggal Mulai</label>
                            <input type="date" name="probation_start_date" id="rescheduleStartDate" class="form-control form-control-sm" value="{{ $goal->probation_start_date }}" onchange="applyReschedulePreset()">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Tanggal Berakhir / Evaluasi</label>
                            <input type="date" name="probation_end_date" id="rescheduleEndDate" class="form-control form-control-sm" value="{{ $goal->probation_end_date }}" onchange="document.getElementById('reschedulePreset').value='custom'; document.getElementById('rescheduleMonths').value='0';">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">Alasan Perubahan Waktu <span class="text-danger">*</span></label>
                        <textarea name="reschedule_reason" class="form-control form-control-sm" rows="2" placeholder="Misal: Percepatan evaluasi dari 6 bulan ke 3 bulan..." required>{{ $goal->reschedule_reason }}</textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="submitRescheduleGoal()">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>
@endsection

