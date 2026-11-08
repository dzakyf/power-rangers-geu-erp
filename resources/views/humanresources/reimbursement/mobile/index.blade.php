<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reimbursement Mobile - Klaim & Biaya</title>
    <!-- Bootstrap 5 & FontAwesome -->
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 85px;
        }
        .header-card {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #fff;
            padding: 22px 18px 24px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.2);
        }
        .status-pill {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .filter-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 4px;
        }
        .filter-btn {
            font-size: 12px;
            font-weight: 600;
            border-radius: 20px;
            padding: 5px 14px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            text-decoration: none;
        }
        .filter-btn.active {
            background: #1e3a8a;
            color: #fff;
            border-color: #1e3a8a;
        }
        .rmb-item-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: transform 0.15s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .rmb-item-card:active {
            transform: scale(0.98);
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            height: 65px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1000;
        }
        .nav-item-mobile {
            text-align: center;
            color: #8e9aab;
            text-decoration: none;
            font-size: 11px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .nav-item-mobile.active {
            color: #2563eb;
            font-weight: bold;
        }
        .nav-item-mobile i {
            font-size: 20px;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>

    <!-- Header Mobile Profil & Ringkasan -->
    <div class="header-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="text-white-50 small">Selamat datang,</span>
                <h5 class="mb-0 fw-bold">{{ $user->Name ?: $user->username }}</h5>
                <small class="text-white-75">{{ $user->Emp_ID }} &bull; {{ $user->Department }}</small>
            </div>
            <div class="text-end">
                <a href="{{ route('reimbursement.mobile.form') }}" class="btn btn-light btn-sm rounded-pill fw-bold text-primary shadow-sm px-3">
                    <i class="fa fa-plus me-1"></i> Baru
                </a>
            </div>
        </div>
        <div class="bg-white bg-opacity-10 p-2 rounded-3 d-flex justify-content-between align-items-center">
            <span class="small"><i class="fa fa-receipt me-1"></i> Modul Klaim Reimbursement</span>
            <span class="badge bg-white text-dark rounded-pill">{{ count($reimbursements) }} Total</span>
        </div>
    </div>

    <!-- Filter Tab Horizontal -->
    <div class="container px-3 mt-3 mb-2">
        <div class="filter-scroll">
            <a href="{{ route('reimbursement.mobile.index', ['status' => 'ALL']) }}" class="filter-btn {{ $status == 'ALL' ? 'active' : '' }}">Semua</a>
            <a href="{{ route('reimbursement.mobile.index', ['status' => 'PENDING']) }}" class="filter-btn {{ $status == 'PENDING' ? 'active' : '' }}">Menunggu</a>
            <a href="{{ route('reimbursement.mobile.index', ['status' => 'APPROVED']) }}" class="filter-btn {{ $status == 'APPROVED' ? 'active' : '' }}">Disetujui</a>
            <a href="{{ route('reimbursement.mobile.index', ['status' => 'REJECTED']) }}" class="filter-btn {{ $status == 'REJECTED' ? 'active' : '' }}">Ditolak</a>
        </div>
    </div>

    <!-- List Pengajuan Reimbursement -->
    <div class="container px-3">
        @forelse ($reimbursements as $r)
            @php
                $st = strtoupper($r->Status ?? '');
                $stBg = 'bg-secondary text-white';
                if (in_array($st, ['APPROVED', 'CLOSED', 'COMPLETED'])) {
                    $stBg = 'bg-success text-white';
                } elseif (in_array($st, ['REJECT', 'REJECTED', 'CANCELED'])) {
                    $stBg = 'bg-danger text-white';
                } elseif (in_array($st, ['PENDING', 'WAITING_APPROVAL', 'IN_PROGRESS', 'APPROVAL_UPPER', 'APPROVAL_VP', 'APPROVAL_HCGA'])) {
                    $stBg = 'bg-warning text-dark';
                }
            @endphp
            <a href="{{ route('reimbursement.mobile.detail', ['id' => $r->ID]) }}" class="card rmb-item-card mb-3 p-3 bg-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-primary fw-bold fs-14">{{ $r->Reimbursement_No }}</span>
                        <div class="text-muted small mt-1">
                            <i class="fa fa-calendar-alt me-1"></i> {{ $r->Reimbursement_Date ? date('d M Y', strtotime($r->Reimbursement_Date)) : '-' }}
                        </div>
                    </div>
                    <span class="status-pill {{ $stBg }}">{{ $r->Status }}</span>
                </div>

                <div class="text-secondary small mb-2 text-truncate" style="max-width: 90%;">
                    <i class="fa fa-sticky-note me-1 text-muted"></i> {{ $r->Description ?: '-' }}
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="text-muted small">Total Klaim:</span>
                    <strong class="text-primary fs-6">Rp {{ number_format((float) $r->Total, 0, ',', '.') }}</strong>
                </div>

                @if($r->Attachment_File)
                <div class="mt-2 text-muted small">
                    <i class="fa fa-paperclip text-success me-1"></i> Terlampir Bukti Nota
                </div>
                @endif
            </a>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fa fa-receipt fa-3x mb-3 text-secondary opacity-50"></i>
                <p class="mb-0">Belum ada data pengajuan reimbursement.</p>
                <small>Tekan tombol "+ Baru" di atas untuk membuat pengajuan baru.</small>
            </div>
        @endforelse
    </div>

    <!-- Bottom Navigation Bar -->
    <div class="bottom-nav">
        <a href="{{ route('reimbursement.mobile.index') }}" class="nav-item-mobile active">
            <i class="fa fa-receipt"></i>
            <span>Reimbursement</span>
        </a>
        <a href="{{ route('reimbursement.mobile.form') }}" class="nav-item-mobile">
            <i class="fa fa-plus-circle text-primary" style="font-size: 26px;"></i>
            <span>Ajukan</span>
        </a>
        @if(\App\Http\Controllers\HCGA\ReimbursementApprovalController::isApprover())
        <a href="{{ route('reimbursement.mobile.approval') }}" class="nav-item-mobile">
            <i class="fa fa-check-double text-warning"></i>
            <span>Approval</span>
        </a>
        @endif
        <a href="{{ url('hrd-reimbursement-index') }}" class="nav-item-mobile">
            <i class="fa fa-desktop"></i>
            <span>Desktop</span>
        </a>
    </div>

</body>
</html>

