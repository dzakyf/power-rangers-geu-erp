<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Approval Reimbursement Mobile</title>
    <!-- Bootstrap 5 & FontAwesome -->
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 85px;
        }
        .header-card {
            background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
            color: #fff;
            padding: 22px 18px 24px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 15px rgba(180, 83, 9, 0.2);
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
            color: #b45309;
            font-weight: bold;
        }
        .nav-item-mobile i {
            font-size: 20px;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>

    <!-- Header Mobile Approval -->
    <div class="header-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 fw-bold"><i class="fa fa-stamp me-2"></i>Approval Reimbursement</h5>
                <small class="text-white-75">Antrean Verifikasi Klaim Biaya</small>
            </div>
            <span class="badge bg-white text-dark rounded-pill px-3 py-2 fw-bold shadow-sm">
                {{ count($queue) }} Menunggu
            </span>
        </div>
    </div>

    <!-- Container List Antrean -->
    <div class="container px-3 mt-3">
        @forelse ($queue as $r)
            <div class="card rmb-item-card mb-3 p-3 bg-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-primary fw-bold">{{ $r->Reimbursement_No }}</span>
                        <div class="text-muted small mt-1">
                            <i class="fa fa-user me-1"></i> {{ $r->User_Responsibility_Name }} ({{ $r->Department }})
                        </div>
                    </div>
                    <span class="badge bg-warning text-dark rounded-pill" style="font-size:10px;">{{ $r->Status }}</span>
                </div>

                <div class="text-secondary small mb-2 text-truncate" style="max-width:95%;">
                    {{ $r->Description ?: '-' }}
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 border-top border-bottom">
                    <span class="text-muted small">Total Nominal:</span>
                    <strong class="text-primary fs-6">Rp {{ number_format((float) $r->Total, 0, ',', '.') }}</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2 pt-1">
                    <span class="text-muted" style="font-size:11px;">
                        <i class="fa fa-layer-group me-1"></i> {{ $r->Task_Label ?: ($r->Task_Position ?: 'Tahap Approval') }}
                    </span>
                    <div class="d-flex gap-2">
                        @if($r->Attachment_File)
                        <a href="{{ url('hrd-reimbursement-attachment?ID=' . $r->ID) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0" style="font-size:11px;" target="_blank">
                            <i class="fa fa-paperclip"></i> Nota
                        </a>
                        @endif
                        <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3 py-0 fw-bold" style="font-size:12px;" onclick="bukaModalApproval({{ $r->ID }}, '{{ $r->Reimbursement_No }}', '{{ number_format((float)$r->Total, 0, ',', '.') }}');">
                            Proses &rarr;
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fa fa-check-circle fa-3x mb-3 text-success opacity-50"></i>
                <p class="mb-0 fw-bold">Semua antrean telah selesai!</p>
                <small>Tidak ada pengajuan reimbursement yang menunggu persetujuan Anda saat ini.</small>
            </div>
        @endforelse
    </div>

    <!-- Modal Keputusan Approval Mobile -->
    <div class="modal fade" id="modalApprovalMobile" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold" id="modalTitle">Verifikasi Approval</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="apprId" />
                    <p class="small text-muted mb-2">No: <b id="apprNo" class="text-dark"></b> | Total: <b id="apprTotal" class="text-primary"></b></p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Catatan Approver (Wajib jika menolak):</label>
                        <textarea id="apprNote" class="form-control form-control-sm" rows="3" placeholder="Tuliskan catatan persetujuan atau alasan revisi/penolakan..."></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-danger w-50 fw-bold" onclick="kirimKeputusan('REJECT');">
                            <i class="fa fa-times-circle me-1"></i> Tolak
                        </button>
                        <button type="button" class="btn btn-success w-50 fw-bold" onclick="kirimKeputusan('APPROVE');">
                            <i class="fa fa-check-circle me-1"></i> Setujui
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation Bar -->
    <div class="bottom-nav">
        <a href="{{ route('reimbursement.mobile.index') }}" class="nav-item-mobile">
            <i class="fa fa-receipt"></i>
            <span>Reimbursement</span>
        </a>
        <a href="{{ route('reimbursement.mobile.form') }}" class="nav-item-mobile">
            <i class="fa fa-plus-circle text-primary" style="font-size: 26px;"></i>
            <span>Ajukan</span>
        </a>
        <a href="{{ route('reimbursement.mobile.approval') }}" class="nav-item-mobile active">
            <i class="fa fa-check-double text-warning"></i>
            <span>Approval</span>
        </a>
        <a href="{{ url('hrd-reimbursement-index') }}" class="nav-item-mobile">
            <i class="fa fa-desktop"></i>
            <span>Desktop</span>
        </a>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="{{ asset('adminor/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        var apprModal;
        document.addEventListener('DOMContentLoaded', function () {
            apprModal = new bootstrap.Modal(document.getElementById('modalApprovalMobile'));
        });

        function bukaModalApproval(id, no, total) {
            $('#apprId').val(id);
            $('#apprNo').text(no);
            $('#apprTotal').text('Rp ' + total);
            $('#apprNote').val('');
            apprModal.show();
        }

        function kirimKeputusan(action) {
            var id = $('#apprId').val();
            var note = $.trim($('#apprNote').val());

            if (action === 'REJECT' && note === '') {
                alert('Cantumkan alasan penolakan pada kolom catatan!');
                $('#apprNote').focus();
                return;
            }

            var confirmText = action === 'APPROVE' ? 'Setujui reimbursement ini?' : 'Tolak reimbursement ini?';
            if (!confirm(confirmText)) return;

            $.ajax({
                url: '{{ url("hrd-reimbursement-approval-submit") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: {
                    ID: id,
                    Action: action,
                    Note: note
                },
                success: function (res) {
                    if (res.status === 'success') {
                        alert(res.message);
                        location.reload();
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
</body>
</html>

