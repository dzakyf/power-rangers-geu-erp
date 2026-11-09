<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$LSP = $location ?? Auth::user()->Location;
$Saya = $username ?? Auth::user()->username;

// Fallback jika dirender langsung tanpa lewat controller
if (!isset($requisitionPolicies)) {
    $requisitionPolicies = DB::table('approval_policy')
        ->where('Request_Type', 'REQUISITION')
        ->orderByRaw("CASE WHEN Status = 'ACTIVE' THEN 0 ELSE 1 END")
        ->orderBy('ID', 'asc')
        ->get();

    foreach ($requisitionPolicies as $pol) {
        $pol->steps = DB::table('approval_policy_step')
            ->where('Policy_ID', $pol->ID)
            ->orderBy('Stage_Order', 'asc')
            ->orderBy('Level_Number', 'asc')
            ->orderBy('ID', 'asc')
            ->get();
    }
}

?>

@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<style>
/* Custom Requisition UX styling (Reference: Recruitment Interview UI) */
#RequisitionLists img,
.gridbox_dhx_skyblue img {
    width: auto !important;
    max-width: none !important;
    height: auto !important;
}
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
#RequisitionLists {
    border-radius: 4px;
}

/* Minimalist Approval Stepper */
.approval-stepper-wrapper {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.approval-step-item {
    display: inline-flex;
    align-items: center;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 0.35rem 0.75rem;
    font-size: 0.78rem;
    transition: all 0.15s ease;
}
.approval-step-item .step-num {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #0d6efd;
    color: #fff;
    font-weight: 700;
    font-size: 0.7rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.5rem;
}
.approval-step-connector {
    color: #adb5bd;
    font-size: 0.75rem;
}
</style>

{{-- Modern Breadcrumb Header --}}
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            Permintaan Tenaga Kerja (PTK)
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Recruitment</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Job Requisition (PTK)</li>
            </ol>
        </nav>
    </div>
    <div class="d-none d-sm-flex align-items-center gap-2">
        <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-location-dot me-1"></i> Site: {{ $LSP }}
        </span>
    </div>
</div>

{{-- Quick Status Metrics --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-info-transparent text-info rounded-3 me-3">
                    <i class="fa fa-file-contract fs-20"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Total Dokumen PTK</h6>
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
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Proses Approval</h6>
                    <h4 class="fw-bold mb-0 text-dark" id="metricCountPending">-</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-success-transparent text-success rounded-3 me-3">
                    <i class="fa fa-circle-check fs-20"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Sudah Disetujui</h6>
                    <h4 class="fw-bold mb-0 text-dark" id="metricCountDone">-</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-secondary-transparent text-secondary rounded-3 me-3">
                    <i class="fa fa-arrow-pointer fs-20"></i>
                </div>
                <div class="overflow-hidden">
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">PTK Terpilih</h6>
                    <div id="metricSelectedPtk" class="fw-bold text-dark text-truncate fs-13" title="Belum dipilih">Belum dipilih</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Approval Policy & Stepper Card (Visualisasi Progress Step dari approval_policy & approval_policy_step) --}}
<div class="card shadow-sm border mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold text-dark fs-13">
                    <i class="fa fa-diagram-project text-primary me-1"></i> Alur Tahapan Persetujuan PTK (Approval Policy Steps)
                </span>
                @if(count($requisitionPolicies) > 0)
                    <select id="policyStepSelector" class="form-select form-select-sm py-0 ps-2 pe-4" style="width: auto; height: 26px; font-size: 0.76rem;" onchange="switchPolicyStepper(this.value)">
                        @foreach($requisitionPolicies as $idx => $pol)
                            <option value="policy-step-group-{{ $pol->ID }}" {{ $idx === 0 ? 'selected' : '' }}>
                                {{ $pol->Policy_Name }} ({{ $pol->Status }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
            <span class="text-muted small">
                <i class="fa fa-circle-check text-success me-1"></i> Otomatis mengikuti level jabatan &amp; departemen
            </span>
        </div>

        @if(count($requisitionPolicies) > 0)
            @foreach($requisitionPolicies as $idx => $pol)
                <div id="policy-step-group-{{ $pol->ID }}" class="policy-stepper-group {{ $idx === 0 ? '' : 'd-none' }}">
                    <div class="approval-stepper-wrapper">
                        <div class="approval-step-item bg-white">
                            <span class="step-num bg-secondary"><i class="fa fa-pen" style="font-size: 0.6rem;"></i></span>
                            <div>
                                <div class="fw-semibold text-dark lh-1">Pemohon (Creator)</div>
                                <small class="text-muted" style="font-size: 0.68rem;">Submit Form PTK</small>
                            </div>
                        </div>

                        @foreach($pol->steps as $step)
                            <span class="approval-step-connector"><i class="fa fa-chevron-right"></i></span>
                            <div class="approval-step-item bg-white">
                                <span class="step-num">{{ $step->Stage_Order }}</span>
                                <div>
                                    <div class="fw-semibold text-dark lh-1">
                                        {{ $step->Label ?: ($step->Position_Code ?: 'Level ' . $step->Level_Number) }}
                                    </div>
                                    <small class="text-muted" style="font-size: 0.68rem;">
                                        Stage {{ $step->Stage_Order }} &bull; Level {{ $step->Level_Number }}
                                        {{ !empty($step->Position_Code) ? ' (' . $step->Position_Code . ')' : '' }}
                                    </small>
                                </div>
                            </div>
                        @endforeach

                        <span class="approval-step-connector"><i class="fa fa-chevron-right"></i></span>
                        <div class="approval-step-item bg-success-transparent border-success border-opacity-25">
                            <span class="step-num bg-success"><i class="fa fa-check" style="font-size: 0.6rem;"></i></span>
                            <div>
                                <div class="fw-semibold text-success lh-1">APPROVED</div>
                                <small class="text-muted" style="font-size: 0.68rem;">Siap Rekrutmen</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-muted small py-1">
                <i class="fa fa-info-circle me-1"></i> Policy approval REQUISITION belum dikonfigurasi pada tabel <code>approval_policy</code>.
            </div>
        @endif
    </div>
</div>

{{-- Main Actions Toolbar Card --}}
<div class="card shadow-sm border mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            {{-- Action Buttons --}}
            <div class="d-flex flex-wrap align-items-center gap-1">
                <button type="button" class="btn btn-outline-secondary btn-sm quick-action-btn" onclick="refreshdata();" title="Refresh Daftar PTK">
                    <i class="fa fa-rotate me-1"></i> Refresh
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm quick-action-btn" id="btnActionDetail" onclick="showDetailRequisition(null, true);" title="Buka Detail &amp; Progress Approval PTK" disabled>
                    <i class="fa fa-eye me-1"></i> Buka Detail
                </button>
                <button type="button" class="btn btn-outline-dark btn-sm quick-action-btn" id="btnActionPrint" onclick="printSelectedRequisition();" title="Cetak PDF Form PTK Terpilih" disabled>
                    <i class="fa fa-print me-1"></i> Cetak PDF
                </button>
                <button type="button" class="btn btn-primary btn-sm quick-action-btn" onclick="newreq();" title="Buat Pengajuan Form PTK Baru">
                    <i class="fa fa-plus me-1"></i> Form PTK Baru
                </button>
                <div class="vr my-1 mx-1 text-muted opacity-50"></div>
                <button type="button" id="ekselRequisitionListsDet" name="ekselRequisitionListsDet" class="btn btn-success btn-sm text-white quick-action-btn" onclick="RequisitionLists.toExcel('./export/generate.php');" title="Export data tabel ke file Excel">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm quick-action-btn" id="btnActionCancel" onclick="delsRequisition();" title="Batalkan PTK Terpilih" disabled>
                    <i class="fa fa-ban me-1"></i> Cancel PTK
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm quick-action-btn" id="btnActionClose" onclick="closereq();" title="Tutup PTK Terpilih" disabled>
                    <i class="fa fa-lock me-1"></i> Close PTK
                </button>
            </div>

            {{-- PTK Selection Indicator Bar --}}
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small d-none d-md-inline">Status Pilihan:</span>
                <span id="selectedPtkIndicator" class="badge bg-light text-dark border px-3 py-2 fs-12 fw-normal">
                    <i class="fa fa-arrow-pointer text-muted me-1"></i> Belum ada PTK dipilih
                </span>
            </div>
        </div>
    </div>
</div>

{{-- dhtmlXGrid Container --}}
<div class="card shadow-sm border mb-3">
    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
        <span class="fw-semibold text-dark fs-13">
            <i class="fa fa-list text-primary me-1"></i> Daftar Dokumen Permintaan Tenaga Kerja (PTK)
        </span>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-light text-muted border fw-normal">
                <i class="fa fa-filter me-1"></i> Ketik pada kolom tabel untuk memfilter
            </span>
            <small class="text-muted" id="ptkRowCountLabel">Memuat data...</small>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="RequisitionLists" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
    </div>
    <div class="card-footer bg-light py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <small class="text-muted">
            <i class="fa fa-circle-info text-info me-1"></i> <strong>Tips UX:</strong> Klik pada baris tabel untuk otomatis melihat rincian PTK &amp; progress approval di bawah, atau klik <strong>Buka Detail</strong>.
        </small>
        <small class="text-muted">
            Double-click pada baris untuk scroll langsung ke panel detail.
        </small>
    </div>
</div>

{{-- Detail & Form Container --}}
<div id="divlistRequisition" class="mb-4"></div>

{{-- Modal Notifikasi Interaktif --}}
<div class="modal fade" id="modalPtkAlert" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow border-0 text-center p-3">
            <div class="modal-body py-2">
                <div id="modalPtkAlertIconContainer" class="mb-3"></div>
                <h5 class="fw-bold mb-2" id="modalPtkAlertTitle">Notifikasi</h5>
                <p class="text-muted small mb-0" id="modalPtkAlertMessage"></p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <button type="button" class="btn btn-primary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
var currentRequisitionId = null;
var bsModalPtkAlert = null;
var RequisitionLists;
var PRequisitionLists;

document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('modalPtkAlert');
    if (modalEl && typeof bootstrap !== 'undefined') {
        bsModalPtkAlert = new bootstrap.Modal(modalEl);
    }
    updateActionButtonState(null);
    initRequisitionGrid();
});

/**
 * Switch tampilan policy stepper
 */
function switchPolicyStepper(groupId) {
    var groups = document.querySelectorAll('.policy-stepper-group');
    groups.forEach(function (el) {
        el.classList.add('d-none');
    });
    var target = document.getElementById(groupId);
    if (target) {
        target.classList.remove('d-none');
    }
}

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

    var iconContainer = document.getElementById('modalPtkAlertIconContainer');
    var titleEl = document.getElementById('modalPtkAlertTitle');
    var msgEl = document.getElementById('modalPtkAlertMessage');

    if (iconContainer) iconContainer.innerHTML = iconHtml;
    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;

    if (bsModalPtkAlert) {
        bsModalPtkAlert.show();
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
    var btnPrint = document.getElementById('btnActionPrint');
    var btnCancel = document.getElementById('btnActionCancel');
    var btnClose = document.getElementById('btnActionClose');

    if (btnDetail) btnDetail.disabled = !hasSelection;
    if (btnPrint) btnPrint.disabled = !hasSelection;
    if (btnCancel) btnCancel.disabled = !hasSelection;
    if (btnClose) btnClose.disabled = !hasSelection;
}

function printSelectedRequisition() {
    var rowId = currentRequisitionId || RequisitionLists.getSelectedRowId();
    if (!rowId) {
        showAppNotification('Pilih PTK', 'Silakan pilih dokumen PTK pada tabel terlebih dahulu.', 'warning');
        return;
    }
    var ptkNo = '';
    try {
        ptkNo = RequisitionLists.cells(rowId, 1).getValue() || '';
    } catch (e) {}
    var url = ptkNo
        ? ('hrd-requisition-prints?Task=' + encodeURIComponent(ptkNo))
        : ('hrd-requisition-prints?ID=' + encodeURIComponent(rowId));
    window.open(url, '_blank');
}

/**
 * Update indikator visual PTK yang dipilih
 */
function updateSelectedPtkIndicator(id) {
    var indicator = document.getElementById('selectedPtkIndicator');
    var metricSelected = document.getElementById('metricSelectedPtk');

    updateActionButtonState(id);

    if (!id) {
        if (indicator) {
            indicator.className = 'badge bg-light text-dark border px-3 py-2 fs-12 fw-normal shadow-none';
            indicator.innerHTML = '<i class="fa fa-arrow-pointer text-muted me-1"></i> Belum ada PTK dipilih';
        }
        if (metricSelected) {
            metricSelected.innerText = 'Belum dipilih';
            metricSelected.title = 'Belum dipilih';
        }
        return;
    }

    var ptkNo = '#' + id;
    var jobPos = '';
    var dept = '';
    try {
        if (RequisitionLists.getRowIndex(id) !== -1) {
            ptkNo = RequisitionLists.cells(id, 1).getValue() || ptkNo;
            jobPos = RequisitionLists.cells(id, 2).getValue() || '';
            dept = RequisitionLists.cells(id, 5).getValue() || '';
        }
    } catch (e) {}

    if (indicator) {
        indicator.className = 'badge bg-primary text-white px-3 py-2 fs-12 fw-normal shadow-sm';
        indicator.innerHTML = '<i class="fa fa-file-circle-check me-1"></i> ' + (jobPos ? (jobPos + ' - ' + ptkNo) : ptkNo);
    }
    if (metricSelected) {
        metricSelected.innerText = jobPos ? (jobPos + ' (' + ptkNo + ')') : ptkNo;
        metricSelected.title = jobPos ? (jobPos + ' [' + dept + '] - ' + ptkNo) : ptkNo;
    }
}

/**
 * Hitung metrik ringkasan PTK setelah data grid dimuat
 */
function calculatePtkMetrics() {
    if (!RequisitionLists) return;
    var totalRows = RequisitionLists.getRowsNum();
    var pendingCount = 0;
    var doneCount = 0;

    for (var i = 0; i < totalRows; i++) {
        var rowId = RequisitionLists.getRowId(i);
        var status = (RequisitionLists.cells(rowId, 13).getValue() || '').toUpperCase().trim();
        if (status === 'APPROVED') {
            doneCount++;
        } else {
            pendingCount++;
        }
    }

    var elTotal = document.getElementById('metricCountTotal');
    var elPending = document.getElementById('metricCountPending');
    var elDone = document.getElementById('metricCountDone');
    var rowCountLabel = document.getElementById('ptkRowCountLabel');

    if (elTotal) elTotal.innerText = totalRows;
    if (elPending) elPending.innerText = pendingCount;
    if (elDone) elDone.innerText = doneCount;
    if (rowCountLabel) rowCountLabel.innerText = 'Total ' + totalRows + ' dokumen PTK';
}

/**
 * Inisialisasi dhtmlXGrid
 */
function initRequisitionGrid() {
    RequisitionLists = new dhtmlXGridObject('RequisitionLists');
    RequisitionLists.setImagePath("./dhtmlx/imgs/");
    RequisitionLists.setHeader("No.,No. PTK,Posisi Jabatan,Deskripsi,Requirement,Departemen,Prioritas,Qty,Aging (Hari),Tgl Mulai,Tgl Selesai,Referensi,PIC,Status,Catatan");
    RequisitionLists.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter");
    RequisitionLists.setInitWidths("50,145,150,160,160,115,90,60,90,95,95,110,110,115,160");
    RequisitionLists.setColAlign("center,left,left,left,left,center,center,center,center,center,center,center,left,center,left");
    RequisitionLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    RequisitionLists.setColSorting("int,str,str,str,str,str,str,int,int,str,str,str,str,str,str");
    RequisitionLists.enableSmartRendering(true, 150);
    RequisitionLists.enableKeyboardSupport(true);
    RequisitionLists.setSkin("dhx_skyblue");

    PRequisitionLists = new dataProcessor("requisition-submit");
    PRequisitionLists.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	});
    PRequisitionLists.init(RequisitionLists);

    RequisitionLists.init();

    // Single click event: update indicator & load detail
    RequisitionLists.attachEvent("onRowSelect", function (id, ind) {
        currentRequisitionId = id;
        updateSelectedPtkIndicator(id);
        showDetailRequisition(id, false);
    });

    // Double click event: buka detail & scroll
    RequisitionLists.attachEvent("onRowDblClicked", function (id, ind) {
        currentRequisitionId = id;
        updateSelectedPtkIndicator(id);
        showDetailRequisition(id, true);
        return true;
    });

    // onXLE event: hitung metrik & pulihkan seleksi
    RequisitionLists.attachEvent("onXLE", function () {
        calculatePtkMetrics();

        if (currentRequisitionId) {
            try {
                if (RequisitionLists.getRowIndex(currentRequisitionId) !== -1) {
                    RequisitionLists.selectRowById(currentRequisitionId);
                    updateSelectedPtkIndicator(currentRequisitionId);
                } else {
                    currentRequisitionId = null;
                    updateSelectedPtkIndicator(null);
                }
            } catch (e) {}
        } else {
            updateSelectedPtkIndicator(null);
        }
    });

    refreshdata();
}

function refreshdata() {
    var rowCountLabel = document.getElementById('ptkRowCountLabel');
    if (rowCountLabel) {
        rowCountLabel.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyinkronkan...';
    }
    if (RequisitionLists) {
        RequisitionLists.clearAndLoad('xml-requisition-lists');
    }
}

function getSelectedRequisitionId(explicitId) {
    if (explicitId) {
        currentRequisitionId = explicitId;
        return explicitId;
    }
    var id = RequisitionLists ? RequisitionLists.getSelectedId() : null;
    if (id) {
        currentRequisitionId = id;
        return id;
    }
    if (currentRequisitionId) {
        return currentRequisitionId;
    }
    showAppNotification('Pilih Baris', 'Silakan klik salah satu baris PTK pada tabel terlebih dahulu.', 'warning');
    return null;
}

function showDetailRequisition(explicitId, autoScroll) {
    var id = getSelectedRequisitionId(explicitId);
    if (id == null) return false;

    updateSelectedPtkIndicator(id);

    var container = document.getElementById('divlistRequisition');
    container.innerHTML = '<div class="card shadow-sm border p-4 text-center my-3">' +
        '<div class="spinner-border text-primary mx-auto mb-2" role="status"></div>' +
        '<div class="text-muted small">Memuat rincian dokumen PTK &amp; tahapan persetujuan...</div>' +
    '</div>';

    LoadM('divlistRequisition', 'recruitment-requisition-detail?id=' + encodeURIComponent(id));

    if (autoScroll) {
        setTimeout(function () {
            var detailEl = document.getElementById('divlistRequisition');
            if (detailEl) {
                detailEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 300);
    }

    return false;
}

function newreq() {
    var container = document.getElementById('divlistRequisition');
    container.innerHTML = '<div class="card shadow-sm border p-4 text-center my-3">' +
        '<div class="spinner-border text-primary mx-auto mb-2" role="status"></div>' +
        '<div class="text-muted small">Membuka formulir pengajuan PTK baru...</div>' +
    '</div>';

    LoadM('divlistRequisition', 'requisition-new');

    setTimeout(function () {
        var detailEl = document.getElementById('divlistRequisition');
        if (detailEl) {
            detailEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 300);
}

function closereq() {
    var selectedId = getSelectedRequisitionId();
    if (!selectedId) return;

    var taskNo = RequisitionLists.cells(selectedId, 1).getValue();

    dhtmlx.confirm({
        title: "Konfirmasi Close PTK",
        type: "confirm-warning",
        text: "Tutup (CLOSE) dokumen PTK <strong>" + taskNo + "</strong>?",
        callback: function(r) {
            if (r === true) {
                RequisitionLists.cells(selectedId, 13).setValue('CLOSED');
                PRequisitionLists.setUpdated(selectedId, true);
                PRequisitionLists.sendData();
                showAppNotification('Berhasil Ditutup', 'Status PTK ' + taskNo + ' telah diubah menjadi CLOSED.', 'success');
                setTimeout(function() {
                    refreshdata();
                }, 800);
            }
        }
    });
}

function delsRequisition() {
    var selectedId = getSelectedRequisitionId();
    if (!selectedId) return;

    var taskNo = RequisitionLists.cells(selectedId, 1).getValue();

    dhtmlx.confirm({
        title: "Konfirmasi Cancel PTK",
        type: "confirm-error",
        text: "Batalkan (CANCEL) dokumen PTK <strong>" + taskNo + "</strong>?",
        callback: function(r) {
            if (r === true) {
                RequisitionLists.deleteSelectedRows();
                PRequisitionLists.sendData();
                showAppNotification('Berhasil Dibatalkan', 'Dokumen PTK ' + taskNo + ' berhasil dibatalkan.', 'success');
                setTimeout(function() {
                    refreshdata();
                }, 800);
            }
        }
    });
}
</script>
@endsection
