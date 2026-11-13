@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<style>
    .exit-page-wrapper {
        margin-bottom: 2rem;
    }
    .exit-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 14px rgba(0, 0, 0, 0.05);
        background: #ffffff;
        overflow: hidden;
    }
    .exit-card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .exit-card-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .exit-btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.5rem 1.15rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.2s ease;
    }
    .exit-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    .table-exit th {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
    }
    .table-exit td {
        font-size: 0.88rem;
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    .table-exit tbody tr:hover {
        background-color: #f8fafc;
    }
    .badge-soft-warning {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .badge-soft-success {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .badge-soft-danger {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .badge-soft-primary {
        background-color: #e0e7ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
    }
    .badge-soft-info {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
</style>

{{-- Breadcrumb Header persis style SPPD --}}
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
            Exit Clearance & Resignation
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="#">Kepegawaian</a></li>
                <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">Exit Clearance</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('hrd-exit-clearance-resignation-create') }}" class="btn btn-primary exit-btn shadow-sm">
            <i class="fa fa-plus-circle"></i> Ajukan Resign Baru
        </a>
    </div>
</div>

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

<div class="exit-page-wrapper">
    <div class="card exit-card">
        {{-- Card Header persis style SPPD --}}
        <div class="exit-card-header">
            <div>
                <h3 class="exit-card-title">
                    <i class="fa fa-list text-primary"></i> Daftar Pengajuan Resign & Clearance
                </h3>
                <small class="text-muted">
                    <i class="fa fa-shield me-1 text-secondary"></i>
                    @if($isAdmin)
                        <span class="text-success fw-bold">Administrator View (Seluruh Divisi)</span>
                    @elseif($isHcga)
                        <span class="text-info fw-bold">HCGA View (Departemen Terkait & Clearance Aktif)</span>
                    @elseif($isApprover || $isAtasan)
                        <span class="text-warning fw-bold">Approver / Manager View (Departemen / Tim / Tugas Approval)</span>
                    @else
                        <span>Employee View (Hanya Pengajuan Sendiri)</span>
                    @endif
                </small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('hrd-exit-clearance-index') }}" class="btn btn-outline-secondary exit-btn">
                    <i class="fa fa-sync-alt"></i> Refresh
                </a>
                @if($isAdmin || $isApprover)
                <a href="{{ url('hrd-approval-inbox') }}" class="btn btn-warning text-dark exit-btn shadow-sm">
                    <i class="fa fa-check-double"></i> Approval Inbox
                </a>
                @endif
            </div>
        </div>

        {{-- Filter & Search Form --}}
        <div class="card-body p-3 border-bottom bg-light">
            <form action="{{ route('hrd-exit-clearance-index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari No. Trx, Nama Karyawan, Departemen..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit();">
                        <option value="">Semua Status Resign</option>
                        <option value="PENDING_APPROVAL" {{ request('status') === 'PENDING_APPROVAL' ? 'selected' : '' }}>Menunggu Persetujuan Atasan</option>
                        <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Resign Disetujui (Clearance Dibuka)</option>
                        <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Resign Ditolak</option>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-sm btn-primary px-3">
                        <i class="fa fa-filter me-1"></i> Terapkan Filter
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('hrd-exit-clearance-index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table Container --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-exit mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th>No. Pengajuan</th>
                            <th>Karyawan</th>
                            <th>Jabatan / Dept</th>
                            <th>Tgl Resign / Terakhir</th>
                            <th>Status Resign</th>
                            <th>Tahapan Clearance</th>
                            <th style="width: 160px;" class="text-center">Aksi Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resignations as $idx => $item)
                            <tr>
                                <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                <td>
                                    <span class="fw-bold text-primary">{{ $item->No_Trx }}</span><br>
                                    <small class="text-muted"><i class="fa fa-calendar me-1"></i>{{ date('d M Y', strtotime($item->created_at)) }}</small>
                                </td>
                                <td>
                                    <strong class="text-dark">{{ $item->Nama }}</strong><br>
                                    <small class="text-muted"><i class="fa fa-envelope-o me-1"></i>{{ $item->user_email ?? ($item->user_username ?? '-') }}</small>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $item->Jabatan ?? '-' }}</span><br>
                                    <span class="badge bg-light text-secondary border">{{ $item->Departemen ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="small mb-1">Resign: <strong>{{ date('d/m/Y', strtotime($item->Resign_Date)) }}</strong></div>
                                    <div class="small text-danger">Last Day: <strong>{{ date('d/m/Y', strtotime($item->Last_Working_Date)) }}</strong></div>
                                </td>
                                <td>
                                    @if($item->Status === 'PENDING_APPROVAL')
                                        <span class="badge badge-soft-warning px-2 py-1"><i class="fa fa-clock-o me-1"></i> Menunggu Atasan</span>
                                    @elseif($item->Status === 'APPROVED')
                                        <span class="badge badge-soft-success px-2 py-1"><i class="fa fa-check me-1"></i> Disetujui</span>
                                    @elseif($item->Status === 'REJECTED')
                                        <span class="badge badge-soft-danger px-2 py-1"><i class="fa fa-times me-1"></i> Ditolak</span>
                                    @else
                                        <span class="badge bg-secondary text-white px-2 py-1">{{ $item->Status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if(empty($item->exit_clearance_id))
                                        <span class="badge bg-light text-muted border">Belum Terbuka</span>
                                    @else
                                        @if($item->exit_clearance_status === 'DRAFT')
                                            <span class="badge badge-soft-info px-2 py-1"><i class="fa fa-pencil me-1"></i> Pengisian Karyawan</span>
                                        @elseif($item->exit_clearance_status === 'PENDING_INTERVIEW')
                                            <span class="badge badge-soft-warning px-2 py-1"><i class="fa fa-comments me-1"></i> Exit Interview HCGA</span>
                                        @elseif($item->exit_clearance_status === 'PENDING_MANAGER_APPROVAL')
                                            <span class="badge badge-soft-info px-2 py-1"><i class="fa fa-user-check me-1"></i> Approval Atasan</span>
                                        @elseif($item->exit_clearance_status === 'PENDING_HCGA_APPROVAL')
                                            <span class="badge badge-soft-primary px-2 py-1"><i class="fa fa-check-square me-1"></i> Verifikasi HCGA</span>
                                        @elseif($item->exit_clearance_status === 'COMPLETED')
                                            <span class="badge badge-soft-success px-2 py-1"><i class="fa fa-check-circle me-1"></i> Selesai (Completed)</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-2 py-1">{{ $item->exit_clearance_status ?: 'Menunggu Proses' }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        {{-- Aksi Approval Resign oleh Atasan / Admin --}}
                                        @if($item->Status === 'PENDING_APPROVAL' && ($isAdmin || $isApprover))
                                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalApproveResign-{{ $item->id }}" title="Setujui Resign">
                                                <i class="fa fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalRejectResign-{{ $item->id }}" title="Tolak Resign">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        @endif

                                        {{-- Karyawan Mengisi Exit Clearance (Hanya Pemohon Sendiri) --}}
                                        @if($item->Status === 'APPROVED' && !empty($item->exit_clearance_id) && $item->exit_clearance_status === 'DRAFT')
                                            @if($item->User_ID == auth()->id())
                                                <a href="{{ route('hrd-exit-clearance-form', $item->exit_clearance_id) }}" class="btn btn-info text-white" title="Isi Formulir Clearance">
                                                    <i class="fa fa-pencil me-1"></i> Isi Form
                                                </a>
                                            @else
                                                <span class="badge bg-light text-muted border py-1 px-2" title="Menunggu pengisian formulir oleh karyawan yang bersangkutan">
                                                    <i class="fa fa-clock-o me-1"></i> Menunggu Karyawan
                                                </span>
                                            @endif
                                        @endif

                                        {{-- Approval Atasan Departemen untuk Exit Clearance (Tahap 2) --}}
                                        @if(!empty($item->can_manager_approve))
                                            <button type="button" class="btn btn-success text-white" data-bs-toggle="modal" data-bs-target="#modalApproveManager-{{ $item->exit_clearance_id }}" title="Setujui Exit Clearance (Atasan)">
                                                <i class="fa fa-check me-1"></i> Approve
                                            </button>
                                        @endif

                                        {{-- Review Detail Wawancara & Unduh PDF --}}
                                        @if(!empty($item->exit_clearance_id) && $item->exit_clearance_status !== 'DRAFT')
                                            <a href="{{ route('hrd-exit-clearance-view', $item->exit_clearance_id) }}" class="btn btn-outline-primary" title="Review Clearance & Cetak">
                                                <i class="fa fa-eye me-1"></i> Detail
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal Approval Exit Clearance oleh Atasan Departemen --}}
                            @if(!empty($item->can_manager_approve))
                                <div class="modal fade" id="modalApproveManager-{{ $item->exit_clearance_id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="{{ route('hrd-exit-clearance-manager-approve', $item->exit_clearance_id) }}" method="POST">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title font-weight-bold"><i class="fa fa-check-circle text-success me-2"></i> Approve Exit Clearance (Atasan)</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <p>Apakah Anda yakin menyetujui Exit Clearance karyawan <strong>{{ $item->Nama }}</strong> ({{ $item->Departemen }})?</p>
                                                    <p class="text-muted small">Hasil Exit Interview telah dicatat oleh tim HCGA. Setelah Anda setujui, berkas akan diteruskan kembali ke HCGA untuk verifikasi final.</p>
                                                    <div class="form-group mb-2">
                                                        <label class="form-label fw-bold">Catatan Manager / Dept. Head (Opsional)</label>
                                                        <textarea name="manager_notes" class="form-control" rows="2" placeholder="Catatan serah terima pekerjaan atau tindak lanjut..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success px-3">Ya, Setujui</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            {{-- Modal Setujui Resign --}}
                            @if($item->Status === 'PENDING_APPROVAL' && ($isAdmin || $isApprover))
                                <div class="modal fade" id="modalApproveResign-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="{{ route('hrd-exit-clearance-resignation-approve', $item->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title font-weight-bold"><i class="fa fa-check-circle text-success me-2"></i> Setujui Pengajuan Resign</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <p>Apakah Anda yakin menyetujui pengajuan resign karyawan <strong>{{ $item->Nama }}</strong> ({{ $item->Departemen }})?</p>
                                                    <p class="text-muted small">Setelah disetujui, workflow persetujuan akan diproses dan sistem otomatis membukakan formulir Exit Clearance untuk diisi oleh karyawan.</p>
                                                    <div class="form-group mb-2">
                                                        <label class="form-label fw-bold">Catatan Approval (Opsional)</label>
                                                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan untuk pemohon..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success px-3">Ya, Setujui Resign</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                {{-- Modal Tolak Resign --}}
                                <div class="modal fade" id="modalRejectResign-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="{{ route('hrd-exit-clearance-resignation-reject', $item->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title font-weight-bold text-danger"><i class="fa fa-times-circle text-danger me-2"></i> Tolak Pengajuan Resign</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <p>Berikan alasan penolakan pengajuan resign untuk karyawan <strong>{{ $item->Nama }}</strong>:</p>
                                                    <div class="form-group mb-2">
                                                        <label class="form-label fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                                                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Tulis alasan penolakan secara jelas..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger px-3">Tolak Pengajuan</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa fa-folder-open-o fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                    <span class="fw-bold fs-6">Tidak ada pengajuan resign atau exit clearance yang perlu Anda proses saat ini.</span><br>
                                    <small class="text-muted">Hanya pengajuan dari bawahan langsung di departemen Anda atau permohonan pribadi Anda yang akan ditampilkan.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
