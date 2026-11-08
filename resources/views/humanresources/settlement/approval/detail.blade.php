@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<style>
    .settlement-page-wrapper {
        margin-bottom: 2.5rem;
    }
    .settlement-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        background: #ffffff;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .settlement-card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .settlement-card-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .settlement-btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.5rem 1.15rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.2s ease;
    }
    .settlement-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }
    .info-label {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.35rem;
    }
    .info-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
    }
    .info-card-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.9rem 1.1rem;
        height: 100%;
        transition: all 0.2s ease;
    }
    .info-card-box:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
    }
    .badge-status-lg {
        padding: 0.4rem 0.85rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .table-custom-header th {
        background-color: #f1f5f9;
        color: #334155;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .section-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        margin-top: 1.5rem;
        margin-bottom: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .verification-box {
        background: #fffdf5;
        border: 1px solid #fef08a;
        border-radius: 10px;
        padding: 1.25rem;
        margin-top: 1.5rem;
    }
</style>

<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
            Verifikasi Request Settlement
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('hrd-settlement-approval-index') }}">Verifikasi Settlement</a></li>
                <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Pemeriksaan Dokumen ({{ $settlement->STTL_No }})</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ url('hrd-settlement-print?ID=' . $settlement->ID) }}" class="btn btn-outline-info settlement-btn" target="_blank">
            <i class="fa fa-print"></i> Cetak Dokumen
        </a>
        <a href="{{ url('hrd-settlement-approval-index') }}" class="btn btn-outline-secondary settlement-btn">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="settlement-page-wrapper">
    <div class="card settlement-card">
        <div class="settlement-card-header">
            <div>
                <h3 class="settlement-card-title">
                    <i class="fa fa-clipboard-check text-warning"></i> Form Verifikasi Settlement : {{ $settlement->STTL_No }}
                </h3>
                <small class="text-muted"><i class="fa fa-user me-1"></i> Diajukan oleh: <strong>{{ $settlement->Requester_Name }}</strong> ({{ $settlement->Department }})</small>
            </div>
            <div>
                @php
                    $status = strtoupper($settlement->Status);
                    $badgeClass = 'bg-secondary text-white';
                    $iconClass = 'fa-clock';
                    if ($status === 'APPROVED') {
                        $badgeClass = 'bg-success text-white';
                        $iconClass = 'fa-check-circle';
                    } elseif ($status === 'REJECTED') {
                        $badgeClass = 'bg-danger text-white';
                        $iconClass = 'fa-times-circle';
                    } elseif ($status === 'WAITING' || $status === 'PENDING') {
                        $badgeClass = 'bg-warning text-dark';
                        $iconClass = 'fa-hourglass-half';
                    } elseif ($status === 'CLOSED') {
                        $badgeClass = 'bg-dark text-white';
                        $iconClass = 'fa-lock';
                    }
                @endphp
                <span class="badge-status-lg {{ $badgeClass }}">
                    <i class="fa {{ $iconClass }}"></i> Status: {{ $status }}
                </span>
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Informasi Pokok Dokumen -->
            <div class="row g-3">
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-card-box">
                        <div class="info-label"><i class="fa fa-barcode me-1"></i> Nomor Settlement</div>
                        <div class="info-value font-monospace text-primary">{{ $settlement->STTL_No }}</div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-card-box">
                        <div class="info-label"><i class="fa fa-user me-1"></i> Pemohon & NIK</div>
                        <div class="info-value">{{ $settlement->Requester_Name }}</div>
                        <small class="text-muted">NIK: {{ $settlement->Requester_NIK ?: '-' }}</small>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-card-box">
                        <div class="info-label"><i class="fa fa-building me-1"></i> Departemen</div>
                        <div class="info-value">{{ $settlement->Department }}</div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-card-box">
                        <div class="info-label"><i class="fa fa-plane-departure me-1"></i> Referensi SPPD</div>
                        <div class="info-value">
                            @if (!empty($settlement->SPPD_ID))
                                <a href="{{ url('hrd-sppd-detail?ID=' . $settlement->SPPD_ID) }}" class="text-decoration-none fw-bold text-primary" target="_blank">
                                    <i class="fa fa-external-link-alt me-1"></i> {{ $settlement->SPPD_No }}
                                </a>
                            @elseif(!empty($settlement->SPPD_No))
                                {{ $settlement->SPPD_No }}
                            @else
                                <span class="text-muted font-italic">- Tanpa Referensi SPPD -</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-card-box">
                        <div class="info-label"><i class="fa fa-hand-holding-usd me-1"></i> Advance / Uang Muka Diterima</div>
                        <div class="info-value">
                            {{ $settlement->Advance_Nominal !== null ? 'Rp ' . number_format((float) $settlement->Advance_Nominal, 0, ',', '.') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-card-box">
                        <div class="info-label"><i class="fa fa-university me-1"></i> Rekening Bank Karyawan</div>
                        <div class="info-value">{{ $settlement->Bank ?: '-' }}</div>
                    </div>
                </div>

                <div class="col-md-12 col-12">
                    <div class="info-card-box">
                        <div class="info-label"><i class="fa fa-paperclip me-1"></i> Berkas Lampiran Invoice / Bukti Kwitansi</div>
                        <div class="info-value">
                            @if ($invoice)
                                <a href="{{ url('hrd-settlement-invoice?ID=' . $settlement->ID) }}" class="btn btn-sm btn-outline-primary mt-1" target="_blank">
                                    <i class="fa fa-file-download me-1"></i> Unduh / Buka Dokumen: {{ $invoice->Filename }}
                                </a>
                            @else
                                <span class="text-muted">- Tidak ada berkas kwitansi terlampir -</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rincian Biaya -->
            <div class="section-title">
                <i class="fa fa-receipt text-primary"></i> Rincian Pengeluaran yang Diverifikasi
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-2">
                    <thead class="table-custom-header">
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th>Deskripsi Pengeluaran</th>
                            <th style="width: 200px;" class="text-end">Jumlah (Rp)</th>
                            <th>Keterangan / Keperluan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @forelse ($expenses as $i => $e)
                            @php $total += (float) $e->Cost; @endphp
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $e->Expense }}</td>
                                <td class="text-end font-monospace">Rp {{ number_format((float) $e->Cost, 0, ',', '.') }}</td>
                                <td>{{ $e->Description ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">Belum ada rincian biaya.</td>
                            </tr>
                        @endforelse
                        <tr class="table-light fw-bold">
                            <td colspan="2" class="text-end text-uppercase">Total Pengeluaran Real:</td>
                            <td class="text-end font-monospace text-primary h6 mb-0">Rp {{ number_format($total, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        @if ($settlement->Advance_Nominal !== null && (float)$settlement->Advance_Nominal > 0)
                            @php 
                                $adv = (float) $settlement->Advance_Nominal;
                                $selisih = $total - $adv;
                            @endphp
                            <tr class="table-light fw-bold">
                                <td colspan="2" class="text-end text-uppercase">Advance / Uang Muka Diterima:</td>
                                <td class="text-end font-monospace text-muted">Rp {{ number_format($adv, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                            <tr class="table-light fw-bold">
                                <td colspan="2" class="text-end text-uppercase">
                                    {{ $selisih >= 0 ? 'Kurang Bayar (Reimbursement ke Karyawan):' : 'Lebih Bayar (Dikembalikan ke Kantor):' }}
                                </td>
                                <td class="text-end font-monospace {{ $selisih >= 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format(abs($selisih), 0, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- History Logs -->
            <div class="section-title">
                <i class="fa fa-history text-primary"></i> Riwayat Aktivitas Pengajuan
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-custom-header">
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th style="width: 150px;">Aksi</th>
                            <th style="width: 200px;">Pelaksana (Actor)</th>
                            <th>Catatan</th>
                            <th style="width: 170px;" class="text-center">Waktu Eksekusi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $i => $l)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $l->Action }}</span></td>
                            <td class="fw-semibold">{{ $l->Actor_Name }}</td>
                            <td>{{ $l->Note ?: '-' }}</td>
                            <td class="text-center text-muted small">{{ $l->Created_At }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-2 text-muted">Belum ada riwayat aktivitas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Panel Aksi Verifikasi HCGA Dept Head -->
            @if ($canApprove)
                <div class="verification-box">
                    <h5 class="fw-bold text-dark mb-2">
                        <i class="fa fa-user-check text-warning me-1"></i> Keputusan Verifikasi HCGA Dept Head
                    </h5>
                    <p class="text-muted small mb-3">
                        Silakan periksa kembali keaslian kwitansi/invoice serta kesesuaian rincian pengeluaran di atas sebelum memberikan keputusan.
                    </p>
                    <div class="form-group mb-3">
                        <label for="Note" class="fw-semibold text-dark mb-1">Catatan Verifikasi (Wajib diisi jika menolak / REJECT):</label>
                        <textarea id="Note" class="form-control" rows="3" placeholder="Tuliskan catatan verifikasi, alasan persetujuan, atau alasan penolakan..."></textarea>
                    </div>
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-danger settlement-btn" id="rejectBtn">
                                <i class="fa fa-times"></i> REJECT PENGATURAN
                            </button>
                            <button type="button" class="btn btn-success settlement-btn" id="approveBtn">
                                <i class="fa fa-check"></i> APPROVE SETTLEMENT
                            </button>
                        </div>
                        <a href="{{ url('hrd-settlement-approval-index') }}" class="btn btn-secondary settlement-btn">
                            <i class="fa fa-arrow-left"></i> Kembali ke Antrian
                        </a>
                    </div>
                </div>
            @else
                <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ url('hrd-settlement-approval-index') }}" class="btn btn-secondary settlement-btn">
                        <i class="fa fa-arrow-left"></i> Kembali ke Antrian
                    </a>
                </div>
            @endif

        </div>
    </div>
</div>

<script>
$('#approveBtn').click(function (e) {
    e.preventDefault();
    if (!confirm('Apakah Anda yakin menyetujui (APPROVE) pengajuan settlement ini?')) return;
    doVerification('1');
});

$('#rejectBtn').click(function (e) {
    e.preventDefault();
    var note = $.trim($('#Note').val());
    if (note === '') {
        alert('Mohon cantumkan alasan penolakan pada kolom Catatan Verifikasi!');
        $('#Note').focus();
        return;
    }
    if (!confirm('Apakah Anda yakin MENOLAK (REJECT) pengajuan settlement ini?')) return;
    doVerification('2');
});

function doVerification(stat) {
    var $btnApprove = $('#approveBtn');
    var $btnReject = $('#rejectBtn');
    $btnApprove.prop('disabled', true);
    $btnReject.prop('disabled', true);

    $.ajax({
        url: 'hrd-settlement-approval-submit',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        data: {
            ID: '{{ $settlement->ID }}',
            Approval: stat,
            Note: $('#Note').val()
        },
        success: function (data) {
            $btnApprove.prop('disabled', false);
            $btnReject.prop('disabled', false);
            if (data.status === 'success') {
                alert('Verifikasi Settlement Berhasil Disimpan!');
                location.href = 'hrd-settlement-approval-index';
            } else {
                alert('Verifikasi Gagal: ' + data.message);
            }
        },
        error: function (xhr) {
            $btnApprove.prop('disabled', false);
            $btnReject.prop('disabled', false);
            var msg = 'Terjadi kesalahan sistem saat memproses verifikasi.';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            alert(msg);
        }
    });
}
</script>
@endsection
