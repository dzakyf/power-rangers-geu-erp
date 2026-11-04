<?php

use Illuminate\Support\Facades\Auth;

$LSP = $location ?? Auth::user()->Location;
$Saya = $username ?? Auth::user()->username;

?>

@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<style>
#RequisitionLists img,
.gridbox_dhx_skyblue img {
    width: auto !important;
    max-width: none !important;
    height: auto !important;
}
.quick-action-btn {
    transition: all 0.15s ease;
    font-weight: 500;
}
.quick-action-btn:disabled {
    opacity: 0.45 !important;
    cursor: not-allowed;
}
</style>

{{-- Simple Header --}}
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-3 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.3rem;">
            Approval Permintaan Tenaga Kerja (PTK)
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Recruitment</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Approval PTK</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge px-3 py-2 fs-12" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.35); color: #ffffff !important;">
            <i class="fa fa-inbox me-1"></i> Antrean: <strong id="badgeQueueCount">0</strong> PTK
        </span>
    </div>
</div>

{{-- Main Grid & Action Card (Satu Card Bersih & Fokus) --}}
<div class="card shadow-sm border mb-3">
    {{-- Top Toolbar --}}
    <div class="card-header bg-white py-2 px-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm quick-action-btn" onclick="refreshdata()" title="Refresh Data">
                <i class="fa fa-rotate me-1"></i> Refresh
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm quick-action-btn" id="btnActionDetail" onclick="showApprovalDetail(null, true)" disabled>
                <i class="fa fa-eye me-1"></i> Lihat Detail PTK
            </button>
            <button type="button" id="ekselRequisitionListsDet" class="btn btn-outline-success btn-sm quick-action-btn" onclick="RequisitionLists.toExcel('./export/generate.php');">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </button>
        </div>

        <div class="d-flex align-items-center gap-2 small">
            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="toggleCheckAllPtk(true)">Pilih Semua</button>
            <span class="text-muted">&bull;</span>
            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" onclick="toggleCheckAllPtk(false)">Reset Centang</button>
            <span class="badge bg-light text-dark border ms-2 px-2 py-1">
                Dicentang: <strong id="checkedCountText" class="text-primary">0</strong>
            </span>
        </div>
    </div>

    {{-- Grid Table --}}
    <div class="card-body p-0">
        <div id="RequisitionLists" style="width:100%;height:360px;background-color:white;overflow:hidden"></div>
    </div>

    {{-- Compact Approval Decision Footer --}}
    <div class="card-footer bg-light p-3 border-top">
        <div class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white fw-semibold text-dark">
                        <i class="fa fa-comment-dots text-primary me-1"></i> Catatan (Notes) <span class="text-danger ms-1">*</span>
                    </span>
                    <input type="text" id="txtRequisitionListsDet" name="txtRequisitionListsDet" class="form-control" placeholder="Ketik catatan persetujuan atau alasan penolakan (min. 4 karakter)...">
                </div>
            </div>
            <div class="col-md-4 d-flex justify-content-md-end gap-2">
                <button type="button" id="delets" class="btn btn-danger btn-sm px-3 fw-bold flex-fill flex-md-grow-0" onclick="delsRequisition()">
                    <i class="fa fa-xmark me-1"></i> Reject / Cancel
                </button>
                <button type="button" id="approvePTK" class="btn btn-success btn-sm px-3 fw-bold text-white flex-fill flex-md-grow-0" onclick="closereq()">
                    <i class="fa fa-check me-1"></i> Approve PTK
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Detail Container (Hanya muncul saat tombol "Lihat Detail PTK" diklik atau baris di-double click) --}}
<div id="divlistRequisition" class="mb-4"></div>

{{-- Modal Notifikasi --}}
<div class="modal fade" id="modalApprovalPtkAlert" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow border-0 text-center p-3">
            <div class="modal-body py-2">
                <div id="modalApprovalPtkAlertIconContainer" class="mb-3"></div>
                <h5 class="fw-bold mb-2 text-dark" id="modalApprovalPtkAlertTitle">Notifikasi</h5>
                <p class="text-muted small mb-0" id="modalApprovalPtkAlertMessage"></p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <button type="button" class="btn btn-primary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
var currentApprovalRowId = null;
var bsModalApprovalAlert = null;
var RequisitionLists;
var PRequisitionLists;

document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('modalApprovalPtkAlert');
    if (modalEl && typeof bootstrap !== 'undefined') {
        bsModalApprovalAlert = new bootstrap.Modal(modalEl);
    }
    initApprovalGrid();
});

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

    var iconContainer = document.getElementById('modalApprovalPtkAlertIconContainer');
    var titleEl = document.getElementById('modalApprovalPtkAlertTitle');
    var msgEl = document.getElementById('modalApprovalPtkAlertMessage');

    if (iconContainer) iconContainer.innerHTML = iconHtml;
    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;

    if (bsModalApprovalAlert) {
        bsModalApprovalAlert.show();
    } else {
        alert(message);
    }
}

function updateCheckedCounter() {
    if (!RequisitionLists) return;
    var checkedStr = RequisitionLists.getCheckedRows(0);
    var count = checkedStr ? checkedStr.split(',').filter(Boolean).length : 0;
    var elChecked = document.getElementById('checkedCountText');
    if (elChecked) elChecked.innerText = count;
}

function toggleCheckAllPtk(checkState) {
    if (!RequisitionLists) return;
    var val = checkState ? 1 : 0;
    RequisitionLists.forEachRow(function (id) {
        RequisitionLists.cells(id, 0).setValue(val);
        PRequisitionLists.setUpdated(id, checkState, "updated");
    });
    updateCheckedCounter();
}

function initApprovalGrid() {
    RequisitionLists = new dhtmlXGridObject('RequisitionLists');
    RequisitionLists.setImagePath("./dhtmlx/imgs/");
    RequisitionLists.setHeader("Checklist,No.,No. PTK,Subject,Posisi Jabatan,Deskripsi,Requirement,Departemen,Prioritas,Qty,Aging (Hari),Tgl Mulai,Tgl Selesai,Referensi,PIC,Status");
    RequisitionLists.attachHeader("#rspan,#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter");
    RequisitionLists.setInitWidths("65,50,145,150,150,150,150,110,90,55,85,95,95,100,100,110");
    RequisitionLists.setColAlign("center,center,left,left,left,left,left,center,center,center,center,center,center,center,left,center");
    RequisitionLists.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro,coro,ro,ro,ro,ro,ro,combo,ro");
    RequisitionLists.setColSorting("int,int,str,str,str,str,str,str,str,int,int,str,str,str,str,str");
    RequisitionLists.setEditable(true);
    RequisitionLists.setSkin("dhx_skyblue");

    PRequisitionLists = new dataProcessor("requisition-approval-update");
    PRequisitionLists.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    PRequisitionLists.setUpdateMode("Off");
    PRequisitionLists.init(RequisitionLists);

    RequisitionLists.init();

    PRequisitionLists.attachEvent("onBeforeDataSending", function(id, state, data) {
        var notes = $('#txtRequisitionListsDet').val();
        data.customPayload = {
            notes: notes
        };
        return true;
    });

    PRequisitionLists.attachEvent("onAfterUpdateFinish", function(id, state, data) {
        showAppNotification('Berhasil Diproses', 'Keputusan approval PTK berhasil disimpan.', 'success');
        $('#txtRequisitionListsDet').val('');
        $('#divlistRequisition').html('');
        setTimeout(function() {
            refreshdata();
        }, 800);
    });

    RequisitionLists.getCombo(8).put("NORMAL", "NORMAL");
    RequisitionLists.getCombo(8).put("HIGH", "HIGH");
    RequisitionLists.getCombo(8).put("URGENT", "URGENT");

    var combocurr = RequisitionLists.getColumnCombo(14);
    if (combocurr) {
        combocurr.enableFilteringMode(true);
        combocurr.load("xml-load-pic-production");
    }

    RequisitionLists.attachEvent("onCheck", function(rId, cInd, state) {
        updateCheckedCounter();
        return true;
    });

    // Klik biasa hanya memilih baris & mengaktifkan tombol "Lihat Detail PTK" (tidak otomatis membuka panel bawah agar tidak ramai)
    RequisitionLists.attachEvent("onRowSelect", function(id, ind) {
        currentApprovalRowId = id;
        var btnDetail = document.getElementById('btnActionDetail');
        if (btnDetail) btnDetail.disabled = false;
    });

    // Double click baru membuka panel detail
    RequisitionLists.attachEvent("onRowDblClicked", function(id, ind) {
        currentApprovalRowId = id;
        showApprovalDetail(id, true);
        return true;
    });

    RequisitionLists.attachEvent("onXLE", function() {
        var totalRows = RequisitionLists.getRowsNum();
        var badgeQueue = document.getElementById('badgeQueueCount');
        if (badgeQueue) badgeQueue.innerText = totalRows;
        updateCheckedCounter();
    });

    refreshdata();
}

function refreshdata() {
    if (RequisitionLists) {
        RequisitionLists.clearAndLoad('xml-recruitment-requisition-approval');
    }
}

function showApprovalDetail(explicitId, autoScroll) {
    var id = explicitId || (RequisitionLists ? RequisitionLists.getSelectedId() : null) || currentApprovalRowId;
    if (!id) {
        showAppNotification('Pilih Baris', 'Silakan klik salah satu baris PTK pada tabel terlebih dahulu.', 'warning');
        return false;
    }

    currentApprovalRowId = id;

    var container = document.getElementById('divlistRequisition');
    container.innerHTML = '<div class="card shadow-sm border p-3 text-center my-2">' +
        '<div class="spinner-border spinner-border-sm text-primary mx-auto mb-1" role="status"></div>' +
        '<div class="text-muted small">Memuat detail PTK...</div>' +
    '</div>';

    LoadM('divlistRequisition', 'recruitment-requisition-detail?id=' + encodeURIComponent(id));

    if (autoScroll) {
        setTimeout(function () {
            var detailEl = document.getElementById('divlistRequisition');
            if (detailEl) {
                detailEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 250);
    }

    return false;
}

function closereq() {
    var checkedRows = RequisitionLists.getCheckedRows(0);
    if (!checkedRows) {
        showAppNotification('Checklist Kosong', 'Silakan centang minimal satu dokumen PTK pada kolom Checklist untuk di-Approve.', 'warning');
        return;
    }

    var notes = ($('#txtRequisitionListsDet').val() || '').trim();
    if (notes.length <= 3) {
        $('#txtRequisitionListsDet').addClass('is-invalid').focus();
        showAppNotification('Catatan Wajib Diisi', 'Mohon isi catatan persetujuan (minimal 4 karakter) sebelum melakukan Approve.', 'warning');
        return;
    }
    $('#txtRequisitionListsDet').removeClass('is-invalid');

    dhtmlx.confirm({
        title: "Konfirmasi Approve PTK",
        type: "confirm-warning",
        text: "Setujui (APPROVE) dokumen PTK yang telah dicentang?",
        callback: function(r) {
            if (r === true) {
                checkedRows.split(',').forEach(function(rowId) {
                    if (rowId) {
                        PRequisitionLists.setUpdated(rowId, true, "updated");
                    }
                });
                PRequisitionLists.sendData();
            }
        }
    });
}

function delsRequisition() {
    var checkedRows = RequisitionLists.getCheckedRows(0);
    if (!checkedRows) {
        showAppNotification('Checklist Kosong', 'Silakan centang minimal satu dokumen PTK pada kolom Checklist yang ingin ditolak/dibatalkan.', 'warning');
        return;
    }

    var notes = ($('#txtRequisitionListsDet').val() || '').trim();
    if (notes.length <= 3) {
        $('#txtRequisitionListsDet').addClass('is-invalid').focus();
        showAppNotification('Alasan Penolakan Wajib Diisi', 'Mohon isi alasan penolakan/pembatalan (minimal 4 karakter) pada kolom Catatan.', 'warning');
        return;
    }
    $('#txtRequisitionListsDet').removeClass('is-invalid');

    dhtmlx.confirm({
        title: "Konfirmasi Reject / Cancel PTK",
        type: "confirm-error",
        text: "Tolak / Batalkan (REJECT) dokumen PTK yang telah dicentang?",
        callback: function(r) {
            if (r === true) {
                checkedRows.split(',').forEach(function(rowId) {
                    if (rowId) {
                        PRequisitionLists.setUpdated(rowId, true, "deleted");
                    }
                });
                PRequisitionLists.sendData();
            }
        }
    });
}
</script>
@endsection
