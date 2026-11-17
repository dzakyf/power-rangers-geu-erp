@extends('layout.mainlayout-hrd')

@section('title', 'Surat Peringatan & Sanksi Disiplin (SP)')

@section('content-wrapper')
@csrf
<style>
    .sanction-page-wrapper {
        margin-bottom: 2rem;
    }
    .sanction-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 14px rgba(0, 0, 0, 0.05);
        background: #ffffff;
        overflow: hidden;
    }
    .sanction-card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .sanction-card-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .sanction-btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.5rem 1.15rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.2s ease;
    }
    .sanction-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    .badge-soft-warning { background-color: #fef3c7; color: #92400e; }
    .badge-soft-danger { background-color: #fee2e2; color: #991b1b; }
    .badge-soft-primary { background-color: #e0e7ff; color: #3730a3; }
    .badge-soft-success { background-color: #d1fae5; color: #065f46; }
    .badge-soft-secondary { background-color: #f1f5f9; color: #475569; }
    .badge-soft-dark { background-color: #e2e8f0; color: #0f172a; }
</style>

{{-- Breadcrumb Header Persis Style SPPD --}}
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
            Surat Peringatan & Sanksi Disiplin Karyawan
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Human Capital</a></li>
                <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">Surat Peringatan (FR/HCGA/10-00)</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if($isAdmin || $isHcga || $isAtasan)
            <a href="{{ route('hrd-disciplinary-sanction-create') }}" class="btn btn-primary sanction-btn shadow-sm">
                <i class="fa fa-plus-circle"></i> Buat Laporan Pelanggaran
            </a>
        @endif
    </div>
</div>

<div class="sanction-page-wrapper">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-check-circle fa-lg me-2 text-success"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-exclamation-circle fa-lg me-2 text-danger"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter & Search Card --}}
    <div class="card sanction-card mb-4">
        <div class="card-body p-3">
            <form action="{{ route('hrd-disciplinary-sanction-index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari No. SP, Nama Karyawan, NIK, atau Departemen..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="PENDING_HCGA" {{ request('status') == 'PENDING_HCGA' ? 'selected' : '' }}>Verifikasi HCGA</option>
                        <option value="WAITING_EMPLOYEE" {{ request('status') == 'WAITING_EMPLOYEE' ? 'selected' : '' }}>Menunggu Komitmen Karyawan</option>
                        <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Aktif (Berlaku)</option>
                        <option value="EXPIRED" {{ request('status') == 'EXPIRED' ? 'selected' : '' }}>Selesai / Kadaluarsa</option>
                        <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary sanction-btn">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('hrd-disciplinary-sanction-index') }}" class="btn btn-outline-secondary sanction-btn">
                            <i class="fa fa-refresh"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Daftar SP --}}
    <div class="card sanction-card">
        <div class="sanction-card-header">
            <div>
                <h3 class="sanction-card-title">
                    <i class="fa fa-gavel text-primary"></i> Data Surat Peringatan & Sanksi Disiplin
                </h3>
                <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Data sanksi disiplin resmi (Doc ID: FR/HCGA/10-00) dengan masa berlaku dan riwayat komitmen karyawan.</small>
            </div>
            <div class="text-muted small">
                Menampilkan <strong>{{ $sanctions->total() }}</strong> catatan
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 5%;">No</th>
                            <th style="width: 15%;">No. SP / Tanggal</th>
                            <th style="width: 20%;">Karyawan</th>
                            <th style="width: 15%;">Tingkat Sanksi</th>
                            <th style="width: 15%;">Masa Berlaku</th>
                            <th style="width: 15%;">Pelapor</th>
                            <th style="width: 10%;">Status</th>
                            <th class="text-center pe-3" style="width: 5%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sanctions as $idx => $item)
                            <tr>
                                <td class="ps-3 text-muted">{{ $sanctions->firstItem() + $idx }}</td>
                                <td>
                                    <strong class="text-dark">{{ $item->No_Trx }}</strong>
                                    <small class="d-block text-muted">{{ date('d M Y', strtotime($item->created_at)) }}</small>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $item->Nama }}</span>
                                    <small class="d-block text-muted">
                                        {{ $item->Jabatan ?? '-' }} | {{ $item->Departemen ?? '-' }} (NIK: {{ $item->NIK ?? '-' }})
                                    </small>
                                </td>
                                <td>
                                    @if($item->Sanction_Level === 'LISAN')
                                        <span class="badge badge-soft-secondary px-2 py-1">Peringatan Lisan</span>
                                    @elseif($item->Sanction_Level === 'SP1')
                                        <span class="badge badge-soft-warning px-2 py-1"><i class="fa fa-exclamation-circle me-1"></i> SP 1</span>
                                    @elseif($item->Sanction_Level === 'SP2')
                                        <span class="badge badge-soft-primary px-2 py-1"><i class="fa fa-exclamation-triangle me-1"></i> SP 2</span>
                                    @elseif($item->Sanction_Level === 'SP3')
                                        <span class="badge badge-soft-danger px-2 py-1"><i class="fa fa-ban me-1"></i> SP 3 (Terakhir)</span>
                                    @else
                                        <span class="badge badge-soft-dark px-2 py-1">{{ $item->Sanction_Level }}</span>
                                    @endif
                                    <small class="d-block text-muted mt-1">Sifat: <strong>{{ $item->Violation_Severity }}</strong></small>
                                </td>
                                <td>
                                    <div class="small">
                                        <span class="text-muted">Mulai:</span> {{ $item->Valid_From ? date('d M Y', strtotime($item->Valid_From)) : '-' }}<br>
                                        <span class="text-muted">Hingga:</span> <strong>{{ $item->Valid_Until ? date('d M Y', strtotime($item->Valid_Until)) : '-' }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $item->Reporter_Nama }}</span>
                                    <small class="d-block text-muted">{{ $item->Reporter_Jabatan ?? 'Atasan' }}</small>
                                </td>
                                <td>
                                    @if($item->Status === 'PENDING_HCGA')
                                        <span class="badge badge-soft-warning px-2 py-1"><i class="fa fa-hourglass-half me-1"></i> Verifikasi HCGA</span>
                                    @elseif($item->Status === 'WAITING_EMPLOYEE')
                                        <span class="badge badge-soft-primary px-2 py-1"><i class="fa fa-pencil me-1"></i> Menunggu TTD Karyawan</span>
                                    @elseif($item->Status === 'ACTIVE')
                                        <span class="badge badge-soft-success px-2 py-1"><i class="fa fa-check-circle me-1"></i> Aktif Berlaku</span>
                                    @elseif($item->Status === 'EXPIRED')
                                        <span class="badge badge-soft-secondary px-2 py-1"><i class="fa fa-clock-o me-1"></i> Selesai (Expired)</span>
                                    @elseif($item->Status === 'REJECTED')
                                        <span class="badge badge-soft-danger px-2 py-1"><i class="fa fa-times-circle me-1"></i> Ditolak</span>
                                    @else
                                        <span class="badge badge-soft-secondary px-2 py-1">{{ $item->Status }}</span>
                                    @endif
                                </td>
                                <td class="text-center pe-3">
                                    <div class="btn-group btn-group-sm">
                                        {{-- Verifikasi oleh HCGA --}}
                                        @if($item->Status === 'PENDING_HCGA' && ($isAdmin || $isHcga))
                                            <a href="{{ route('hrd-disciplinary-sanction-review', $item->id) }}" class="btn btn-warning text-dark" title="Review & Verifikasi HCGA">
                                                <i class="fa fa-check-square-o"></i> Review
                                            </a>
                                        @endif

                                        {{-- Detail Riwayat & TTD Komitmen --}}
                                        <a href="{{ route('hrd-disciplinary-sanction-detail', $item->id) }}" class="btn btn-outline-primary" title="Lihat Detail & Dokumen">
                                            <i class="fa fa-eye"></i> Detail
                                        </a>

                                        {{-- Unduh PDF jika sudah aktif / verifikasi --}}
                                        @if(in_array($item->Status, ['WAITING_EMPLOYEE', 'ACTIVE', 'EXPIRED']))
                                            <a href="{{ route('hrd-disciplinary-sanction-download-pdf', $item->id) }}" target="_blank" class="btn btn-outline-danger" title="Unduh PDF Resmi">
                                                <i class="fa fa-file-pdf-o"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa fa-folder-open-o fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                    <span class="fw-bold fs-6">Belum ada data Surat Peringatan atau Sanksi Disiplin yang tercatat.</span><br>
                                    <small class="text-muted">Laporan pelanggaran yang diajukan oleh atasan langsung akan ditampilkan di sini.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sanctions->hasPages())
                <div class="p-3 border-top">
                    {{ $sanctions->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

