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
    .btn-copy-acc {
        padding: 0.25rem 0.65rem;
        font-size: 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        transition: all 0.15s ease;
    }
    .settlement-interactive-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        padding: 1.15rem 1.35rem;
        height: 100%;
        transition: all 0.2s ease;
    }
    .settlement-interactive-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
</style>

<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
            Rincian Request Settlement
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('hrd-settlement-index') }}">Settlement</a></li>
                <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Detail Pengajuan ({{ $settlement->STTL_No }})</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ url('hrd-settlement-print?ID=' . $settlement->ID) }}" class="btn btn-outline-info settlement-btn" target="_blank">
            <i class="fa fa-print"></i> Cetak / PDF
        </a>
        <a href="{{ url('hrd-settlement-index') }}" class="btn btn-outline-secondary settlement-btn">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="settlement-page-wrapper">
    <div class="card settlement-card">
        <div class="settlement-card-header">
            <div>
                <h3 class="settlement-card-title">
                    <i class="fa fa-file-invoice text-primary"></i> Data Pengajuan Settlement : {{ $settlement->STTL_No }}
                </h3>
                <small class="text-muted"><i class="fa fa-calendar-alt me-1"></i> Diajukan pada: {{ $settlement->Created_Date ?? ($settlement->Created_At ?? '-') }}</small>
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
                    <i class="fa {{ $iconClass }}"></i> {{ $status }}
                </span>
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Grid Informasi Dokumen -->
            <div class="row g-3">
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-card-box">
                        <div class="info-label"><i class="fa fa-barcode me-1"></i> Nomor Settlement</div>
                        <div class="info-value font-monospace text-primary">{{ $settlement->STTL_No }}</div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-card-box">
                        <div class="info-label"><i class="fa fa-user me-1"></i> Karyawan Pemohon</div>
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

                @php
                    $calcManualTotal = 0;
                    foreach ($expenses as $e) {
                        $calcManualTotal += (float) $e->Cost;
                    }
                    $matrixAmt = isset($settlement->Matrix_Amount) ? (float) $settlement->Matrix_Amount : 0;
                    $calcTotal = $calcManualTotal + $matrixAmt;

                    $advNom = $settlement->Advance_Nominal !== null ? (float)$settlement->Advance_Nominal : 0;
                    $diffVal = $calcTotal - $advNom;
                @endphp

                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-card-box">
                        <div class="info-label"><i class="fa fa-hand-holding-usd me-1"></i> Biaya Advance Diterima</div>
                        <div class="info-value">
                            {{ $settlement->Advance_Nominal !== null ? 'Rp ' . number_format((float) $settlement->Advance_Nominal, 0, ',', '.') : '-' }}
                        </div>
                        @if ($advNom > 0 && $diffVal < 0)
                            <div class="mt-1">
                                <span class="badge bg-success text-white" style="font-size: 0.75rem;">
                                    <i class="fa fa-undo-alt me-1"></i> Lebih Bayar: Rp {{ number_format(abs($diffVal), 0, ',', '.') }}
                                </span>
                            </div>
                        @elseif ($diffVal > 0)
                            <div class="mt-1">
                                <span class="badge bg-danger text-white" style="font-size: 0.75rem;">
                                    <i class="fa fa-hand-holding-usd me-1"></i> Kurang Bayar: Rp {{ number_format($diffVal, 0, ',', '.') }}
                                </span>
                            </div>
                        @elseif ($advNom > 0 && $diffVal == 0)
                            <div class="mt-1">
                                <span class="badge bg-info text-white" style="font-size: 0.75rem;">
                                    <i class="fa fa-check-circle me-1"></i> Pas Sesuai Advance
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-card-box">
                        <div class="info-label d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-university me-1"></i> Rekening Bank Tujuan</span>
                            @if (!empty($settlement->Bank))
                                <button type="button" class="btn btn-outline-secondary btn-copy-acc py-0 px-2"
                                    onclick="salinRekeningDetail('{{ addslashes($settlement->Bank) }}', this);"
                                    title="Salin keterangan rekening">
                                    <i class="fa fa-copy"></i> Salin
                                </button>
                            @endif
                        </div>
                        <div class="info-value font-monospace" style="font-size: 0.9rem;">
                            {{ $settlement->Bank ?: '-' }}
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                            @if ($advNom > 0 && $diffVal < 0)
                                <i class="fa fa-info-circle text-success me-1"></i> Rekening kantor tujuan pengembalian dana
                            @elseif ($diffVal > 0)
                                <i class="fa fa-info-circle text-primary me-1"></i> Rekening karyawan tujuan pencairan reimbursement
                            @else
                                <i class="fa fa-info-circle text-muted me-1"></i> Rekening settlement terkonfirmasi
                            @endif
                        </small>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-card-box">
                        <div class="info-label"><i class="fa fa-paperclip me-1"></i> Berkas Invoice / Bukti Kwitansi</div>
                        <div class="info-value">
                            @if ($invoice)
                                <a href="{{ url('hrd-settlement-invoice?ID=' . $settlement->ID) }}" class="btn btn-sm btn-outline-primary mt-1 d-inline-flex align-items-center gap-1 shadow-sm" target="_blank">
                                    <i class="fa fa-file-download text-primary"></i> 
                                    <span class="text-truncate" style="max-width: 170px;">{{ $invoice->Filename }}</span>
                                </a>
                            @else
                                <span class="text-muted small">- Tidak ada file terlampir -</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="info-card-box">
                        <div class="info-label"><i class="fa fa-user-check me-1"></i> Validator / HCGA Dept Head</div>
                        <div class="info-value">{{ $settlement->Validator_Name ?: '-' }}</div>
                        @if (!empty($settlement->Approved_At))
                            <small class="text-muted d-block mt-1"><i class="fa fa-clock me-1"></i> Waktu: {{ $settlement->Approved_At }}</small>
                        @endif
                    </div>
                </div>

                @if ($settlement->Approval_Note)
                <div class="col-12">
                    <div class="info-card-box bg-light border-warning">
                        <div class="info-label text-warning"><i class="fa fa-comment-dots me-1"></i> Catatan Verifikasi HCGA</div>
                        <div class="info-value text-dark" style="font-size: 0.9rem;">{!! nl2br(e($settlement->Approval_Note)) !!}</div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Rincian Biaya -->
            <div class="section-title">
                <i class="fa fa-receipt text-primary"></i> Rincian Penggunaan Biaya Real
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
                                <td colspan="4" class="text-center py-3 text-muted">Belum ada rincian pengeluaran biaya.</td>
                            </tr>
                        @endforelse
                        <tr class="table-light fw-bold">
                            <td colspan="2" class="text-end text-uppercase">Total Pengeluaran Real (Manual):</td>
                            <td class="text-end font-monospace text-primary h6 mb-0">Rp {{ number_format($total, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        @php
                            $matrixAmt = isset($settlement->Matrix_Amount) ? (float) $settlement->Matrix_Amount : 0;
                            $grandTotal = $total + $matrixAmt;
                        @endphp
                        @if ($matrixAmt > 0)
                            <tr class="table-light fw-bold">
                                <td colspan="2" class="text-end text-uppercase">Tunjangan Matriks Regulasi SPPD:</td>
                                <td class="text-end font-monospace text-info h6 mb-0">+ Rp {{ number_format($matrixAmt, 0, ',', '.') }}</td>
                                <td><small class="text-muted"><i class="fa fa-info-circle me-1"></i> Uang makan, uang saku, tiket & penginapan otomatis</small></td>
                            </tr>
                            <tr class="table-light fw-bold">
                                <td colspan="2" class="text-end text-uppercase">Total Biaya Diperhitungkan:</td>
                                <td class="text-end font-monospace text-dark h6 mb-0">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        @endif
                        @if ($settlement->Advance_Nominal !== null && (float)$settlement->Advance_Nominal > 0)
                            @php 
                                $adv = (float) $settlement->Advance_Nominal;
                                $selisih = $grandTotal - $adv;
                            @endphp
                            <tr class="table-light fw-bold">
                                <td colspan="2" class="text-end text-uppercase">Advance / Uang Muka Diterima:</td>
                                <td class="text-end font-monospace text-muted">Rp {{ number_format($adv, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                            <tr class="table-light fw-bold">
                                <td colspan="2" class="text-end text-uppercase">
                                    @if ($selisih < 0)
                                        Lebih Bayar (Pengembalian Sisa Dana ke Perusahaan):
                                    @elseif ($selisih > 0)
                                        Kurang Bayar (Reimbursement Pencairan ke Karyawan):
                                    @else
                                        Status Selisih (Pas Sesuai Nilai Advance):
                                    @endif
                                </td>
                                <td class="text-end font-monospace {{ $selisih > 0 ? 'text-danger' : ($selisih < 0 ? 'text-success' : 'text-primary') }}">
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
                <i class="fa fa-history text-primary"></i> Riwayat Aktivitas & Perjalanan Dokumen
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-custom-header">
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th style="width: 150px;">Aksi / Aktivitas</th>
                            <th style="width: 200px;">Pelaksana (Actor)</th>
                            <th>Catatan Log</th>
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

            <!-- Footer Action Buttons -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4 pt-3 border-top">
                <div class="d-flex gap-2">
                    <a href="{{ url('hrd-settlement-print?ID=' . $settlement->ID) }}" class="btn btn-info settlement-btn" target="_blank">
                        <i class="fa fa-download"></i> Cetak / Form Settlement
                    </a>
                    @if ($canClose)
                        <button class="btn btn-danger settlement-btn" id="BtnCloseSettlement">
                            <i class="fa fa-lock"></i> Close Request
                        </button>
                    @endif
                </div>
                <a href="{{ url('hrd-settlement-index') }}" class="btn btn-secondary settlement-btn">
                    <i class="fa fa-arrow-left"></i> Kembali ke List
                </a>
            </div>

        </div>
    </div>
</div>

<script>
function salinRekeningDetail(text, btn) {
    if (!text) return;
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            feedbackSalinDetail(btn);
        }).catch(function() {
            fallbackCopyDetail(text, btn);
        });
    } else {
        fallbackCopyDetail(text, btn);
    }
}

function fallbackCopyDetail(text, btn) {
    var temp = document.createElement("input");
    temp.style.position = "absolute";
    temp.style.left = "-9999px";
    temp.value = text;
    document.body.appendChild(temp);
    temp.select();
    try {
        document.execCommand("copy");
        feedbackSalinDetail(btn);
    } catch (e) {
        alert("Nomor rekening: " + text);
    }
    document.body.removeChild(temp);
}

function feedbackSalinDetail(btn) {
    var $b = $(btn);
    var oldHtml = $b.html();
    $b.html('<i class="fa fa-check text-success"></i> Tersalin!').addClass('btn-success text-white').removeClass('btn-outline-secondary');
    setTimeout(function() {
        $b.html(oldHtml).removeClass('btn-success text-white').addClass('btn-outline-secondary');
    }, 1600);
}

$('#BtnCloseSettlement').click(function (e) {
    e.preventDefault();
    if (!confirm('Apakah Anda yakin ingin menutup request settlement ini? Status akan menjadi CLOSED.')) return;

    var $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');

    $.ajax({
        url: 'hrd-settlement-close',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        data: {
            ID: '{{ $settlement->ID }}'
        },
        success: function (data) {
            $btn.prop('disabled', false).html('<i class="fa fa-lock"></i> Close Request');
            if (data.status === 'success') {
                alert('Request Settlement Berhasil Ditutup!');
                location.href = 'hrd-settlement-detail?ID={{ $settlement->ID }}';
            } else {
                alert('Gagal menutup request: ' + data.message);
            }
        },
        error: function (xhr) {
            $btn.prop('disabled', false).html('<i class="fa fa-lock"></i> Close Request');
            var msg = 'Terjadi kesalahan sistem saat menghubungi server.';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            alert(msg);
        }
    });
});
</script>
@endsection
