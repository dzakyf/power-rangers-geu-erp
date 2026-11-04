@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<style>
/* Custom Psikotest UX styling (Reference: Candidate Analysis UI) */
.approval-metric-card {
    transition: all 0.2s ease-in-out;
    border-radius: 8px;
    border: 1px solid rgba(0,0,0,0.06);
}
.approval-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.status-pill-badge {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.25rem 0.6rem;
    border-radius: 50rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}
.quick-action-btn {
    transition: all 0.15s ease;
    font-weight: 500;
}
.quick-action-btn:hover:not(:disabled) {
    transform: translateY(-1px);
}
.quick-action-btn:disabled {
    opacity: 0.45 !important;
    cursor: not-allowed;
}
#RecruitmentLists {
    border-radius: 4px;
}
</style>

{{-- Modern Breadcrumb Header --}}
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            Psikotest Rekrutmen (Psychological Test)
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Recruitment</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Psikotest Kandidat</li>
            </ol>
        </nav>
    </div>
    <div class="d-none d-sm-flex align-items-center gap-2">
        <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-brain me-1"></i> Stage: PSIKOTEST Active
        </span>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="fa fa-triangle-exclamation me-2"></i>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Quick Status Metrics --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-info-transparent text-info rounded-3 me-3">
                    <i class="fa fa-users fs-20"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Total Antrean Psikotest</h6>
                    <h4 class="fw-bold mb-0 text-dark" id="metricCountTotal">-</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-warning-transparent text-warning rounded-3 me-3">
                    <i class="fa fa-clock fs-20"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Menunggu Psikotest</h6>
                    <h4 class="fw-bold mb-0 text-dark" id="metricCountPending">-</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-success-transparent text-success rounded-3 me-3">
                    <i class="fa fa-user-check fs-20"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Sudah Dievaluasi</h6>
                    <h4 class="fw-bold mb-0 text-dark" id="metricCountDone">-</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-primary-transparent text-primary rounded-3 me-3">
                    <i class="fa fa-arrow-pointer fs-20"></i>
                </div>
                <div class="overflow-hidden">
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Kandidat Terpilih</h6>
                    <div id="metricSelectedCandidate" class="fw-bold text-dark text-truncate fs-13" title="Belum dipilih">Belum dipilih</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main Actions Toolbar Card --}}
<div class="card shadow-sm border mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            {{-- Action Buttons --}}
            <div class="d-flex flex-wrap align-items-center gap-1">
                <button type="button" class="btn btn-outline-secondary btn-sm quick-action-btn" onclick="RecruitmentListsRefresh();" title="Refresh Daftar Kandidat">
                    <i class="fa fa-rotate me-1"></i> Refresh
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm quick-action-btn" id="btnActionDetail" onclick="RecruitmentListsDetail(null, true);" title="Buka Detail &amp; Form Hasil Psikotest" disabled>
                    <i class="fa fa-eye me-1"></i> Buka Detail
                </button>
                <div class="vr my-1 mx-1 text-muted opacity-50"></div>
                <button type="button" id="ekselRecruitmentListsDet" name="ekselRecruitmentListsDet" class="btn btn-success btn-sm text-white quick-action-btn" onclick="RecruitmentLists.toExcel('./export/generate.php');" title="Export data tabel ke file Excel">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
            </div>

            {{-- Candidate Selection Indicator Bar --}}
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small d-none d-md-inline">Status Pilihan:</span>
                <span id="selectedCandidateIndicator" class="badge bg-light text-dark border px-3 py-2 fs-12 fw-normal">
                    <i class="fa fa-arrow-pointer text-muted me-1"></i> Belum ada kandidat dipilih
                </span>
            </div>
        </div>
    </div>
</div>

{{-- dhtmlXGrid Container --}}
<div class="card shadow-sm border mb-3">
    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
        <span class="fw-semibold text-dark fs-13">
            <i class="fa fa-list text-primary me-1"></i> Daftar Antrean Kandidat Tahap Psikotest
        </span>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-light text-muted border fw-normal" id="gridFilterHelper">
                <i class="fa fa-filter me-1"></i> Ketik pada kolom tabel untuk memfilter
            </span>
            <small class="text-muted" id="psikotestRowCountLabel">Memuat data...</small>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="RecruitmentLists" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
    </div>
    <div class="card-footer bg-light py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <small class="text-muted">
            <i class="fa fa-circle-info text-info me-1"></i> <strong>Tips UX:</strong> Klik pada baris tabel untuk melihat profil kandidat &amp; form penilaian psikotest di bawah, atau klik <strong>Buka Detail</strong>.
        </small>
        <small class="text-muted">
            Double-click pada baris untuk scroll langsung ke panel formulir.
        </small>
    </div>
</div>

{{-- Detail Container --}}
<div id="divlistRecruitment" class="mb-4"></div>

{{-- Modal Notifikasi Interaktif --}}
<div class="modal fade" id="modalPsikotestAlert" tabindex="-1" aria-labelledby="modalPsikotestAlertLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow border-0 text-center p-3">
            <div class="modal-body py-2">
                <div id="modalPsikotestAlertIconContainer" class="mb-3"></div>
                <h5 class="fw-bold mb-2 text-dark" id="modalPsikotestAlertTitle">Notifikasi</h5>
                <p class="text-muted small mb-0" id="modalPsikotestAlertMessage"></p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <button type="button" class="btn btn-primary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
var currentRecruitmentId = null;
var bsModalPsikotestAlert = null;

document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('modalPsikotestAlert');
    if (modalEl && typeof bootstrap !== 'undefined') {
        bsModalPsikotestAlert = new bootstrap.Modal(modalEl);
    }
    updateActionButtonState(null);
});

/**
 * Tampilkan Notifikasi Modal interaktif
 */
function showAppNotification(title, message, type) {
    type = type || 'info';
    var iconHtml = '';

    if (type === 'success') {
        iconHtml = '<div class="avatar avatar-xl bg-success-transparent text-success rounded-circle mx-auto"><i class="fa fa-check fs-24"></i></div>';
    } else if (type === 'danger' || type === 'error') {
        iconHtml = '<div class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle mx-auto"><i class="fa fa-exclamation-circle fs-24"></i></div>';
    } else if (type === 'warning') {
        iconHtml = '<div class="avatar avatar-xl bg-warning-transparent text-warning rounded-circle mx-auto"><i class="fa fa-triangle-exclamation fs-24"></i></div>';
    } else {
        iconHtml = '<div class="avatar avatar-xl bg-info-transparent text-info rounded-circle mx-auto"><i class="fa fa-circle-info fs-24"></i></div>';
    }

    var iconContainer = document.getElementById('modalPsikotestAlertIconContainer');
    var titleEl = document.getElementById('modalPsikotestAlertTitle');
    var msgEl = document.getElementById('modalPsikotestAlertMessage');

    if (iconContainer) iconContainer.innerHTML = iconHtml;
    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;

    if (bsModalPsikotestAlert) {
        bsModalPsikotestAlert.show();
    } else {
        alert(message);
    }
}

/**
 * Update Status Tombol Aksi
 */
function updateActionButtonState(id) {
    var hasSelection = (id !== null && id !== undefined && id !== '');
    var btnDetail = document.getElementById('btnActionDetail');
    if (btnDetail) btnDetail.disabled = !hasSelection;
}

/**
 * Refresh Grid dan pertahankan seleksi jika masih ada
 */
function RecruitmentListsRefresh() {
    var rowCountLabel = document.getElementById('psikotestRowCountLabel');
    if (rowCountLabel) {
        rowCountLabel.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyinkronkan...';
    }
    RecruitmentLists.clearAndLoad('xml-hrd-recruitment-psikotest');
}

// Alias agar script lama di detail tetap berfungsi jika memanggil refreshdata()
function refreshdata() {
    RecruitmentListsRefresh();
}

/**
 * Hitung metrik ringkasan antrean psikotest
 */
function calculatePsikotestMetrics() {
    var totalRows = RecruitmentLists.getRowsNum();
    var pendingCount = 0;
    var doneCount = 0;

    for (var i = 0; i < totalRows; i++) {
        var rowId = RecruitmentLists.getRowId(i);
        var status = (RecruitmentLists.cells(rowId, 9).getValue() || '').toUpperCase();
        if (status.indexOf('ACCEPT') !== -1 || status.indexOf('REJECT') !== -1 || status.indexOf('INTERVIEW') !== -1 || status.indexOf('PASS') !== -1) {
            doneCount++;
        } else {
            pendingCount++;
        }
    }

    var elTotal = document.getElementById('metricCountTotal');
    var elPending = document.getElementById('metricCountPending');
    var elDone = document.getElementById('metricCountDone');
    var rowCountLabel = document.getElementById('psikotestRowCountLabel');

    if (elTotal) elTotal.innerText = totalRows;
    if (elPending) elPending.innerText = pendingCount;
    if (elDone) elDone.innerText = doneCount;
    if (rowCountLabel) rowCountLabel.innerText = 'Total ' + totalRows + ' antrean kandidat';
}

/**
 * Mendapatkan ID Kandidat yang sedang dipilih
 */
function RecruitmentListsSelectedId(explicitId) {
    if (explicitId) {
        currentRecruitmentId = explicitId;
        return explicitId;
    }

    var id = RecruitmentLists.getSelectedId();
    if (id) {
        currentRecruitmentId = id;
        return id;
    }

    if (currentRecruitmentId) {
        return currentRecruitmentId;
    }

    showAppNotification('Pilih Baris', 'Silakan klik salah satu baris kandidat pada tabel terlebih dahulu.', 'warning');
    return null;
}

/**
 * Update indikator visual kandidat aktif
 */
function updateSelectedCandidateIndicator(id) {
    var indicator = document.getElementById('selectedCandidateIndicator');
    var metricSelected = document.getElementById('metricSelectedCandidate');

    updateActionButtonState(id);

    if (!id) {
        if (indicator) {
            indicator.className = 'badge bg-light text-dark border px-3 py-2 fs-12 fw-normal shadow-none';
            indicator.innerHTML = '<i class="fa fa-arrow-pointer text-muted me-1"></i> Belum ada kandidat dipilih';
        }
        if (metricSelected) {
            metricSelected.innerText = 'Belum dipilih';
            metricSelected.title = 'Belum dipilih';
        }
        return;
    }

    var taskId = '#' + id;
    var candName = '';
    var jobPos = '';
    try {
        if (RecruitmentLists.getRowIndex(id) !== -1) {
            taskId = RecruitmentLists.cells(id, 1).getValue() || taskId;
            candName = RecruitmentLists.cells(id, 2).getValue() || '';
            jobPos = RecruitmentLists.cells(id, 5).getValue() || '';
        }
    } catch (e) {}

    if (indicator) {
        indicator.className = 'badge bg-primary text-white px-3 py-2 fs-12 fw-normal shadow-sm';
        indicator.innerHTML = '<i class="fa fa-user-check me-1"></i> ' + (candName ? candName : taskId) + ' (' + taskId + ')';
    }
    if (metricSelected) {
        metricSelected.innerText = candName ? (candName + ' (' + taskId + ')') : taskId;
        metricSelected.title = candName ? (candName + ' - ' + jobPos) : taskId;
    }
}

/**
 * Muat view detail psikotest via AJAX
 */
function RecruitmentListsDetail(explicitId, autoScroll) {
    var id = RecruitmentListsSelectedId(explicitId);
    if (id == null) return false;

    updateSelectedCandidateIndicator(id);

    var container = document.getElementById('divlistRecruitment');
    container.innerHTML = '<div class="card shadow-sm border p-4 text-center my-3">' +
        '<div class="spinner-border text-primary mx-auto mb-2" role="status"></div>' +
        '<div class="text-muted small">Memuat rincian profil kandidat &amp; formulir evaluasi psikotest...</div>' +
    '</div>';

    LoadM('divlistRecruitment', 'hrd-recruitment-psikotest-detail?ID=' + encodeURIComponent(id));

    if (autoScroll) {
        setTimeout(function () {
            var detailEl = document.getElementById('divlistRecruitment');
            if (detailEl) {
                detailEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 300);
    }

    return false;
}

/**
 * Tutup view detail psikotest
 */
function RecruitmentListsCloseDetail() {
    document.getElementById('divlistRecruitment').innerHTML = '';
}

// Inisialisasi dhtmlXGrid
var RecruitmentLists = new dhtmlXGridObject('RecruitmentLists');
RecruitmentLists.setImagePath("./dhtmlx/imgs/");
RecruitmentLists.setHeader("No.,Task ID,Nama Kandidat,Gender,Tgl Lahir,Posisi / Requisisi,Pendidikan,No. Referensi,Keterangan Referensi,Status,Sisa Waktu (Hari)");
RecruitmentLists.attachHeader("#rspan,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#select_filter,#text_filter");
RecruitmentLists.setInitWidths("50,140,180,90,110,180,120,130,160,120,110");
RecruitmentLists.setColAlign("center,left,left,center,center,left,center,left,left,center,center");
RecruitmentLists.setColSorting("int,str,str,str,date,str,str,str,str,str,int");
RecruitmentLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
RecruitmentLists.enableSmartRendering(true, 150);
RecruitmentLists.enableKeyboardSupport(true);
RecruitmentLists.setSkin("dhx_skyblue");
RecruitmentLists.init();

// Single click event
RecruitmentLists.attachEvent("onRowSelect", function (id, ind) {
    currentRecruitmentId = id;
    updateSelectedCandidateIndicator(id);
    RecruitmentListsDetail(id, false);
});

// Double click event
RecruitmentLists.attachEvent("onRowDblClicked", function (id, ind) {
    currentRecruitmentId = id;
    updateSelectedCandidateIndicator(id);
    RecruitmentListsDetail(id, true);
    return true;
});

// onXLE event
RecruitmentLists.attachEvent("onXLE", function () {
    calculatePsikotestMetrics();

    if (currentRecruitmentId) {
        try {
            if (RecruitmentLists.getRowIndex(currentRecruitmentId) !== -1) {
                RecruitmentLists.selectRowById(currentRecruitmentId);
                updateSelectedCandidateIndicator(currentRecruitmentId);
            } else {
                currentRecruitmentId = null;
                updateSelectedCandidateIndicator(null);
            }
        } catch (e) {}
    } else {
        updateSelectedCandidateIndicator(null);
    }
});

// Muat data awal
RecruitmentListsRefresh();
</script>
@endsection
