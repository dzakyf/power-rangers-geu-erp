@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            Approval Inbox
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Approval Engine</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Inbox Persetujuan</li>
            </ol>
        </nav>
    </div>
    <div class="d-none d-sm-block">
        <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fe fe-inbox me-1"></i> Position &amp; Policy-Based Approval
        </span>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fe fe-alert-octagon me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fe fe-alert-triangle me-2"></i>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Toolbar Card --}}
<div class="card shadow-sm border mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            {{-- Action Buttons --}}
            <div class="d-flex flex-wrap align-items-center gap-1">
                <button type="button" class="btn btn-outline-info btn-sm" onclick="ApprovalInboxRefresh();" title="Refresh Daftar Task">
                    <i class="fe fe-rotate-cw me-1"></i> Refresh
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="ApprovalInboxDetail();" title="Lihat Detail Task">
                    <i class="fe fe-eye me-1"></i> Detail
                </button>
                <button type="button" class="btn btn-success btn-sm text-white" onclick="ApprovalInboxAct('APPROVE');" title="Setujui Permohonan">
                    <i class="fe fe-check me-1"></i> Approve
                </button>
                <button type="button" class="btn btn-danger btn-sm text-white" onclick="ApprovalInboxAct('REJECT');" title="Tolak Permohonan">
                    <i class="fe fe-x me-1"></i> Reject
                </button>
                <button type="button" class="btn btn-primary btn-sm text-white" onclick="ApprovalInboxClaim();" title="Claim Task agar Tidak Diambil Approver Lain">
                    <i class="fe fe-user-check me-1"></i> Claim
                </button>
                @if($isAdmin)
                <button type="button" class="btn btn-warning btn-sm text-dark" onclick="ApprovalInboxRelease();" title="Release Claim (Admin)">
                    <i class="fe fe-unlock me-1"></i> Release Claim
                </button>
                <div class="vr my-1 mx-2"></div>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-approval-escalation') }}">
                    <i class="fe fe-alert-triangle me-1"></i> Escalation Queue
                </a>
                <a class="btn btn-outline-dark btn-sm" href="{{ url('hrd-approval-policy') }}">
                    <i class="fe fe-sliders me-1"></i> Policy Management
                </a>
                <a class="btn btn-outline-info btn-sm" href="{{ url('hrd-organization-chart') }}">
                    <i class="fe fe-git-branch me-1"></i> Bagan Organisasi
                </a>
                @endif
            </div>

            {{-- Task Selection Indicator --}}
            <div class="d-flex align-items-center">
                <span class="text-muted small me-2">Task Aktif:</span>
                <span id="selectedTaskIndicator" class="badge bg-light text-dark border px-2 py-1 fs-12">
                    <i class="fe fe-mouse-pointer me-1"></i> Belum ada task dipilih
                </span>
            </div>
        </div>
    </div>
</div>

{{-- dhtmlXGrid Container --}}
<div class="card shadow-sm border mb-3">
    <div class="card-body p-0">
        <div id="ApprovalInboxGrid" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
    </div>
    <div class="card-footer bg-light py-2 px-3 d-flex justify-content-between align-items-center">
        <small class="text-muted">
            <i class="fe fe-info me-1"></i> Klik pada salah satu baris untuk memilih dan melihat detail permohonan. Anda dapat langsung menekan tombol <strong>Approve</strong>.
        </small>
        <small class="text-muted" id="inboxRowCountLabel"></small>
    </div>
</div>

{{-- Detail Container --}}
<div id="ApprovalInboxDetail" class="mb-4"></div>

{{-- ========================================================================= --}}
{{-- MODALS INTERAKTIF (PENGGANTI JAVASCRIPT PROMPT / CONFIRM / ALERT)          --}}
{{-- ========================================================================= --}}

{{-- 1. Modal Approve / Reject --}}
<div class="modal fade" id="modalApprovalAct" tabindex="-1" aria-labelledby="modalApprovalActLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header py-3" id="modalApprovalActHeader">
                <h5 class="modal-title fw-bold" id="modalApprovalActLabel">
                    <i class="fe fe-check-circle text-success me-2"></i>Konfirmasi Persetujuan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalActTaskId" />
                <input type="hidden" id="modalActAction" />

                {{-- Task Summary Box --}}
                <div class="bg-light p-3 rounded border mb-3" id="modalActSummaryBox">
                    <div class="row g-2 small">
                        <div class="col-sm-4 text-muted fw-semibold">No. Permohonan:</div>
                        <div class="col-sm-8 fw-bold text-dark" id="modalActRequestNo">-</div>
                        <div class="col-sm-4 text-muted fw-semibold">Tipe Request:</div>
                        <div class="col-sm-8" id="modalActRequestType">-</div>
                        <div class="col-sm-4 text-muted fw-semibold">Pemohon:</div>
                        <div class="col-sm-8 fw-semibold" id="modalActRequester">-</div>
                        <div class="col-sm-4 text-muted fw-semibold">Posisi Approval:</div>
                        <div class="col-sm-8" id="modalActPosition">-</div>
                    </div>
                </div>

                {{-- Notes Form --}}
                <div class="mb-2">
                    <label for="modalActNotes" class="form-label fw-semibold" id="modalActNotesLabel">
                        Catatan Persetujuan (Opsional):
                    </label>
                    <textarea class="form-control" id="modalActNotes" rows="3" placeholder="Tulis catatan atau instruksi jika ada..."></textarea>
                    <div class="form-text small" id="modalActNotesHelp">
                        Catatan ini akan tersimpan ke dalam riwayat audit log approval.
                    </div>
                    <div class="invalid-feedback" id="modalActNotesFeedback">
                        Alasan wajib diisi saat menolak permohonan.
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success btn-sm" id="modalActSubmitBtn" onclick="ApprovalInboxSubmitAct();">
                    <i class="fe fe-check me-1"></i> Ya, Setujui
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 2. Modal Claim Task --}}
<div class="modal fade" id="modalApprovalClaim" tabindex="-1" aria-labelledby="modalApprovalClaimLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header py-3">
                <h5 class="modal-title fw-bold text-primary" id="modalApprovalClaimLabel">
                    <i class="fe fe-user-check me-2"></i>Konfirmasi Claim Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalClaimTaskId" />
                <p class="mb-2">
                    Apakah Anda yakin ingin meng-claim task approval untuk permohonan <strong id="modalClaimRequestNo">-</strong>?
                </p>
                <div class="alert alert-info py-2 px-3 small mb-0">
                    <i class="fe fe-info me-1"></i> Setelah di-claim, task ini akan terkunci sebagai tugas Anda dan kandidat lain tidak dapat mengambil tindakan sampai claim di-release atau diputuskan.
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="modalClaimSubmitBtn" onclick="ApprovalInboxSubmitClaim();">
                    <i class="fe fe-user-check me-1"></i> Ya, Claim Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 3. Modal Release Claim --}}
<div class="modal fade" id="modalApprovalRelease" tabindex="-1" aria-labelledby="modalApprovalReleaseLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header py-3 bg-warning-transparent">
                <h5 class="modal-title fw-bold text-warning-dark" id="modalApprovalReleaseLabel">
                    <i class="fe fe-unlock me-2"></i>Release Claim Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalReleaseTaskId" />
                <p class="small text-muted mb-3">
                    Task akan dikembalikan ke status <strong>PENDING</strong> sehingga seluruh kandidat approver terkait dapat kembali meng-claim atau menyetujui permohonan ini.
                </p>
                <div class="mb-2">
                    <label for="modalReleaseReason" class="form-label fw-semibold">
                        Alasan Release Claim <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="modalReleaseReason" rows="3" placeholder="Tulis alasan pelepasan claim..."></textarea>
                    <div class="invalid-feedback" id="modalReleaseReasonFeedback">
                        Alasan release claim wajib diisi.
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning btn-sm text-dark" id="modalReleaseSubmitBtn" onclick="ApprovalInboxSubmitRelease();">
                    <i class="fe fe-unlock me-1"></i> Release Claim
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 4. Modal Alert Notifikasi (Pengganti alert() javascript biasa) --}}
<div class="modal fade" id="modalApprovalAlert" tabindex="-1" aria-labelledby="modalApprovalAlertLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow text-center p-3">
            <div class="modal-body py-2">
                <div id="modalAlertIconContainer" class="mb-3"></div>
                <h5 class="fw-bold mb-2" id="modalAlertTitle">Notifikasi</h5>
                <p class="text-muted small mb-0" id="modalAlertMessage"></p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <button type="button" class="btn btn-primary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
var ApprovalInboxToken = @json(csrf_token());
var currentApprovalTaskId = null;

// Bootstrap Modal Instances
var bsModalAct = null;
var bsModalClaim = null;
var bsModalRelease = null;
var bsModalAlert = null;

document.addEventListener('DOMContentLoaded', function () {
    bsModalAct = new bootstrap.Modal(document.getElementById('modalApprovalAct'));
    bsModalClaim = new bootstrap.Modal(document.getElementById('modalApprovalClaim'));
    bsModalRelease = new bootstrap.Modal(document.getElementById('modalApprovalRelease'));
    bsModalAlert = new bootstrap.Modal(document.getElementById('modalApprovalAlert'));
});

/**
 * Tampilkan Notifikasi Modal interaktif (pengganti alert js)
 */
function showAppNotification(title, message, type) {
    type = type || 'info';
    var iconHtml = '';

    if (type === 'success') {
        iconHtml = '<div class="avatar avatar-xl bg-success-transparent text-success rounded-circle mx-auto"><i class="fe fe-check fs-24"></i></div>';
    } else if (type === 'danger' || type === 'error') {
        iconHtml = '<div class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle mx-auto"><i class="fe fe-alert-octagon fs-24"></i></div>';
    } else if (type === 'warning') {
        iconHtml = '<div class="avatar avatar-xl bg-warning-transparent text-warning rounded-circle mx-auto"><i class="fe fe-alert-triangle fs-24"></i></div>';
    } else {
        iconHtml = '<div class="avatar avatar-xl bg-info-transparent text-info rounded-circle mx-auto"><i class="fe fe-info fs-24"></i></div>';
    }

    document.getElementById('modalAlertIconContainer').innerHTML = iconHtml;
    document.getElementById('modalAlertTitle').innerText = title;
    document.getElementById('modalAlertMessage').innerText = message;

    if (bsModalAlert) {
        bsModalAlert.show();
    }
}

/**
 * Refresh Grid dan pertahankan task terpilih jika masih ada
 */
function ApprovalInboxRefresh() {
    ApprovalInbox.clearAndLoad('hrd-approval-inbox-data');
}

/**
 * Mendapatkan Task ID yang sedang aktif/dipilih secara cerdas:
 * 1. Pengecekan row terpilih di dhtmlXGrid
 * 2. Fallback ke currentApprovalTaskId
 * 3. Fallback ke dataset ApprovalTaskDetailCard
 */
function ApprovalInboxSelectedId(explicitId) {
    if (explicitId) {
        currentApprovalTaskId = explicitId;
        return explicitId;
    }

    var id = ApprovalInbox.getSelectedRowId();
    if (id) {
        currentApprovalTaskId = id;
        return id;
    }

    if (currentApprovalTaskId) {
        return currentApprovalTaskId;
    }

    var detailCard = document.getElementById('ApprovalTaskDetailCard');
    if (detailCard && detailCard.dataset.taskId) {
        currentApprovalTaskId = detailCard.dataset.taskId;
        return currentApprovalTaskId;
    }

    showAppNotification('Pilih Baris', 'Silakan pilih salah satu baris task approval pada tabel terlebih dahulu.', 'warning');
    return null;
}

/**
 * Update indikator visual task yang aktif
 */
function updateSelectedTaskIndicator(id) {
    var indicator = document.getElementById('selectedTaskIndicator');
    if (!indicator) return;

    if (!id) {
        indicator.className = 'badge bg-light text-dark border px-2 py-1 fs-12';
        indicator.innerHTML = '<i class="fe fe-mouse-pointer me-1"></i> Belum ada task dipilih';
        return;
    }

    var reqNo = '-';
    try {
        if (ApprovalInbox.getRowIndex(id) !== -1) {
            reqNo = ApprovalInbox.cells(id, 1).getValue() || ('#' + id);
        }
    } catch (e) {}

    indicator.className = 'badge bg-primary text-white px-2 py-1 fs-12 shadow-sm';
    indicator.innerHTML = '<i class="fe fe-check-circle me-1"></i> Task #' + id + ' (' + reqNo + ')';
}

/**
 * Muat view detail task
 */
function ApprovalInboxDetail(explicitId) {
    var id = ApprovalInboxSelectedId(explicitId);
    if (id == null) return false;

    updateSelectedTaskIndicator(id);
    LoadM('ApprovalInboxDetail', 'hrd-approval-detail?ID=' + encodeURIComponent(id));
    return false;
}

/**
 * Tutup view detail task
 */
function ApprovalInboxCloseDetail() {
    document.getElementById('ApprovalInboxDetail').innerHTML = '';
}

/**
 * Eksekusi AJAX Request dengan fetch
 */
function ApprovalInboxPost(url, data, onSuccess, buttonElement) {
    var originalButtonHtml = '';
    if (buttonElement) {
        originalButtonHtml = buttonElement.innerHTML;
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Memproses...';
    }

    var body = new URLSearchParams();
    body.append('_token', ApprovalInboxToken);
    Object.keys(data).forEach(function (key) {
        body.append(key, data[key]);
    });

    fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-CSRF-TOKEN': ApprovalInboxToken,
            'Accept': 'application/json'
        },
        body: body.toString()
    }).then(function (response) {
        return response.json().then(function (payload) {
            return { ok: response.ok, payload: payload };
        });
    }).then(function (result) {
        if (buttonElement) {
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalButtonHtml;
        }

        if (!result.ok || result.payload.status === 'error') {
            showAppNotification('Gagal', result.payload.message || 'Aksi approval tidak dapat diproses.', 'danger');
            return;
        }

        showAppNotification('Berhasil', result.payload.message || 'Aksi approval berhasil diselesaikan.', 'success');
        ApprovalInboxRefresh();
        if (typeof onSuccess === 'function') onSuccess(result.payload);
    }).catch(function () {
        if (buttonElement) {
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalButtonHtml;
        }
        showAppNotification('Error Jaringan', 'Tidak dapat terhubung ke server. Silakan coba kembali.', 'danger');
    });
}

/**
 * Buka Modal Konfirmasi Claim
 */
function ApprovalInboxClaim(explicitId) {
    var id = ApprovalInboxSelectedId(explicitId);
    if (id == null) return false;

    var reqNo = '#' + id;
    try {
        if (ApprovalInbox.getRowIndex(id) !== -1) {
            reqNo = ApprovalInbox.cells(id, 1).getValue() || reqNo;
        }
    } catch (e) {}

    document.getElementById('modalClaimTaskId').value = id;
    document.getElementById('modalClaimRequestNo').innerText = reqNo;

    if (bsModalClaim) {
        bsModalClaim.show();
    }
    return false;
}

/**
 * Submit Claim dari Modal
 */
function ApprovalInboxSubmitClaim() {
    var taskId = document.getElementById('modalClaimTaskId').value;
    var btn = document.getElementById('modalClaimSubmitBtn');

    if (!taskId) return;

    ApprovalInboxPost('hrd-approval-claim', { Task_ID: taskId }, function () {
        if (bsModalClaim) bsModalClaim.hide();
        ApprovalInboxDetail(taskId);
    }, btn);
}

/**
 * Buka Modal Approve atau Reject (Menggantikan JS Prompt)
 */
function ApprovalInboxAct(action, explicitId) {
    var id = ApprovalInboxSelectedId(explicitId);
    if (id == null) return false;

    action = (action || 'APPROVE').toUpperCase();

    // Data dari Grid atau Detail Card
    var reqNo = '-' ;
    var reqType = '-';
    var requester = '-';
    var position = '-';

    try {
        if (ApprovalInbox.getRowIndex(id) !== -1) {
            reqNo = ApprovalInbox.cells(id, 1).getValue() || reqNo;
            reqType = ApprovalInbox.cells(id, 2).getValue() || reqType;
            position = ApprovalInbox.cells(id, 3).getValue() || position;
            requester = ApprovalInbox.cells(id, 8).getValue() || requester;
        }
    } catch (e) {}

    var detailCard = document.getElementById('ApprovalTaskDetailCard');
    if (detailCard && detailCard.dataset.taskId == id) {
        reqNo = detailCard.dataset.requestNo || reqNo;
        reqType = detailCard.dataset.requestType || reqType;
        requester = detailCard.dataset.requester || requester;
        position = detailCard.dataset.position || position;
    }

    // Set Data ke Modal
    document.getElementById('modalActTaskId').value = id;
    document.getElementById('modalActAction').value = action;
    document.getElementById('modalActRequestNo').innerText = reqNo;
    document.getElementById('modalActRequestType').innerText = reqType;
    document.getElementById('modalActRequester').innerText = requester;
    document.getElementById('modalActPosition').innerText = position;

    var notesInput = document.getElementById('modalActNotes');
    notesInput.value = '';
    notesInput.classList.remove('is-invalid');

    var modalLabel = document.getElementById('modalApprovalActLabel');
    var notesLabel = document.getElementById('modalActNotesLabel');
    var notesHelp = document.getElementById('modalActNotesHelp');
    var submitBtn = document.getElementById('modalActSubmitBtn');

    if (action === 'APPROVE') {
        modalLabel.innerHTML = '<i class="fe fe-check-circle text-success me-2"></i>Konfirmasi Persetujuan (Approve)';
        notesLabel.innerHTML = 'Catatan Persetujuan <span class="text-muted fw-normal">(Opsional)</span>:';
        notesHelp.innerHTML = 'Catatan atau instruksi persetujuan jika diperlukan.';
        submitBtn.className = 'btn btn-success btn-sm';
        submitBtn.innerHTML = '<i class="fe fe-check me-1"></i> Ya, Setujui Permohonan';
    } else {
        modalLabel.innerHTML = '<i class="fe fe-x-circle text-danger me-2"></i>Konfirmasi Penolakan (Reject)';
        notesLabel.innerHTML = 'Alasan Penolakan <span class="text-danger">*</span>:';
        notesHelp.innerHTML = 'Harap tuliskan alasan atau kekurangan dokumen secara jelas.';
        submitBtn.className = 'btn btn-danger btn-sm';
        submitBtn.innerHTML = '<i class="fe fe-x me-1"></i> Ya, Tolak Permohonan';
    }

    if (bsModalAct) {
        bsModalAct.show();
    }
    return false;
}

/**
 * Submit Keputusan Approval / Reject dari Modal
 */
function ApprovalInboxSubmitAct() {
    var taskId = document.getElementById('modalActTaskId').value;
    var action = document.getElementById('modalActAction').value;
    var notesInput = document.getElementById('modalActNotes');
    var notes = notesInput.value.trim();
    var btn = document.getElementById('modalActSubmitBtn');

    // Validasi catatan jika reject
    if (action === 'REJECT' && notes === '') {
        notesInput.classList.add('is-invalid');
        notesInput.focus();
        return;
    }
    notesInput.classList.remove('is-invalid');

    ApprovalInboxPost('hrd-approval-act', { Task_ID: taskId, Action: action, Notes: notes }, function () {
        if (bsModalAct) bsModalAct.hide();
        // Bersihkan detail karena task sudah disetujui / ditolak
        ApprovalInboxCloseDetail();
        currentApprovalTaskId = null;
        updateSelectedTaskIndicator(null);
    }, btn);
}

/**
 * Buka Modal Release Claim (Menggantikan JS Prompt)
 */
function ApprovalInboxRelease(explicitId) {
    var id = ApprovalInboxSelectedId(explicitId);
    if (id == null) return false;

    document.getElementById('modalReleaseTaskId').value = id;
    var reasonInput = document.getElementById('modalReleaseReason');
    reasonInput.value = '';
    reasonInput.classList.remove('is-invalid');

    if (bsModalRelease) {
        bsModalRelease.show();
    }
    return false;
}

/**
 * Submit Release Claim dari Modal
 */
function ApprovalInboxSubmitRelease() {
    var taskId = document.getElementById('modalReleaseTaskId').value;
    var reasonInput = document.getElementById('modalReleaseReason');
    var reason = reasonInput.value.trim();
    var btn = document.getElementById('modalReleaseSubmitBtn');

    if (reason === '') {
        reasonInput.classList.add('is-invalid');
        reasonInput.focus();
        return;
    }
    reasonInput.classList.remove('is-invalid');

    ApprovalInboxPost('hrd-approval-release', { Task_ID: taskId, Reason: reason }, function () {
        if (bsModalRelease) bsModalRelease.hide();
        ApprovalInboxDetail(taskId);
    }, btn);
}

// Inisialisasi dhtmlXGrid
var ApprovalInbox = new dhtmlXGridObject('ApprovalInboxGrid');
ApprovalInbox.setImagePath("./dhtmlx/imgs/");
ApprovalInbox.setHeader("No,Request No,Type,Position,Stage,Level,Mode,Task Status,Requester,Location,Submitted");
ApprovalInbox.attachHeader("&nbsp;,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#select_filter,#text_filter");
ApprovalInbox.setInitWidths("50,150,130,220,60,60,110,130,150,100,150");
ApprovalInbox.setColAlign("center,center,center,left,center,center,center,center,left,center,center");
ApprovalInbox.setColSorting("int,str,str,str,int,int,str,str,str,str,str");
ApprovalInbox.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
ApprovalInbox.enableSmartRendering(true, 150);
ApprovalInbox.setSkin("dhx_skyblue");
ApprovalInbox.init();

// Event saat baris di-klik: simpan task aktif dan langsung muat detail
ApprovalInbox.attachEvent("onRowSelect", function (id, ind) {
    currentApprovalTaskId = id;
    updateSelectedTaskIndicator(id);
    ApprovalInboxDetail(id);
});

// Event saat data selesai dimuat: pulihkan seleksi jika task masih ada
ApprovalInbox.attachEvent("onXLE", function () {
    if (currentApprovalTaskId) {
        try {
            if (ApprovalInbox.getRowIndex(currentApprovalTaskId) !== -1) {
                ApprovalInbox.selectRowById(currentApprovalTaskId);
                updateSelectedTaskIndicator(currentApprovalTaskId);
            }
        } catch (e) {}
    }
});

// Muat data awal
ApprovalInbox.clearAndLoad('hrd-approval-inbox-data');
</script>
@endsection
