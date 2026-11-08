@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    @csrf

    <style>
        .rmb-page-wrapper {
            margin-bottom: 2.5rem;
        }

        .rmb-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 14px rgba(0, 0, 0, 0.04);
            background: #ffffff;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .rmb-card-header {
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .rmb-card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .rmb-section-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            margin-bottom: 1.25rem;
            overflow: hidden;
        }

        .rmb-section-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.9rem 1.25rem;
            font-weight: 700;
            font-size: 0.95rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .rmb-btn {
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.5rem 1.1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.2s ease;
        }

        .rmb-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .info-label {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 0.95rem;
            color: #1e293b;
            font-weight: 600;
        }

        .table-expense thead th,
        .table-history thead th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .total-badge-card {
            background: #f8fafc;
            border-left: 4px solid #2563eb;
            padding: 1rem 1.25rem;
            border-radius: 8px;
        }
    </style>

    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fa fa-tasks text-warning me-2"></i> Verifikasi Approval Reimbursement : {{ $reimbursement->Reimbursement_No }}
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Human Capital</a></li>
                    <li class="breadcrumb-item"><a href="#">Kompensasi & Benefit</a></li>
                    <li class="breadcrumb-item"><a href="hrd-reimbursement-approval-index">Approval Reimbursement</a></li>
                    <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Verifikasi</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2">
            @php
                $status = strtoupper($reimbursement->Status ?? '');
                $badgeClass = 'bg-secondary';
                if (in_array($status, ['APPROVED', 'CLOSED', 'COMPLETED'])) {
                    $badgeClass = 'bg-success text-white';
                } elseif (in_array($status, ['REJECT', 'REJECTED', 'CANCELED'])) {
                    $badgeClass = 'bg-danger text-white';
                } elseif (in_array($status, ['PENDING', 'WAITING_APPROVAL', 'SUBMITTED', 'IN_PROGRESS', 'REVIEW', 'APPROVAL_UPPER', 'APPROVAL_VP', 'APPROVAL_HCGA'])) {
                    $badgeClass = 'bg-warning text-dark';
                }
            @endphp
            <span class="badge {{ $badgeClass }} px-3 py-2 fs-6 shadow-sm">
                <i class="fa fa-info-circle me-1"></i> {{ $reimbursement->Status }}
            </span>
            <a href="hrd-reimbursement-approval-index" class="btn btn-outline-secondary rmb-btn">
                <i class="fa fa-arrow-left"></i> Kembali ke Antrean
            </a>
        </div>
    </div>

    <div class="rmb-page-wrapper">
        <div class="card rmb-card">
            <div class="rmb-card-header">
                <div>
                    <h3 class="rmb-card-title">
                        <i class="fa fa-file-invoice-dollar text-primary"></i> Lembar Verifikasi Pengajuan Reimbursement
                    </h3>
                    <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Periksa rincian biaya pengeluaran pemohon sebelum memberikan persetujuan atau penolakan.</small>
                </div>
            </div>

            <div class="card-body p-4">

                <!-- Section 1: Informasi Dokumen & Pemohon -->
                <div class="rmb-section-card shadow-sm">
                    <div class="rmb-section-header">
                        <i class="fa fa-id-card text-primary"></i> 1. Informasi Dokumen & Pemohon
                    </div>
                    <div class="p-3 p-md-4">
                        <div class="row g-4">
                            <div class="col-md-4 col-sm-6">
                                <div class="info-label">No Reimbursement</div>
                                <div class="info-value text-primary">{{ $reimbursement->Reimbursement_No }}</div>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <div class="info-label">Tanggal Pengajuan</div>
                                <div class="info-value">
                                    <i class="fa fa-calendar-alt text-muted me-1"></i>
                                    {{ $reimbursement->Reimbursement_Date ? date('d-m-Y', strtotime($reimbursement->Reimbursement_Date)) : '-' }}
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <div class="info-label">Departemen</div>
                                <div class="info-value">{{ $reimbursement->Department ?: '-' }}</div>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <div class="info-label">Karyawan Pemohon</div>
                                <div class="info-value">
                                    {{ $reimbursement->User_Responsibility_Name ?: '-' }}
                                    <span class="text-muted small">({{ $reimbursement->User_Responsibility_ID ?: '-' }})</span>
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <div class="info-label">No Referensi / Dokumen Terkait</div>
                                <div class="info-value">
                                    @if (!empty($reimbursement->Reference_No))
                                        <span class="badge bg-light text-dark border"><i class="fa fa-paperclip me-1"></i>
                                            {{ $reimbursement->Reference_No }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <div class="info-label">Waktu Submit Sistem</div>
                                <div class="info-value">
                                    {{ $reimbursement->submitted_date ? date('d-m-Y H:i', strtotime($reimbursement->submitted_date)) : ($reimbursement->Created_Date ? date('d-m-Y H:i', strtotime($reimbursement->Created_Date)) : '-') }}
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <div class="info-label">Berkas / Bukti Kwitansi</div>
                                <div class="info-value">
                                    @if (!empty($attachment))
                                        <a href="hrd-reimbursement-attachment?ID={{ $reimbursement->ID }}" class="btn btn-sm btn-outline-primary rmb-btn" target="_blank">
                                            <i class="fa fa-file-download"></i> Unduh Lampiran ({{ strtoupper($attachment->Extension ?? 'FILE') }})
                                        </a>
                                    @else
                                        <span class="text-muted"><i class="fa fa-times-circle text-danger me-1"></i> Tidak ada lampiran</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="info-label">Deskripsi / Keperluan Reimbursement</div>
                                <div class="p-3 bg-light rounded border text-secondary" style="font-size: 0.92rem; line-height: 1.5;">
                                    {{ $reimbursement->Description ?: '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Rincian Biaya -->
                <div class="rmb-section-card shadow-sm">
                    <div class="rmb-section-header">
                        <i class="fa fa-list-ol text-primary"></i> 2. Rincian Pengeluaran / Biaya
                    </div>
                    <div class="p-3 p-md-4">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-expense align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="25%">Reference / No Bukti</th>
                                        <th width="45%">Keterangan Pengeluaran</th>
                                        <th width="25%" class="text-end">Jumlah Nominal (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $total = 0; @endphp
                                    @forelse ($dtls as $i => $d)
                                        @php $total += (float) $d->Amount; @endphp
                                        <tr>
                                            <td class="text-center">{{ $i + 1 }}</td>
                                            <td>
                                                @if ($d->Reference)
                                                    <span class="badge bg-light text-dark border">{{ $d->Reference }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $d->Remark }}</td>
                                            <td class="text-end fw-semibold">
                                                {{ number_format((float) $d->Amount, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">Tidak ada data rincian biaya.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <div class="total-badge-card shadow-sm" style="min-width: 320px;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-semibold small">TOTAL REIMBURSEMENT:</span>
                                    <h4 class="mb-0 text-primary fw-bold">Rp {{ number_format($total, 0, ',', '.') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: History & Log Perubahan Status -->
                <div class="rmb-section-card shadow-sm">
                    <div class="rmb-section-header">
                        <i class="fa fa-history text-primary"></i> 3. Riwayat Progress & Log Approval
                    </div>
                    <div class="p-3 p-md-4">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-history align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="20%">Transaksi / Action</th>
                                        <th width="20%">Status</th>
                                        <th width="35%">Keterangan</th>
                                        <th width="20%">Waktu / Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($logs as $i => $l)
                                        <tr>
                                            <td class="text-center">{{ $i + 1 }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $l->Transaction }}</span></td>
                                            <td><span class="badge bg-info text-white">{{ $l->Status }}</span></td>
                                            <td>{{ $l->Description ?: '-' }}</td>
                                            <td class="text-muted small">
                                                <i class="fa fa-clock me-1"></i>
                                                {{ $l->Created_Date ? date('d-m-Y H:i:s', strtotime($l->Created_Date)) : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">Belum ada catatan riwayat verifikasi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Form Aksi Persetujuan -->
                @if ($canApprove)
                <div class="rmb-section-card shadow-sm border-warning">
                    <div class="rmb-section-header bg-warning text-dark">
                        <i class="fa fa-check-double"></i> 4. Keputusan & Catatan Approval
                    </div>
                    <div class="p-3 p-md-4">
                        <div class="mb-3">
                            <label for="ApprovalNote" class="form-label fw-bold text-dark">Catatan Approver (Wajib jika menolak):</label>
                            <textarea id="ApprovalNote" class="form-control" rows="3" placeholder="Tuliskan catatan persetujuan atau alasan revisi/penolakan..."></textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                            <button type="button" class="btn btn-danger rmb-btn px-4 shadow-sm" onclick="prosesApproval('REJECT');">
                                <i class="fa fa-times-circle"></i> Tolak (Reject)
                            </button>
                            <button type="button" class="btn btn-success rmb-btn px-4 shadow-sm" onclick="prosesApproval('APPROVE');">
                                <i class="fa fa-check-circle"></i> Setujui (Approve)
                            </button>
                        </div>
                    </div>
                </div>
                @else
                <div class="alert alert-info d-flex align-items-center gap-2">
                    <i class="fa fa-info-circle fa-lg"></i>
                    <div>
                        Dokumen ini saat ini tidak memerlukan tindakan approval dari Anda, atau sudah berada dalam status final (<strong>{{ $reimbursement->Status }}</strong>).
                    </div>
                </div>
                @endif

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2">
                    <a href="hrd-reimbursement-approval-index" class="btn btn-secondary rmb-btn">
                        <i class="fa fa-arrow-left"></i> Kembali ke Antrean
                    </a>
                </div>

            </div>
        </div>
    </div>

<script>
function prosesApproval(action) {
    var note = $.trim($('#ApprovalNote').val());
    if (action === 'REJECT' && note === '') {
        alert('Mohon cantumkan alasan penolakan pada kolom catatan!');
        $('#ApprovalNote').focus();
        return;
    }

    var confirmMsg = action === 'APPROVE' ? 'Apakah Anda yakin ingin MENYETUJUI pengajuan reimbursement ini?' : 'Apakah Anda yakin ingin MENOLAK pengajuan reimbursement ini?';
    if (!confirm(confirmMsg)) return;

    $.ajax({
        url: 'hrd-reimbursement-approval-submit',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        data: {
            ID: "{{ $reimbursement->ID }}",
            Action: action,
            Note: note
        },
        success: function (res) {
            if (res.status === 'success') {
                alert(res.message);
                location.href = 'hrd-reimbursement-approval-index';
            } else {
                alert('Gagal: ' + res.message);
            }
        },
        error: function (xhr) {
            var msg = 'Terjadi kesalahan sistem';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            alert(msg);
        }
    });
}
</script>
@endsection

