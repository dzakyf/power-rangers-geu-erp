@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
@include('humanresources.performance.partials.page-header', [
    'title'       => '4. Evaluasi Probation & Kontrak',
    'parentLabel' => 'Performance Management',
    'parentUrl'   => url('hrd-performance-probation-index'),
    'current'     => 'Evaluasi Probation',
    'icon'        => 'fa-user-clock',
    'badgeText'   => 'Step 4: Monitoring Probation',
])

@include('humanresources.performance.partials.workflow-guide', ['activeStep' => 4])

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-1">Monitoring Karyawan Masa Percobaan (Probation)</h5>
            <div class="text-muted small">
                KPI Probation wajib melalui 2 tahap approval (Atasan Langsung + Manager HCGA). Sistem otomatis membuat lembar evaluasi pada H-14 sebelum berakhirnya masa probation.
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ url('hrd-performance-kpi-index') }}" class="btn btn-sm btn-outline-primary">
                <i class="feather-target me-1"></i> Kelola KPI
            </a>
            <a href="{{ url('hrd-performance-competency-index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="feather-sliders me-1"></i> Master Soft Skill
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Daftar Karyawan Probation ({{ $probationList->count() }})</h5>
        <form method="GET" action="{{ url('hrd-performance-probation-index') }}" class="d-flex gap-2">
            <select name="department" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Semua Departemen --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Karyawan</th>
                        <th>Dept / Posisi</th>
                        <th>Tgl Masuk (Date_in)</th>
                        <th>Akhir Probation</th>
                        <th class="text-center">Sisa Hari</th>
                        <th>Status KPI Probation</th>
                        <th>Status Evaluasi</th>
                        <th class="text-center" style="width: 240px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($probationList as $row)
                        @php
                            $goal = $row->probation_goal;
                            $rev = $row->probation_review;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $row->Name }}</strong>
                                <div class="text-muted small">{{ $row->NIK }}</div>
                            </td>
                            <td>
                                <div>{{ $row->Department }}</div>
                                <small class="text-muted">{{ $row->Position }}</small>
                            </td>
                            <td>{{ $row->Date_in && $row->Date_in !== '0000-00-00' ? \Carbon\Carbon::parse($row->Date_in)->format('d M Y') : '-' }}</td>
                            <td>{{ $row->probation_end_date && $row->probation_end_date !== '0000-00-00' ? \Carbon\Carbon::parse($row->probation_end_date)->format('d M Y') : '-' }}</td>
                            <td class="text-center">
                                @if($row->days_left === null)
                                    <span class="badge bg-secondary">Belum Diatur</span>
                                @elseif($row->days_left < 0)
                                    <span class="badge bg-danger">Lewat {{ abs($row->days_left) }} hr</span>
                                @elseif($row->days_left <= 14)
                                    <span class="badge bg-warning text-dark">H-{{ $row->days_left }}</span>
                                @else
                                    <span class="badge bg-info text-white">{{ $row->days_left }} hr</span>
                                @endif
                            </td>
                            <td>
                                @if($goal)
                                    <a href="{{ url('hrd-performance-kpi-detail', $goal->id) }}" class="fw-bold">{{ $goal->goal_no }}</a>
                                    <div>
                                        <span class="badge bg-{{ $goal->status === 'APPROVED' ? 'success' : ($goal->status === 'PENDING_APPROVAL' ? 'warning text-dark' : 'secondary') }}">
                                            {{ $goal->status }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted small">Belum ada KPI Probation</span>
                                @endif
                            </td>
                            <td>
                                @if($rev)
                                    <a href="{{ url('hrd-performance-review-detail', $rev->id) }}" class="fw-bold">{{ $rev->review_no }}</a>
                                    <div>
                                        <span class="badge bg-{{ $rev->status === 'FINALIZED' ? 'success' : 'primary' }}">{{ $rev->status }}</span>
                                        @if($rev->final_score !== null)
                                            <small class="fw-bold ms-1">{{ number_format($rev->final_score, 1) }}%</small>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small">Menunggu H-14 / Manual</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($canSetupKpi && $row->user_id && !$goal)
                                    <a href="{{ url('hrd-performance-kpi-new') }}?goal_category=PROBATION&user_id={{ $row->user_id }}" class="btn btn-xs btn-outline-primary mb-1">
                                        + Setup KPI
                                    </a>
                                @endif
                                @if($rev)
                                    <a href="{{ url('hrd-performance-review-detail', $rev->id) }}" class="btn btn-xs btn-primary mb-1">
                                        Buka Evaluasi
                                    </a>
                                    <a href="{{ url('hrd-performance-review-pdf', $rev->id) }}" target="_blank" class="btn btn-xs btn-outline-danger mb-1">
                                        PDF
                                    </a>
                                @elseif($canSetupKpi && $row->user_id)
                                    <form action="{{ url('hrd-performance-probation-initiate', $row->user_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Buat lembar evaluasi Probation sekarang?');">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-success mb-1">Buat Review</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Tidak ada data karyawan masa probation.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
