<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Reimbursement Mobile</title>
    <!-- Bootstrap 5 & FontAwesome -->
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 85px;
        }
        .header-card {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #fff;
            padding: 20px 18px 22px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.2);
        }
        .detail-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            margin-bottom: 14px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .detail-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 14px;
            font-weight: 700;
            font-size: 13px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .info-label-mobile {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }
        .info-value-mobile {
            font-size: 13px;
            color: #1e293b;
            font-weight: 600;
        }
        .bottom-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            padding: 12px 16px;
            box-shadow: 0 -3px 12px rgba(0,0,0,0.08);
            display: flex;
            gap: 10px;
            z-index: 1000;
        }
    </style>
</head>
<body>

    <!-- Header Detail Mobile -->
    <div class="header-card">
        <div class="d-flex align-items-center mb-2">
            <a href="{{ route('reimbursement.mobile.index') }}" class="btn btn-sm btn-outline-light rounded-circle me-2" style="width:32px; height:32px; padding:0; display:flex; align-items:center; justify-content:center;">
                <i class="fa fa-arrow-left"></i>
            </a>
            <h5 class="mb-0 fw-bold">Detail Reimbursement</h5>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-white-75 small">{{ $reimbursement->Reimbursement_No }}</span>
            @php
                $st = strtoupper($reimbursement->Status ?? '');
                $stBg = 'bg-secondary text-white';
                if (in_array($st, ['APPROVED', 'CLOSED', 'COMPLETED'])) {
                    $stBg = 'bg-success text-white';
                } elseif (in_array($st, ['REJECT', 'REJECTED', 'CANCELED'])) {
                    $stBg = 'bg-danger text-white';
                } elseif (in_array($st, ['PENDING', 'WAITING_APPROVAL', 'IN_PROGRESS', 'APPROVAL_UPPER', 'APPROVAL_VP', 'APPROVAL_HCGA'])) {
                    $stBg = 'bg-warning text-dark';
                }
            @endphp
            <span class="badge {{ $stBg }} px-3 py-1 rounded-pill">{{ $reimbursement->Status }}</span>
        </div>
    </div>

    <div class="container px-3 mt-3">

        <!-- Section 1: Informasi Dokumen & Pemohon -->
        <div class="detail-card">
            <div class="detail-header">
                <i class="fa fa-id-card text-primary"></i> 1. Informasi Pemohon
            </div>
            <div class="p-3">
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <div class="info-label-mobile">Karyawan</div>
                        <div class="info-value-mobile">{{ $reimbursement->User_Responsibility_Name }}</div>
                        <small class="text-muted">{{ $reimbursement->User_Responsibility_ID }}</small>
                    </div>
                    <div class="col-6">
                        <div class="info-label-mobile">Departemen</div>
                        <div class="info-value-mobile">{{ $reimbursement->Department ?: '-' }}</div>
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <div class="info-label-mobile">Tanggal Pengajuan</div>
                        <div class="info-value-mobile">{{ $reimbursement->Reimbursement_Date ? date('d-m-Y', strtotime($reimbursement->Reimbursement_Date)) : '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="info-label-mobile">No Referensi</div>
                        <div class="info-value-mobile">{{ $reimbursement->Reference_No ?: '-' }}</div>
                    </div>
                </div>

                <div class="mb-2">
                    <div class="info-label-mobile">Keperluan / Keterangan</div>
                    <div class="p-2 bg-light rounded text-secondary small mt-1">
                        {{ $reimbursement->Description ?: '-' }}
                    </div>
                </div>

                <div>
                    <div class="info-label-mobile">Berkas Lampiran Nota</div>
                    <div class="mt-1">
                        @if (!empty($attachment))
                            <a href="{{ url('hrd-reimbursement-attachment?ID=' . $reimbursement->ID) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" target="_blank">
                                <i class="fa fa-file-download me-1"></i> Unduh Bukti ({{ strtoupper($attachment->Extension ?? 'FILE') }})
                            </a>
                        @else
                            <span class="text-muted small"><i class="fa fa-times-circle text-danger me-1"></i> Tidak ada lampiran nota</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Rincian Pengeluaran -->
        <div class="detail-card">
            <div class="detail-header">
                <i class="fa fa-list-ol text-primary"></i> 2. Rincian Biaya
            </div>
            <div class="p-3">
                @php $total = 0; @endphp
                @forelse ($dtls as $i => $d)
                    @php $total += (float) $d->Amount; @endphp
                    <div class="p-2 border-bottom {{ $loop->last ? 'border-0' : '' }}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold small">{{ $d->Remark }}</span>
                            <strong class="text-primary fs-14">Rp {{ number_format((float) $d->Amount, 0, ',', '.') }}</strong>
                        </div>
                        @if($d->Reference)
                            <span class="badge bg-light text-dark border" style="font-size:10px;">Ref: {{ $d->Reference }}</span>
                        @endif
                    </div>
                @empty
                    <div class="text-muted small py-2 text-center">Tidak ada rincian biaya.</div>
                @endforelse

                <div class="p-2 bg-light rounded-3 d-flex justify-content-between align-items-center mt-3 border">
                    <span class="small fw-bold text-muted">TOTAL REIMBURSEMENT:</span>
                    <h5 class="mb-0 text-primary fw-bold">Rp {{ number_format($total, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>

        <!-- Section 3: Riwayat Log Status -->
        <div class="detail-card">
            <div class="detail-header">
                <i class="fa fa-history text-primary"></i> 3. Riwayat Status
            </div>
            <div class="p-3">
                @forelse ($logs as $l)
                    <div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-2 {{ $loop->last ? 'border-0 mb-0 pb-0' : '' }}">
                        <div>
                            <span class="badge bg-info text-white" style="font-size:10px;">{{ $l->Status }}</span>
                            <div class="small text-secondary mt-1">{{ $l->Description ?: '-' }}</div>
                        </div>
                        <span class="text-muted" style="font-size:11px;">
                            {{ $l->Created_Date ? date('d/m/y H:i', strtotime($l->Created_Date)) : '-' }}
                        </span>
                    </div>
                @empty
                    <div class="text-muted small text-center">Belum ada riwayat status.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Bottom Navigation / Action -->
    <div class="bottom-action-bar">
        <a href="{{ route('reimbursement.mobile.index') }}" class="btn btn-secondary w-100 fw-bold">
            <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>

</body>
</html>

