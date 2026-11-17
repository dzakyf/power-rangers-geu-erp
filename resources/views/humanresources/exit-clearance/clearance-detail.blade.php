@extends('layout.mainlayout-hrd')

@section('title', 'Detail Exit Clearance & Interview')

@section('content-wrapper')
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
</style>

{{-- Breadcrumb Header persis style Index SPPD --}}
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
            Review Exit Clearance & Sesi Exit Interview
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hrd-exit-clearance-index') }}">Exit Clearance</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail & Verifikasi ({{ $clearance->No_Trx }})</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('hrd-exit-clearance-download-pdf', $clearance->id) }}" class="btn btn-outline-danger exit-btn">
            <i class="fa fa-file-pdf-o"></i> Unduh / Cetak PDF
        </a>
        <a href="{{ route('hrd-exit-clearance-index') }}" class="btn btn-light exit-btn border">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="exit-page-wrapper">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Ringkasan Status Clearance --}}
    <div class="exit-card mb-4">
        <div class="exit-card-header">
            <div class="exit-card-title">
                <i class="fa fa-info-circle text-primary"></i>
                <span>Informasi Status & Karyawan</span>
            </div>
            <div class="text-muted small">
                No. Dokumen: <strong class="text-primary">FR/HCGA/07-00</strong>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row text-center gy-3">
                <div class="col-md-3 border-end">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Karyawan</small>
                    <span class="fw-bold text-dark fs-6">{{ $clearance->Nama }}</span>
                    <small class="text-muted d-block">{{ $clearance->NIK ?? '-' }}</small>
                </div>
                <div class="col-md-3 border-end">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Jabatan / Departemen</small>
                    <span class="fw-bold text-dark fs-6">{{ $clearance->Jabatan }}</span>
                    <small class="text-muted d-block">{{ $clearance->Departemen }}</small>
                </div>
                <div class="col-md-3 border-end">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Tanggal Efektif Keluar</small>
                    <span class="fw-bold text-dark fs-6">{{ $clearance->Exit_Date ? date('d M Y', strtotime($clearance->Exit_Date)) : '-' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Status Clearance</small>
                    @if($clearance->Status === 'PENDING_INTERVIEW')
                        <span class="badge bg-warning text-dark px-2 py-1"><i class="fa fa-clock-o me-1"></i> Exit Interview HCGA</span>
                    @elseif($clearance->Status === 'PENDING_MANAGER_APPROVAL')
                        <span class="badge bg-info text-white px-2 py-1"><i class="fa fa-user-check me-1"></i> Approval Atasan</span>
                    @elseif($clearance->Status === 'PENDING_HCGA_APPROVAL')
                        <span class="badge bg-primary text-white px-2 py-1"><i class="fa fa-hourglass-half me-1"></i> Verifikasi HCGA</span>
                    @elseif($clearance->Status === 'COMPLETED')
                        <span class="badge bg-success text-white px-2 py-1"><i class="fa fa-check-circle me-1"></i> Selesai (Completed)</span>
                    @else
                        <span class="badge bg-secondary text-white px-2 py-1">{{ $clearance->Status }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tampilan Hasil Wawancara Exit Interview & Verifikasi (Dapat Dilihat Semua Pihak yang Berwenang) --}}
    @if(!empty($clearance->exit_interview_notes) || !empty($clearance->manager_notes) || !empty($clearance->hcga_notes))
        <div class="exit-card mb-4" style="border: 1px solid #cbd5e1 !important;">
            <div class="exit-card-header bg-light">
                <div class="exit-card-title text-dark">
                    <i class="fa fa-file-text-o text-primary"></i>
                    <span>Hasil Sesi Wawancara Exit Interview & Catatan Verifikasi</span>
                </div>
                @php
                    $interviewTimestamp = $clearance->Hcga_Interview_At ?? ($clearance->Manager_Interview_At ?? null);
                @endphp
                @if(!empty($interviewTimestamp))
                    <span class="badge bg-light text-secondary border">
                        <i class="fa fa-calendar me-1"></i> Selesai Diwawancarai: {{ date('d M Y H:i', strtotime($interviewTimestamp)) }}
                    </span>
                @endif
            </div>
            <div class="card-body p-4">
                @if(!empty($clearance->exit_interview_notes))
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-primary mb-0"><i class="fa fa-comments-o me-1"></i> Catatan Hasil Wawancara Exit Interview (HCGA):</h6>
                            @if(!empty($interviewer))
                                <small class="text-muted"><i class="fa fa-user me-1"></i> Pewawancara: <strong>{{ $interviewer->Name ?? ($interviewer->name ?? 'Tim HCGA') }}</strong></small>
                            @endif
                        </div>
                        <div class="p-3 bg-light rounded border text-dark" style="white-space: pre-line;">{{ $clearance->exit_interview_notes }}</div>
                    </div>
                @endif

                @if(!empty($clearance->manager_notes))
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0"><i class="fa fa-user-circle me-1"></i> Catatan Approval Manager / Dept. Head:</h6>
                            @if(!empty($managerApprover))
                                <small class="text-muted"><i class="fa fa-user me-1"></i> Atasan: <strong>{{ $managerApprover->Name ?? ($managerApprover->name ?? 'Atasan Departemen') }}</strong></small>
                            @endif
                        </div>
                        <div class="p-3 bg-light rounded border text-secondary" style="white-space: pre-line;">{{ $clearance->manager_notes }}</div>
                    </div>
                @endif

                @if(!empty($clearance->hcga_notes))
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-success mb-0"><i class="fa fa-check-square me-1"></i> Catatan Verifikasi HCGA:</h6>
                            @if(!empty($hcgaVerifier))
                                <small class="text-muted"><i class="fa fa-user-shield me-1"></i> Verifikator: <strong>{{ $hcgaVerifier->Name ?? ($hcgaVerifier->name ?? 'Tim HCGA') }}</strong></small>
                            @endif
                        </div>
                        <div class="p-3 bg-light rounded border text-dark" style="white-space: pre-line;">{{ $clearance->hcga_notes }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Sesi Input Wawancara Exit Interview oleh HCGA (Tahap 1) --}}
    @if($clearance->Status === 'PENDING_INTERVIEW')
        @if(!empty($isInterviewAuthor))
            <div class="exit-card mb-4" style="border: 1px solid #fed7aa !important;">
                <div class="exit-card-header" style="background-color: #fffbeb;">
                    <div class="exit-card-title text-warning">
                        <i class="fa fa-comments text-warning"></i>
                        <span class="text-dark">Sesi Wawancara Exit Interview (Tim HCGA)</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-warning py-2 px-3 mb-3 small">
                        <i class="fa fa-info-circle me-1"></i> Sesi Exit Interview dilakukan oleh tim HCGA secara tatap muka / offline. Catat hasil dialog mendalam dengan karyawan sebelum diajukan ke Atasan Departemen terkait.
                    </div>
                    <form action="{{ route('hrd-exit-clearance-interview-save', $clearance->id) }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Catatan Hasil Wawancara Exit Interview <span class="text-danger">*</span></label>
                            <textarea name="exit_interview_notes" class="form-control" rows="5" required placeholder="Catat poin-poin hasil wawancara, respon karyawan, alasan utama resign, dan saran karyawan saat sesi tatap muka..."></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-warning exit-btn text-dark">
                                <i class="fa fa-check"></i> Simpan Exit Interview & Ajukan ke Atasan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="alert alert-warning d-flex align-items-center mb-4 border-0 shadow-sm">
                <i class="fa fa-comments fa-lg me-3 text-warning"></i>
                <div>
                    <strong>Menunggu Sesi Exit Interview (HCGA)</strong><br>
                    <span class="small text-muted">Formulir Exit Clearance karyawan telah diterima. Menunggu tim HCGA melaksanakan dan mencatat hasil wawancara exit interview sebelum diajukan ke Atasan Departemen terkait.</span>
                </div>
            </div>
        @endif
    @endif

    {{-- Sesi Approval oleh Atasan Departemen Terkait (Tahap 2) --}}
    @if($clearance->Status === 'PENDING_MANAGER_APPROVAL')
        @if(!empty($isManagerApprover))
            <div class="exit-card mb-4" style="border: 1px solid #bae6fd !important;">
                <div class="exit-card-header" style="background-color: #f0f9ff;">
                    <div class="exit-card-title text-info">
                        <i class="fa fa-user-check text-info"></i>
                        <span class="text-dark">Approval Atasan Departemen Terkait</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info py-2 px-3 mb-3 small">
                        <i class="fa fa-info-circle me-1"></i> Tim HCGA telah mencatat hasil Exit Interview karyawan. Silakan tinjau kelengkapan serah terima pekerjaan dan berikan persetujuan sebelum diteruskan ke verifikasi final HCGA.
                    </div>
                    <form action="{{ route('hrd-exit-clearance-manager-approve', $clearance->id) }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Catatan Manager / Dept. Head (Opsional)</label>
                            <textarea name="manager_notes" class="form-control" rows="3" placeholder="Rekomendasi tindak lanjut, catatan serah terima pekerjaan, atau pengganti posisi..."></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-info exit-btn text-white">
                                <i class="fa fa-check-circle"></i> Setujui & Teruskan ke Verifikasi HCGA
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="alert alert-info d-flex align-items-center mb-4 border-0 shadow-sm">
                <i class="fa fa-user-check fa-lg me-3 text-info"></i>
                <div>
                    <strong>Menunggu Approval Atasan Departemen</strong><br>
                    <span class="small text-muted">Sesi Exit Interview telah dicatat oleh tim HCGA. Berkas saat ini sedang menunggu persetujuan dari Atasan Departemen terkait.</span>
                </div>
            </div>
        @endif
    @endif

    {{-- Sesi Verifikasi Final oleh HCGA (Tahap 3) --}}
    @if($clearance->Status === 'PENDING_HCGA_APPROVAL')
        @if(!empty($isHcga) || !empty($isAdmin))
            <div class="exit-card mb-4" style="border: 1px solid #bfdbfe !important;">
                <div class="exit-card-header" style="background-color: #eff6ff;">
                    <div class="exit-card-title text-primary">
                        <i class="fa fa-check-square-o text-primary"></i>
                        <span>Verifikasi Final & Approval HCGA</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info py-2 px-3 mb-3 small">
                        <i class="fa fa-info-circle me-1"></i> Verifikasi kelengkapan pengembalian aset, data kepatuhan, dan catatan exit interview sebelum menyelesaikan proses Exit Clearance karyawan.
                    </div>
                    <form action="{{ route('hrd-exit-clearance-hcga-verify', $clearance->id) }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Catatan Verifikasi HCGA</label>
                            <textarea name="hcga_notes" class="form-control" rows="3" placeholder="Catatan verifikasi payroll cutoff, kepatuhan administrasi, atau pengembalian aset..."></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary exit-btn">
                                <i class="fa fa-check-circle"></i> Verifikasi & Selesaikan Exit Clearance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="alert alert-info d-flex align-items-center mb-4 border-0 shadow-sm">
                <i class="fa fa-hourglass-half fa-lg me-3 text-primary"></i>
                <div>
                    <strong>Tahap Verifikasi Final Tim HCGA</strong><br>
                    <span class="small text-muted">Persetujuan Atasan Departemen telah selesai. Berkas saat ini sedang dalam proses verifikasi akhir kepatuhan dan payroll cutoff oleh tim HCGA.</span>
                </div>
            </div>
        @endif
    @endif

    {{-- Banner Notifikasi Status Selesai --}}
    @if($clearance->Status === 'COMPLETED')
        <div class="alert alert-success d-flex align-items-center mb-4 border-0 shadow-sm">
            <i class="fa fa-check-circle fa-lg me-3 text-success"></i>
            <div>
                <strong>Exit Clearance Selesai (Completed)</strong><br>
                <span class="small text-muted">Seluruh tahapan Exit Clearance, pengembalian aset/inventaris, dan verifikasi HCGA telah selesai diproses. Dokumen resmi dapat diunduh melalui tombol Cetak PDF di atas.</span>
            </div>
        </div>
    @endif

    {{-- Tampilan Arsip Berkas Digital Resmi --}}
    @if(!empty($documents) && count($documents) > 0)
        <div class="exit-card mb-4">
            <div class="exit-card-header">
                <div class="exit-card-title">
                    <i class="fa fa-folder text-danger"></i>
                    <span>Berkas Digital & Arsip Kepatuhan</span>
                </div>
                <span class="badge bg-light text-dark border px-2 py-1">{{ count($documents) }} Dokumen Tersimpan</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tipe Dokumen</th>
                                <th>Nama Berkas</th>
                                <th>Ukuran</th>
                                <th>Tanggal Arsip</th>
                                <th>Integritas (SHA-256)</th>
                                <th class="text-center">Unduh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                <tr>
                                    <td>
                                        @if($doc->Document_Type === 'FINAL_CLEARANCE_PDF')
                                            <span class="badge bg-success text-white">Final Clearance PDF</span>
                                        @elseif($doc->Document_Type === 'RESIGNATION_LETTER')
                                            <span class="badge bg-info text-white">Surat Resign</span>
                                        @else
                                            <span class="badge bg-secondary text-white">{{ $doc->Document_Type }}</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $doc->File_Name }}</strong></td>
                                    <td>{{ $doc->File_Size ? round($doc->File_Size / 1024, 2) . ' KB' : '-' }}</td>
                                    <td>{{ date('d M Y H:i', strtotime($doc->created_at)) }}</td>
                                    <td><code class="small text-muted">{{ $doc->Sha256_Checksum ? substr($doc->Sha256_Checksum, 0, 16) . '...' : '-' }}</code></td>
                                    <td class="text-center">
                                        <a href="{{ asset($doc->File_Path) }}" target="_blank" class="btn btn-sm btn-outline-primary exit-btn py-1 px-2">
                                            <i class="fa fa-download"></i> Unduh
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif


    {{-- Checklist Pengembalian Aset (Bagian E) --}}
    <div class="exit-card mb-4">
        <div class="exit-card-header">
            <div class="exit-card-title">
                <i class="fa fa-check-square text-primary"></i>
                <span>E. CEKLIST PENGEMBALIAN ASET & KEWAJIBAN (17 ITEM)</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;" class="text-center">No</th>
                            <th style="width: 45%;">Item Kelengkapan</th>
                            <th style="width: 15%;" class="text-center">Status</th>
                            <th style="width: 20%;">Keterangan</th>
                            <th style="width: 15%;">Penanggung Jawab Serah Terima</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checklist as $idx => $item)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td>{{ $item->Item_Name }}</td>
                                <td class="text-center">
                                    @if($item->Status === 'SUDAH')
                                        <span class="badge bg-success text-white">Sudah</span>
                                    @elseif($item->Status === 'BELUM')
                                        <span class="badge bg-danger text-white">Belum</span>
                                    @else
                                        <span class="badge bg-secondary text-white">Tidak Ada</span>
                                    @endif
                                </td>
                                <td>{{ $item->Keterangan ?? '-' }}</td>
                                <td>{{ $item->Penanggung_Jawab ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Audit Trail & Riwayat Aktivitas --}}
    <div class="exit-card mb-4">
        <div class="exit-card-header">
            <div class="exit-card-title">
                <i class="fa fa-history text-primary"></i>
                <span>Riwayat Aktivitas & Audit Trail</span>
            </div>
            <div>
                <span class="badge bg-primary text-white">{{ count($auditLogs ?? []) }} Catatan Aktivitas</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if(!empty($auditLogs) && count($auditLogs) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 18%;">Waktu</th>
                                <th style="width: 22%;">Aktivitas / Event</th>
                                <th style="width: 18%;">Pelaksana</th>
                                <th style="width: 14%;" class="text-center">Status</th>
                                <th style="width: 28%;">Keterangan & Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auditLogs as $log)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ date('d M Y', strtotime($log->created_at)) }}</div>
                                        <small class="text-muted"><i class="fa fa-clock-o me-1"></i>{{ date('H:i:s', strtotime($log->created_at)) }} WIB</small>
                                    </td>
                                    <td>
                                        <strong class="text-dark">{{ $log->Title }}</strong>
                                        <div><code class="small text-secondary">{{ $log->Action }}</code></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $log->Actor_Name ?? 'SYSTEM' }}</div>
                                        @if($log->Actor_Role === 'ADMIN')
                                            <span class="badge bg-danger text-white" style="font-size: 0.7rem;">Administrator</span>
                                        @elseif($log->Actor_Role === 'HCGA')
                                            <span class="badge bg-success text-white" style="font-size: 0.7rem;">HCGA</span>
                                        @elseif($log->Actor_Role === 'ATASAN')
                                            <span class="badge bg-primary text-white" style="font-size: 0.7rem;">Atasan Langsung</span>
                                        @elseif($log->Actor_Role === 'EMPLOYEE')
                                            <span class="badge bg-info text-white" style="font-size: 0.7rem;">Karyawan</span>
                                        @else
                                            <span class="badge bg-secondary text-white" style="font-size: 0.7rem;">System / Cron</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($log->New_Status)
                                            <span class="badge bg-dark text-white">{{ $log->New_Status }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-secondary small">{{ $log->Description }}</div>
                                        @if(!empty($log->Ip_Address))
                                            <div class="text-muted" style="font-size: 0.7rem;"><i class="fa fa-globe me-1"></i>IP: {{ $log->Ip_Address }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center text-muted">
                    <i class="fa fa-info-circle fs-4 mb-2 d-block"></i>
                    Belum ada riwayat aktivitas tercatat untuk dokumen ini.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
