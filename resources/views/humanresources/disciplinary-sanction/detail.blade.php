@extends('layout.mainlayout-hrd')

@section('title', 'Detail Surat Peringatan & Sanksi')

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
</style>

{{-- Breadcrumb Header Persis Style SPPD --}}
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
            Rincian Surat Peringatan & Komitmen
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hrd-disciplinary-sanction-index') }}">Surat Peringatan</a></li>
                <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">Detail ({{ $sanction->No_Trx }})</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if(in_array($sanction->Status, ['WAITING_EMPLOYEE', 'ACTIVE', 'EXPIRED']))
            <a href="{{ route('hrd-disciplinary-sanction-download-pdf', $sanction->id) }}" target="_blank" class="btn btn-outline-danger sanction-btn">
                <i class="fa fa-file-pdf-o"></i> Unduh PDF Resmi
            </a>
        @endif
        <a href="{{ route('hrd-disciplinary-sanction-index') }}" class="btn btn-light sanction-btn border">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
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

    {{-- Ringkasan Status Sanksi --}}
    <div class="card sanction-card mb-4">
        <div class="sanction-card-header">
            <div class="sanction-card-title">
                <i class="fa fa-info-circle text-primary"></i>
                <span>Informasi Surat Sanksi ({{ $sanction->Doc_ID }})</span>
            </div>
            <div>
                No. SP: <strong class="text-primary fs-6">{{ $sanction->No_Trx }}</strong>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row text-center gy-3">
                <div class="col-md-3 border-end">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Karyawan</small>
                    <span class="fw-bold text-dark fs-6">{{ $sanction->Nama }}</span>
                    <small class="text-muted d-block">{{ $sanction->NIK ?? '-' }}</small>
                </div>
                <div class="col-md-3 border-end">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Jabatan / Departemen</small>
                    <span class="fw-bold text-dark fs-6">{{ $sanction->Jabatan ?? '-' }}</span>
                    <small class="text-muted d-block">{{ $sanction->Departemen ?? '-' }}</small>
                </div>
                <div class="col-md-3 border-end">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Tingkat Sanksi</small>
                    @if($sanction->Sanction_Level === 'LISAN')
                        <span class="badge badge-soft-secondary fs-6 px-3 py-1">Peringatan Lisan</span>
                    @elseif($sanction->Sanction_Level === 'SP1')
                        <span class="badge badge-soft-warning fs-6 px-3 py-1">Surat Peringatan 1</span>
                    @elseif($sanction->Sanction_Level === 'SP2')
                        <span class="badge badge-soft-primary fs-6 px-3 py-1">Surat Peringatan 2</span>
                    @elseif($sanction->Sanction_Level === 'SP3')
                        <span class="badge badge-soft-danger fs-6 px-3 py-1">Surat Peringatan 3</span>
                    @else
                        <span class="badge badge-soft-secondary fs-6 px-3 py-1">{{ $sanction->Sanction_Level }}</span>
                    @endif
                    <small class="text-muted d-block mt-1">Masa Berlaku: <strong>{{ $sanction->Duration_Months }} Bulan</strong> (s.d {{ $sanction->Valid_Until ? date('d M Y', strtotime($sanction->Valid_Until)) : '-' }})</small>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Status Transaksi</small>
                    @if($sanction->Status === 'PENDING_HCGA')
                        <span class="badge badge-soft-warning px-2 py-1"><i class="fa fa-clock-o me-1"></i> Verifikasi HCGA</span>
                    @elseif($sanction->Status === 'WAITING_EMPLOYEE')
                        <span class="badge badge-soft-primary px-2 py-1"><i class="fa fa-pencil me-1"></i> Menunggu TTD Karyawan</span>
                    @elseif($sanction->Status === 'ACTIVE')
                        <span class="badge badge-soft-success px-2 py-1"><i class="fa fa-check-circle me-1"></i> Aktif Berlaku</span>
                    @elseif($sanction->Status === 'EXPIRED')
                        <span class="badge badge-soft-secondary px-2 py-1"><i class="fa fa-clock-o me-1"></i> Selesai (Expired)</span>
                    @elseif($sanction->Status === 'REJECTED')
                        <span class="badge badge-soft-danger px-2 py-1"><i class="fa fa-times-circle me-1"></i> Ditolak</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Pelanggaran & Dasar Bukti --}}
    <div class="card sanction-card mb-4">
        <div class="sanction-card-header">
            <div class="sanction-card-title">
                <i class="fa fa-list-alt text-primary"></i>
                <span>Rincian Pelanggaran & Bukti</span>
            </div>
            <div class="small text-muted">
                Dilaporkan oleh: <strong>{{ $sanction->Reporter_Nama }}</strong> ({{ $sanction->Reporter_Jabatan ?? 'Atasan' }})
            </div>
        </div>
        <div class="card-body p-4">
            <h6 class="fw-bold text-dark mb-2">Poin-poin Pelanggaran Perjanjian Kerja / Peraturan Perusahaan:</h6>
            @php
                $points = [];
                if (!empty($sanction->Violation_Points)) {
                    $decoded = json_decode($sanction->Violation_Points, true);
                    $points = is_array($decoded) ? $decoded : explode("\n", $sanction->Violation_Points);
                }
            @endphp
            <ol class="mb-3 ps-3">
                @foreach($points as $p)
                    @if(!empty(trim($p)))
                        <li class="mb-1 text-dark">{{ trim($p) }}</li>
                    @endif
                @endforeach
            </ol>

            <div class="row mt-3 pt-3 border-top gy-2">
                <div class="col-md-4">
                    <span class="text-muted d-block small">Dasar Bukti:</span>
                    <strong class="text-dark">{{ $sanction->Evidence_Basis }}</strong>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">Sifat Pelanggaran:</span>
                    <span class="badge bg-secondary text-white">{{ $sanction->Violation_Severity }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">Tanggal Laporan:</span>
                    <strong class="text-dark">{{ date('d M Y H:i', strtotime($sanction->created_at)) }}</strong>
                </div>
                @if(!empty($sanction->Evidence_Description))
                    <div class="col-12 mt-2">
                        <span class="text-muted d-block small">Kronologi / Uraian Bukti:</span>
                        <div class="p-3 bg-light rounded text-dark small border">{{ $sanction->Evidence_Description }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Jadwal Klarifikasi / Sidang Rapat (Khusus SP2 / SP3 / Ada Jadwal) --}}
    @if(!empty($sanction->Meeting_Type) && $sanction->Meeting_Type !== 'NONE')
        <div class="card sanction-card mb-4" style="border: 1px solid #fed7aa !important;">
            <div class="sanction-card-header" style="background-color: #fffbeb;">
                <div class="sanction-card-title text-warning">
                    <i class="fa fa-calendar-check-o text-warning"></i>
                    <span class="text-dark">Jadwal Sidang & Klarifikasi Pelanggaran</span>
                </div>
                <span class="badge bg-warning text-dark">{{ $sanction->Meeting_Type }}</span>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="mb-2">
                            <span class="text-muted small d-block">Waktu Pertemuan:</span>
                            <strong class="fs-6 text-dark">{{ $sanction->Meeting_Date ? date('d F Y, Pukul H:i \W\I\B', strtotime($sanction->Meeting_Date)) : '-' }}</strong>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Ruangan / Tautan Virtual:</span>
                            @if(filter_var($sanction->Meeting_Location, FILTER_VALIDATE_URL))
                                <a href="{{ $sanction->Meeting_Location }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                    <i class="fa fa-video-camera me-1"></i> Buka Link Meeting Online
                                </a>
                            @else
                                <span class="fw-semibold text-dark">{{ $sanction->Meeting_Location ?? '-' }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        @php
                            $waMsg = sprintf(
                                "Halo %s, diberitahukan perihal Surat Peringatan No: %s. Anda dijadwalkan hadir pada sesi klarifikasi: %s di %s. Mohon kehadirannya tepat waktu.",
                                $sanction->Nama,
                                $sanction->No_Trx,
                                $sanction->Meeting_Date ? date('d M Y H:i', strtotime($sanction->Meeting_Date)) : '',
                                $sanction->Meeting_Location
                            );
                            $waUrl = \App\Services\HCGA\DisciplinarySanctionNotificationService::generateWhatsappUrl('', $waMsg);
                        @endphp
                        <a href="{{ $waUrl }}" target="_blank" class="btn btn-success sanction-btn">
                            <i class="fa fa-whatsapp"></i> Bagikan Info via WA
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Sesi Komitmen Perbaikan Karyawan --}}
    @if($sanction->Status === 'WAITING_EMPLOYEE' && auth()->id() == $sanction->User_ID)
        <div class="card sanction-card mb-4" style="border: 2px solid #3b82f6 !important;">
            <div class="sanction-card-header bg-primary text-white">
                <div class="sanction-card-title text-white">
                    <i class="fa fa-pencil-square-o"></i>
                    <span>Tanda Tangan & Komitmen Perbaikan Sikap Kerja</span>
                </div>
                <small class="badge bg-white text-primary">Wajib Diisi Karyawan</small>
            </div>
            <div class="card-body p-4">
                <p class="text-muted">
                    Sesuai dengan ketentuan perusahaan, silakan baca seluruh butir sanksi di atas dan tuliskan pernyataan komitmen perbaikan sikap kerja Anda dengan sungguh-sungguh untuk masa yang akan datang.
                </p>
                <form action="{{ route('hrd-disciplinary-sanction-commitment-sign', $sanction->id) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Komitmen Karyawan <span class="text-danger">*</span></label>
                        <textarea name="Employee_Commitment" class="form-control" rows="4" required placeholder="Saya mengakui kesalahan tersebut dan berkomitmen untuk memperbaiki sikap kerja, mematuhi seluruh SOP perusahaan, serta tidak mengulangi pelanggaran serupa..."></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="fa fa-lock me-1"></i> Penandatanganan ini bernilai sah sebagai tanda terima resmi sanksi.</small>
                        <button type="submit" class="btn btn-primary sanction-btn">
                            <i class="fa fa-check-circle"></i> Tanda Tangani Komitmen & Terima SP
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @elseif(!empty($sanction->Employee_Commitment))
        <div class="card sanction-card mb-4">
            <div class="sanction-card-header">
                <div class="sanction-card-title">
                    <i class="fa fa-handshake-o text-success"></i>
                    <span>Pernyataan Komitmen Perbaikan Karyawan</span>
                </div>
                <div class="small text-muted">
                    Ditandatangani secara digital pada: <strong>{{ date('d M Y H:i', strtotime($sanction->Employee_Signed_At)) }}</strong>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="p-3 bg-light rounded text-dark border mb-0">
                    {{ $sanction->Employee_Commitment }}
                </div>
            </div>
        </div>
    @endif

    {{-- Berkas Digital & Arsip Kepatuhan (Hybrid Upload) --}}
    <div class="card sanction-card mb-4">
        <div class="sanction-card-header">
            <div class="sanction-card-title">
                <i class="fa fa-folder-open text-danger"></i>
                <span>Berkas Digital & Arsip Dokumen</span>
            </div>
            @if($isAdmin || $isHcga)
                <button type="button" class="btn btn-outline-primary btn-sm sanction-btn" data-bs-toggle="modal" data-bs-target="#modalUploadSanctionDoc">
                    <i class="fa fa-upload"></i> Unggah Berkas Fisik / BA
                </button>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tipe Dokumen</th>
                            <th>Nama Berkas</th>
                            <th>Ukuran</th>
                            <th>Tanggal Unggah</th>
                            <th>Checksum (SHA-256)</th>
                            <th class="text-center pe-3">Unduh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                            <tr>
                                <td class="ps-3">
                                    @if($doc->Document_Type === 'OFFICIAL_SP_PDF')
                                        <span class="badge bg-success text-white">Surat SP Resmi (PDF)</span>
                                    @elseif($doc->Document_Type === 'MEETING_MINUTES_SCAN')
                                        <span class="badge bg-info text-white">Scan Berita Acara Sidang</span>
                                    @elseif($doc->Document_Type === 'EMPLOYEE_SIGNED_SCAN')
                                        <span class="badge bg-primary text-white">Scan TTD Basah</span>
                                    @else
                                        <span class="badge bg-secondary text-white">{{ $doc->Document_Type }}</span>
                                    @endif
                                </td>
                                <td><strong>{{ $doc->File_Name }}</strong></td>
                                <td>{{ $doc->File_Size ? round($doc->File_Size / 1024, 2) . ' KB' : '-' }}</td>
                                <td>{{ date('d M Y H:i', strtotime($doc->created_at)) }}</td>
                                <td><code class="small text-muted">{{ $doc->Sha256_Checksum ? substr($doc->Sha256_Checksum, 0, 16) . '...' : '-' }}</code></td>
                                <td class="text-center pe-3">
                                    <a href="{{ asset($doc->File_Path) }}" target="_blank" class="btn btn-sm btn-outline-primary sanction-btn py-1 px-2">
                                        <i class="fa fa-download"></i> Unduh
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted small">Belum ada berkas digital terlampir.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Upload Berkas Digital --}}
@if($isAdmin || $isHcga)
    <div class="modal fade" id="modalUploadSanctionDoc" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('hrd-disciplinary-sanction-document-upload', $sanction->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold"><i class="fa fa-upload text-primary me-2"></i> Unggah Berkas Arsip</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Tipe Dokumen <span class="text-danger">*</span></label>
                            <select name="Document_Type" class="form-select" required>
                                <option value="MEETING_MINUTES_SCAN">Scan Berita Acara Klarifikasi / Sidang</option>
                                <option value="EMPLOYEE_SIGNED_SCAN">Scan Dokumen Tanda Tangan Basah</option>
                                <option value="EVIDENCE_FILE">Bukti Tambahan Pelanggaran</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Pilih File (PDF / Gambar) <span class="text-danger">*</span></label>
                            <input type="file" name="Document_File" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                            <small class="text-muted">Maksimal 5MB (Format: PDF, JPG, PNG)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-3">Unggah Berkas</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

