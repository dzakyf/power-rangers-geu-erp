@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header mb-3">
    <h1>Performance Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-performance-kpi-index') }}">KPI & OKR</a></li>
        <li class="breadcrumb-item active"><span class="active">Master Periode KPI</span></li>
    </ol>
</div>

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
</script>
@endsection

