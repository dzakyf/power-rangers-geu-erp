@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
@include('humanresources.performance.partials.page-header', [
    'title'       => '1. Master Periode KPI & Dept PIC',
    'parentLabel' => 'Performance Management',
    'parentUrl'   => url('hrd-performance-period-index'),
    'current'     => 'Periode & Dept PIC',
    'icon'        => 'fa-calendar-check',
    'badgeText'   => 'Step 1: Setup Periode & PIC',
])

@include('humanresources.performance.partials.workflow-guide', ['activeStep' => 1])

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tambah Periode Baru</h5>
            </div>
            <div class="card-body">
                <form action="{{ url('hrd-performance-period-store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Kode Periode <span class="text-danger">*</span></label>
                        <input type="text" name="period_code" class="form-control text-uppercase" placeholder="Contoh: PM-2026, Q1-2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Nama Periode <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Performance Cycle 2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Tipe Siklus</label>
                        <select name="period_type" class="form-select form-control" required>
                            <option value="ANNUAL">Tahunan (Annual)</option>
                            <option value="SEMESTER">Semester</option>
                            <option value="QUARTERLY">Kuartal (Quarterly)</option>
                            <option value="MONTHLY">Bulanan (Monthly)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Keterangan periode..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="feather-plus me-1"></i> Simpan Periode
                    </button>
                </form>
            </div>
        </div>

        @if(isset($isHcgaAdmin) && $isHcgaAdmin)
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Pengaturan Kebijakan (HCGA)</h5>
            </div>
            <div class="card-body">
                <form action="{{ url('hrd-performance-settings-update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Penyusunan KPI Mandiri Karyawan</label>
                        <select name="allow_employee_self_planning" class="form-select form-control">
                            <option value="1" {{ ($allowSelfPlanning ?? '1') === '1' ? 'selected' : '' }}>Diizinkan (Karyawan &amp; Atasan/HCGA)</option>
                            <option value="0" {{ ($allowSelfPlanning ?? '1') === '0' ? 'selected' : '' }}>Dibatasi (Hanya Atasan &amp; HCGA)</option>
                        </select>
                        <small class="text-muted">Jika dibatasi, karyawan biasa hanya dapat melihat KPI yang disusun oleh atasan/HCGA.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Ambang Batas Default PIP (&lt; Skor)</label>
                        <input type="number" step="0.01" min="0" max="100" name="default_pip_threshold_score" class="form-control" value="{{ $defaultPipThreshold ?? '60' }}" required>
                        <small class="text-muted">Skor akhir review di bawah nilai ini otomatis memicu draft PIP (default: 60).</small>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="feather-save me-1"></i> Simpan Kebijakan
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Daftar Periode Penilaian Kinerja</h5>
                <a href="{{ url('hrd-performance-kpi-index') }}" class="btn btn-sm btn-secondary">
                    <i class="feather-arrow-left me-1"></i> Ke Daftar KPI
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Periode</th>
                                <th>Tipe</th>
                                <th>Durasi</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Ubah Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($periods as $p)
                                <tr>
                                    <td><strong>{{ $p->period_code }}</strong></td>
                                    <td>{{ $p->name }}</td>
                                    <td><span class="badge bg-info text-white">{{ $p->period_type }}</span></td>
                                    <td class="small">{{ date('d/m/Y', strtotime($p->start_date)) }} s/d {{ date('d/m/Y', strtotime($p->end_date)) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $p->status === 'ACTIVE' ? 'success' : ($p->status === 'EVALUATION' ? 'warning' : 'secondary') }}">
                                            {{ $p->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <select class="form-select form-control form-control-sm d-inline-block" style="width: auto;" onchange="changeStatus({{ $p->id }}, this.value)">
                                            <option value="DRAFT" {{ $p->status === 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                                            <option value="ACTIVE" {{ $p->status === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                                            <option value="EVALUATION" {{ $p->status === 'EVALUATION' ? 'selected' : '' }}>EVALUATION</option>
                                            <option value="CLOSED" {{ $p->status === 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
                                        </select>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada periode yang dibuat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($isHcgaAdmin) && $isHcgaAdmin)
<div class="row mt-3">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Pengaturan PIC Departemen (1 PIC / Dept)</h5>
            </div>
            <div class="card-body">
                <form action="{{ url('hrd-performance-dept-pic-store') }}" method="POST" class="mb-3">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label font-weight-bold">Departemen <span class="text-danger">*</span></label>
                        <select name="department" class="form-select form-control" required>
                            <option value="">-- Pilih Departemen --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label font-weight-bold">PIC Departemen <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select form-control" required>
                            <option value="">-- Pilih Karyawan PIC --</option>
                            @foreach($activeUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->Name }} ({{ $u->Department ?? '-' }} - {{ $u->Position ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Penunjukan</label>
                        <input type="text" name="notes" class="form-control" placeholder="Opsional: alasan penunjukan / perubahan PIC">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="feather-user-check me-1"></i> Simpan / Update PIC Departemen
                    </button>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Departemen</th>
                                <th>PIC Aktif</th>
                                <th class="text-center" style="width: 70px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deptPics as $dp)
                                <tr>
                                    <td><strong>{{ $dp->department }}</strong></td>
                                    <td>
                                        {{ $dp->user->Name ?? '-' }}
                                        <div class="small text-muted">{{ $dp->user->Position ?? '' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteDeptPic({{ $dp->id }})">
                                            <i class="feather-trash-2"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada PIC departemen yang ditetapkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Log History PIC Departemen</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 430px; overflow-y: auto;">
                    <table class="table table-bordered table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu</th>
                                <th>Departemen</th>
                                <th>Aksi</th>
                                <th>PIC Sebelumnya</th>
                                <th>PIC Baru</th>
                                <th>Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deptPicLogs as $log)
                                <tr>
                                    <td class="small">{{ $log->created_at ? $log->created_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td><strong>{{ $log->department }}</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $log->action === 'ASSIGNED' ? 'success' : ($log->action === 'CHANGED' ? 'warning' : 'danger') }} text-white">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="small">{{ $log->previousUser->Name ?? '-' }}</td>
                                    <td class="small">{{ $log->newUser->Name ?? '-' }}</td>
                                    <td class="small">
                                        {{ $log->actor->Name ?? 'System' }}
                                        @if($log->notes)
                                            <div class="text-muted">{{ $log->notes }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada riwayat perubahan PIC departemen.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script>
    function changeStatus(periodId, newStatus) {
        fetch("{{ url('hrd-performance-period-update-status') }}/" + periodId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: res.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => location.reload());
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        })
        .catch(err => Swal.fire('Error', err.message, 'error'));
    }

    function deleteDeptPic(id) {
        Swal.fire({
            title: 'Hapus PIC Departemen?',
            text: 'Penghapusan PIC akan dicatat pada Log History.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("{{ url('hrd-performance-dept-pic-delete') }}/" + id, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
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
</script>
@endsection

