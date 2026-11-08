@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header mb-3">
    <h1>Performance Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-performance-kpi-index') }}">KPI & OKR</a></li>
        <li class="breadcrumb-item active"><span class="active">Detail Sasaran {{ $goal->goal_no }}</span></li>
    </ol>
</div>

<!-- Header Card Status -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h4 class="mb-1">{{ $goal->user->Name ?? '-' }} <span class="badge bg-primary text-white ms-2">{{ $goal->goal_no }}</span></h4>
                <p class="text-muted mb-0">
                    <strong>Departemen:</strong> {{ $goal->department ?? '-' }} | 
                    <strong>Jabatan:</strong> {{ $goal->position ?? '-' }} | 
                    <strong>Periode:</strong> {{ $goal->period->name ?? '-' }} ({{ $goal->period->period_code ?? '-' }})
                </p>
                <p class="text-muted mb-0">
                    <strong>Tipe Sasaran:</strong> {{ $goal->goal_type }} | <strong>Level:</strong> {{ $goal->level }}
                </p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <span class="badge bg-{{ in_array($goal->status, ['APPROVED', 'COMPLETED']) ? 'success' : ($goal->status === 'REJECTED' ? 'danger' : 'warning') }} p-2 fs-6">
                    Status: {{ $goal->status }}
                </span>
                <div class="mt-2">
                    <span class="h5 font-weight-bold text-primary">Progres Keseluruhan: {{ number_format($goal->overall_progress_percent, 1) }}%</span>
                </div>
                <div class="mt-2">
                    @if(in_array($goal->status, ['DRAFT', 'REJECTED']))
                        <button type="button" class="btn btn-success me-1" onclick="submitToApproval({{ $goal->id }})">
                            <i class="feather-send me-1"></i> Ajukan Approval
                        </button>
                    @endif
                    <a href="{{ url('hrd-performance-kpi-index') }}" class="btn btn-secondary">
                        <i class="feather-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

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
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openProgressModal({{ $item->id }}, '{{ addslashes($item->title) }}', {{ $item->actual_value }}, '{{ $item->kpi_unit }}')">
                                    <i class="feather-edit-3 me-1"></i> Check-in
                                </button>
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
                Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        })
        .catch(err => Swal.fire('Error', err.message, 'error'));
    }
</script>
@endsection

