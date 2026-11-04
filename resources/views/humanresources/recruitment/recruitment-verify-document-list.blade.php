@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            <i class="fa fa-file-circle-check me-2"></i>Antrean Verifikasi Dokumen
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                <li class="breadcrumb-item"><a href="{{ url('hrd-recruitment-management') }}" style="color: rgba(255, 255, 255, 0.8) !important;">Recruitment</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Verifikasi Dokumen</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ url('recruitment-new') }}" class="btn btn-sm text-white shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-user-plus me-1"></i> New Recruitment
        </a>
        <a href="{{ url('hrd-recruitment-management') }}" class="btn btn-sm text-white shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-gauge me-1"></i> Recruitment Dashboard
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-md-3">
        <a href="{{ route('hrd-recruitment-verify-document-list', ['status' => 'ALL', 'q' => $search ?? '', 'per_page' => $perPage]) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 h-100 {{ $statusFilter === 'ALL' ? 'border-start border-primary border-4' : '' }}" style="transition: all 0.2s;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar avatar-md bg-primary-transparent text-primary rounded-3 me-3">
                        <i class="fa fa-folder-open fs-20"></i>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Semua Menunggu</h6>
                        <h4 class="fw-bold mb-0 text-dark">{{ $counts['all'] }}</h4>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-md-3">
        <a href="{{ route('hrd-recruitment-verify-document-list', ['status' => 'UPLOADED', 'q' => $search ?? '', 'per_page' => $perPage]) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 h-100 {{ $statusFilter === 'UPLOADED' ? 'border-start border-warning border-4' : '' }}" style="transition: all 0.2s;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar avatar-md bg-warning-transparent text-warning rounded-3 me-3">
                        <i class="fa fa-cloud-arrow-up fs-20"></i>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Uploaded Dokumen</h6>
                        <h4 class="fw-bold mb-0 text-dark">{{ $counts['uploaded'] }}</h4>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-md-3">
        <a href="{{ route('hrd-recruitment-verify-document-list', ['status' => 'CREATED', 'q' => $search ?? '', 'per_page' => $perPage]) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 h-100 {{ $statusFilter === 'CREATED' ? 'border-start border-secondary border-4' : '' }}" style="transition: all 0.2s;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar avatar-md bg-secondary-transparent text-secondary rounded-3 me-3">
                        <i class="fa fa-user-clock fs-20"></i>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Baru / Created</h6>
                        <h4 class="fw-bold mb-0 text-dark">{{ $counts['created'] }}</h4>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-md-3">
        <a href="{{ route('hrd-recruitment-verify-document-list', ['status' => 'PENDING', 'q' => $search ?? '', 'per_page' => $perPage]) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 h-100 {{ $statusFilter === 'PENDING' ? 'border-start border-info border-4' : '' }}" style="transition: all 0.2s;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar avatar-md bg-info-transparent text-info rounded-3 me-3">
                        <i class="fa fa-clock fs-20"></i>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Pending</h6>
                        <h4 class="fw-bold mb-0 text-dark">{{ $counts['pending'] }}</h4>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Filter & Search Toolbar --}}
<div class="card shadow-sm border mb-3">
    <div class="card-body py-2 px-3">
        <form method="GET" action="{{ route('hrd-recruitment-verify-document-list') }}" class="row g-2 align-items-center" id="filterForm">
            <input type="hidden" name="status" value="{{ $statusFilter }}">

            <div class="col-md-5 col-sm-12">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="fa fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Cari nama kandidat, Task ID, posisi, email, atau HP..." value="{{ $search ?? '' }}">
                    @if(!empty($search))
                    <a href="{{ route('hrd-recruitment-verify-document-list', ['status' => $statusFilter, 'per_page' => $perPage]) }}" class="btn btn-outline-secondary" title="Hapus pencarian">
                        <i class="fa fa-xmark"></i>
                    </a>
                    @endif
                    <button type="submit" class="btn btn-primary text-white">Cari</button>
                </div>
            </div>

            <div class="col-md-7 col-sm-12 d-flex flex-wrap justify-content-md-end align-items-center gap-2">
                <div class="d-flex align-items-center">
                    <label for="per_page" class="small text-muted me-2 mb-0 text-nowrap">Tampilkan:</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm w-auto" onchange="document.getElementById('filterForm').submit();">
                        <option value="10" {{ (int) $perPage === 10 ? 'selected' : '' }}>10 baris</option>
                        <option value="15" {{ (int) $perPage === 15 ? 'selected' : '' }}>15 baris</option>
                        <option value="25" {{ (int) $perPage === 25 ? 'selected' : '' }}>25 baris</option>
                        <option value="50" {{ (int) $perPage === 50 ? 'selected' : '' }}>50 baris</option>
                        <option value="100" {{ (int) $perPage === 100 ? 'selected' : '' }}>100 baris</option>
                    </select>
                </div>

                @if(!empty($search) || $statusFilter !== 'ALL')
                <a href="{{ route('hrd-recruitment-verify-document-list') }}" class="btn btn-outline-danger btn-sm">
                    <i class="fa fa-rotate-left me-1"></i> Reset Filter
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Table Card --}}
<div class="card shadow-sm border mb-4">
    <div class="card-header bg-white py-2 px-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="fw-semibold text-dark fs-13">
            <i class="fa fa-list-check text-primary me-1"></i> Daftar Antrean Kandidat Siap Verifikasi
        </span>
        <div class="d-flex align-items-center gap-2">
            @if(!empty($search))
            <span class="badge bg-info-transparent text-info border border-info fw-normal">
                Pencarian: "<strong>{{ $search }}</strong>"
            </span>
            @endif
            <span class="badge bg-light text-muted border fw-normal">
                Status: <strong>{{ $statusFilter }}</strong>
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>Task ID / Tanggal</th>
                        <th>Nama Kandidat</th>
                        <th>Posisi / PTK</th>
                        <th>Pendidikan</th>
                        <th class="text-center">Status</th>
                        <th>Dokumen Terdeteksi</th>
                        <th class="text-center pe-3" width="160">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($candidates as $idx => $candidate)
                        @php
                            $doc = $documents->get($candidate->Task_ID);
                            $req = $requisitions->get($candidate->Requisition_ID);
                            
                            $statusBadgeClass = 'bg-secondary text-white';
                            if ($candidate->Status === 'UPLOADED') {
                                $statusBadgeClass = 'bg-warning text-dark';
                            } elseif ($candidate->Status === 'CREATED') {
                                $statusBadgeClass = 'bg-light text-dark border';
                            } elseif ($candidate->Status === 'PENDING') {
                                $statusBadgeClass = 'bg-info text-white';
                            }

                            // Cek dokumen pendukung
                            $docsFound = [];
                            if ($doc) {
                                if (!empty($doc->CV)) $docsFound[] = 'CV';
                                if (!empty($doc->KTP)) $docsFound[] = 'KTP';
                                if (!empty($doc->Foto)) $docsFound[] = 'Foto';
                                if (!empty($doc->Ijazah)) $docsFound[] = 'Ijazah';
                                if (!empty($doc->Sertifikat)) $docsFound[] = 'Sertifikat';
                                if (!empty($doc->NPWP)) $docsFound[] = 'NPWP';
                            }
                        @endphp
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $candidates->firstItem() + $idx }}</td>
                            <td>
                                <span class="fw-bold text-primary">{{ $candidate->Task_ID }}</span>
                                <div class="small text-muted">
                                    <i class="fa fa-calendar-day me-1"></i>{{ !empty($candidate->Created_Date) ? \Carbon\Carbon::parse($candidate->Created_Date)->format('d M Y H:i') : '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $candidate->Name }}</div>
                                <div class="small text-muted">{{ $candidate->Gender ?? '-' }} &bull; {{ $candidate->Birth_Date ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $req->Job_Position ?? $candidate->Position ?? '-' }}</div>
                                <div class="small text-muted">Req: {{ $candidate->Requisition_ID ?? '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $candidate->Last_Education ?? '-' }}</div>
                                <div class="small text-muted">{{ $candidate->Education_Major ?? '' }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $statusBadgeClass }} px-2 py-1 fs-12">
                                    {{ $candidate->Status }}
                                </span>
                            </td>
                            <td>
                                @if(count($docsFound) > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($docsFound as $dName)
                                            <span class="badge bg-success-transparent text-success border border-success px-2 py-1 fs-11">
                                                <i class="fa fa-check me-1"></i>{{ $dName }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small fst-italic">Belum ada file terdeteksi</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('hrd-recruitment-verify-document-page', $candidate->ID) }}" 
                                   class="btn btn-sm btn-success px-3 shadow-sm text-white">
                                    <i class="fa fa-circle-check me-1"></i> Verifikasi
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p class="mb-1 fw-bold text-dark">Tidak ada antrean dokumen untuk diverifikasi saat ini.</p>
                                    <p class="small text-muted">Kandidat baru yang diinput melalui menu New Recruitment akan otomatis muncul di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($candidates->hasPages())
            <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="small text-muted">
                    Menampilkan <strong>{{ $candidates->firstItem() }}</strong> sampai <strong>{{ $candidates->lastItem() }}</strong> dari <strong>{{ $candidates->total() }}</strong> kandidat ({{ $perPage }} per halaman)
                </div>
                <div>
                    {{ $candidates->appends(['status' => $statusFilter, 'q' => $search, 'per_page' => $perPage])->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

