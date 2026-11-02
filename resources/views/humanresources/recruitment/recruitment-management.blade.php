@extends('layout.mainlayout-hrd')
@section('content-wrapper')
@csrf

<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Human Resources</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Recruitment</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Dashboard Progress</span>
        </li>
    </ol>
</div>

<!-- Info Box: Alur Recruitment -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-12">
                        <div class="d-flex align-items-center">
                            <div class="ml-4">
                                <h5 class="font-weight-bold text-primary">📋 Alur Recruitment HCGA</h5>
                                <div class="mt-2 text-muted">
                                    <span class="badge bg-info text-white me-1">1. Input Candidate</span> → 
                                    <span class="badge bg-warning text-dark me-1">2. Verifikasi Dokumen</span> → 
                                    <span class="badge bg-info text-white me-1">3. Psikotest</span> → 
                                    <span class="badge bg-warning text-dark me-1">4. Interview HR</span> → 
                                    <span class="badge bg-info text-white me-1">5. Interview User</span> → 
                                    <span class="badge bg-warning text-dark me-1">6. Tes Teknik</span> → 
                                    <span class="badge bg-info text-white me-1">7. MCU</span> → 
                                    <span class="badge bg-success text-white">8. Offering Letter</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Panel Pengaturan Kolom (Dinamis seperti di employee-lists) -->
<div class="card shadow border-0 mb-3">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <a href="javascript:void(0)" onclick="toggleRecColPanel()" class="text-primary text-decoration-none fw-bold">
                    <span id="RecColToggleIcon">&#9656;</span> Pengaturan Kolom (<span id="RecColCount">{{ count($activeCols) }}</span> aktif)
                </a>
                <input type="text" id="RecColSearch" placeholder="Cari kolom..." onkeyup="filterRecCols()" onclick="expandRecColPanel()" style="padding:4px 10px;width:220px;border:1px solid #ced4da;border-radius:4px;font-size:13px;" />
                <span id="RecColFound" class="text-muted ms-1 small">{{ count($allCols) }} kolom</span>
                <span id="RecColMsg" class="ms-2 text-success fw-bold small"></span>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleDivPivotTable()">
                    <i class="fa fa-chart-pie me-1"></i> Toggle Pivot Table
                </button>
            </div>
        </div>
        <div id="RecColPanel" style="margin-top:15px;display:none;padding-top:12px;border-top:1px dashed #ced4da;">
            <div class="row g-2">
                @foreach($allCols as $ck => $cm)
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 recColItem" title="{{ $cm[0] }}">
                    <div class="form-check m-0 py-1">
                        <input type="checkbox" class="form-check-input recColCheck me-2" id="col_{{ $ck }}" value="{{ $ck }}" {{ in_array($ck, $activeCols) ? 'checked' : '' }} style="cursor:pointer;">
                        <label class="form-check-label text-dark text-truncate d-block" for="col_{{ $ck }}" style="cursor:pointer;" title="{{ $cm[0] }}">
                            {{ $cm[0] }}
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-3 pt-2 border-top d-flex gap-2">
                <button type="button" class="btn btn-success btn-sm me-2" onclick="saveRecruitmentCols(false)">
                    <i class="fa fa-check me-1"></i> Simpan Pilihan
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="saveRecruitmentCols(true)">
                    <i class="fa fa-undo me-1"></i> Reset Default
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pivot Dashboard Card -->
    <div class="col-12 mb-3" id="divPivotTable" style="display:none;">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white m-2 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 text-white">📊 Dashboard Progress (Pivot)</h5>
            </div>
            <div class="card-body">
                <div class="w-100 mb-3" id="pivottrial"></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow border-0">
    <div class="card-header bg-primary text-white m-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <h5 class="card-title mb-0 text-white">📊 Dashboard Progress (Table)</h5>
            <div class="btn-group btn-group-sm" role="group" aria-label="Pivot Mode Toggle">
                <button type="button" class="btn btn-light fw-bold text-primary active" id="btnModeTotal" onclick="setPivotMode('total')">
                    <i class="fa fa-list-ol me-1"></i> Mode Total (Default)
                </button>
                <button type="button" class="btn btn-outline-light fw-bold text-white" id="btnModeBreakdown" onclick="setPivotMode('breakdown')">
                    <i class="fa fa-layer-group me-1"></i> Mode Rincian (Masuk vs Lolos)
                </button>
            </div>
        </div>
        <span class="badge bg-light text-primary">Klik 2x pada baris untuk memfilter daftar kandidat di bawah</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <div id="RecruitmentPivotGrid" style="width:auto;height:280px;background-color:white;overflow:hidden;border:1px solid #ced4da;border-radius:4px;"></div>
            </div>
        </div>
    </div>
</div>
<!-- Detail PTK Card (Grid Utama) -->
<div class="card shadow border-0 mt-3" id="detailPTKCard" style="display: none;">
    <div class="card-header bg-primary text-white m-2 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 text-white">📋 Daftar Candidate (Recruitment Lists)</h5>
        <div class="badge bg-light text-dark">
            <i class="fa fa-info-circle me-1"></i> Klik 2x pada baris untuk melihat detail
        </div>
    </div>
    <div class="card-body p-0">
        <div id="RecruitmentLists" style="width:auto;height:550px; background-color:white;overflow:hidden;border:1px solid #ced4da;border-radius:4px 4px 0 0;"></div>
        <div class="p-2 border-top d-flex align-items-center justify-content-between" style="background-color:#f8f9fa;">
            <div>
                <button type="button" class="btn btn-warning btn-sm me-2" onclick="RecruitmentLists.toExcel('./export/generate.php');">
                    <i class="fa fa-file-export me-1"></i> Export Excel
                </button>
                <button type="button" class="btn btn-info btn-sm text-white me-2" onclick="refreshdata();">
                    <i class="fa fa-sync me-1"></i> Refresh
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="delsRecruitment();">
                    <i class="fa fa-times-circle me-1"></i> Cancel Selection
                </button>
            </div>
            <div id="selectedPtkNotice" class="text-muted small"></div>
        </div>
    </div>
</div>

<div id="divlistRecruitment"></div>

<!-- Custom CSS - Perbaikan Kontras Teks & Header Grid -->
<style>
    /* Header & Badge styling */
    .card-header.bg-primary {
        background-color: #0d6efd !important;
        color: #ffffff !important;
    }
    .card-header.bg-primary h5 {
        color: #ffffff !important;
    }
</style>

<script>
var RecColMeta = {!! json_encode($meta) !!};
var RecActiveCols = {!! json_encode(array_values($activeCols)) !!};
var RecDefaultCols = {!! json_encode(array_values($defaultCols)) !!};
var RecColTotal = {!! count($allCols) !!};

var RecruitmentPivot = null;
var RecruitmentLists = null;
var PRecruitmentLists = null;

function toggleDivPivotTable() {
    $('#divPivotTable').slideToggle();
}

function expandRecColPanel() {
    $("#RecColPanel").slideDown();
    $("#RecColToggleIcon").html("&#9662;");
}

function toggleRecColPanel() {
    $("#RecColPanel").slideToggle(200, function() {
        $("#RecColToggleIcon").html($(this).is(":visible") ? "&#9662;" : "&#9656;");
    });
}

function filterRecCols() {
    var q = $("#RecColSearch").val().toLowerCase();
    if (q !== "") expandRecColPanel();
    var n = 0;
    $(".recColItem").each(function() {
        var hit = $(this).text().toLowerCase().indexOf(q) !== -1;
        $(this).toggle(hit);
        if (hit) n++;
    });
    $("#RecColFound").text(q === "" ? (RecColTotal + " kolom") : (n + " cocok"));
}

function updateRecColCount() {
    $("#RecColCount").text($(".recColCheck:checked").length);
}

$(document).on("change", ".recColCheck", updateRecColCount);

/**
 * Click handler untuk klik ganda pada baris
 */
function RecruitmentListsKlik() {
    var RecruitmentListsID = RecruitmentLists.getSelectedId();
    if (RecruitmentListsID !== '' && RecruitmentListsID !== null) {
        var splitID = RecruitmentListsID.split('|');
        var recruitmentId = splitID[0];
        LoadM('divlistRecruitment', 'recruitment-lists-detail?ID=' + encodeURIComponent(recruitmentId));
    }
    return true;
}

/**
 * Hapus candidate yang dipilih
 */
function delsRecruitment() {
    dhtmlx.confirm({
        title: 'Cancel Recruitment?',
        text: 'Apakah Anda yakin ingin membatalkan recruitment candidate yang dipilih?',
        ok: 'Yes, Cancel',
        cancel: 'Batal',
        callback: function(result) {
            if (result) {
                RecruitmentLists.deleteSelectedRows();
                if (PRecruitmentLists) {
                    PRecruitmentLists.sendData();
                }
                alert('Data Successfully Deleted!');
                setTimeout(function() {
                    refreshdata();
                }, 1000);
            }
        }
    });
}

/**
 * Bangun grid utama candidate sesuai kolom aktif
 */
function buildRecruitmentGrid(keys) {
    document.getElementById('RecruitmentLists').innerHTML = '';
    RecruitmentLists = new dhtmlXGridObject('RecruitmentLists');
    RecruitmentLists.setImagePath("./dhtmlx/imgs/");

    var header = ['No.'], fh = ['#rspan'], w = ['50'], al = ['center'], ty = ['ro'], so = ['int'];
    for (var i = 0; i < keys.length; i++) {
        var m = RecColMeta[keys[i]];
        if (!m) continue;
        header.push(m.label);
        fh.push(m.filter);
        w.push(m.width);
        al.push(m.align);
        ty.push(m.type);
        so.push(m.sort);
    }

    RecruitmentLists.setHeader(header.join(','));
    RecruitmentLists.attachHeader(fh.join(','));
    RecruitmentLists.setInitWidths(w.join(','));
    RecruitmentLists.setColAlign(al.join(','));
    RecruitmentLists.setColTypes(ty.join(','));
    RecruitmentLists.setColSorting(so.join(','));
    RecruitmentLists.enableKeyboardSupport(true);
    RecruitmentLists.setEditable(false);
    RecruitmentLists.attachEvent("onRowDblClicked", RecruitmentListsKlik);
    RecruitmentLists.setSkin("dhx_skyblue");

    // Init data processor
    PRecruitmentLists = new dataProcessor("recruitment-data-submit");
    PRecruitmentLists.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
    });
    PRecruitmentLists.setTransactionMode("POST", true);
    PRecruitmentLists.setUpdateMode("Off");
    PRecruitmentLists.init(RecruitmentLists);

    RecruitmentLists.init();
    refreshdata();
}

/**
 * Simpan pengaturan kolom ke server
 */
function saveRecruitmentCols(reset) {
    var keys = reset ? RecDefaultCols.slice() : $(".recColCheck:checked").map(function() { return this.value; }).get();
    if (!keys.length) {
        alert('Pilih minimal 1 kolom.');
        return;
    }
    $("#RecColMsg").text('Menyimpan...');
    $.post('{{ route("hrd-recruitment-management-columns-save") }}', {
        _token: "{{ csrf_token() }}",
        cols: keys.join(',')
    }, function(res) {
        if (res && res.status === 'ok') {
            $(".recColCheck").each(function() {
                this.checked = (keys.indexOf(this.value) !== -1);
            });
            updateRecColCount();
            RecActiveCols = keys;
            buildRecruitmentGrid(keys);
            $("#RecColMsg").text('Pilihan kolom tersimpan.');
            setTimeout(function() { $("#RecColMsg").text(''); }, 3000);
        } else {
            $("#RecColMsg").text('');
            alert('Gagal menyimpan: ' + ((res && res.message) ? res.message : 'unknown error'));
        }
    }, 'json').fail(function() {
        $("#RecColMsg").text('');
        alert('Gagal menyimpan pilihan kolom.');
    });
}

/**
 * Refresh data dari server
 */
function refreshdata() {
    if (!RecruitmentLists) return;
    var colsParam = RecActiveCols.join(',');
    var id = (RecruitmentPivot && typeof RecruitmentPivot.getSelectedId === 'function') ? RecruitmentPivot.getSelectedId() : null;

    var url = 'xml-hrd-recruitment-management?cols=' + encodeURIComponent(colsParam);
    if (id !== null && id !== '') {
        url += '&Requisition_ID=' + encodeURIComponent(id);
        $("#selectedPtkNotice").html('Filter PTK: <b>' + id + '</b> <a href="javascript:void(0)" onclick="clearPtkFilter()" class="ms-1 text-danger">(Hapus Filter)</a>');
    } else {
        $("#selectedPtkNotice").html('');
    }

    RecruitmentLists.clearAndLoad(url);
    RecruitmentLists.sortRows(0, "str", "des");
}

function clearPtkFilter() {
    if (RecruitmentPivot) {
        RecruitmentPivot.clearSelection();
    }
    refreshdata();
}

/**
 * Filter data berdasarkan Requisition ID
 */
function filterByRequisition(id) {
    if (!RecruitmentLists) return;
    setTimeout(function() {
        RecruitmentLists.setSizes();
        refreshdata();
    }, 50);
}

// Inisialisasi Pivot Grid (Dashboard Summary)
var currentPivotMode = 'total';

function initPivotGrid(mode) {
    currentPivotMode = mode || 'total';
    document.getElementById('RecruitmentPivotGrid').innerHTML = '';
    RecruitmentPivot = new dhtmlXGridObject('RecruitmentPivotGrid');
    RecruitmentPivot.setImagePath("./dhtmlx/imgs/");

    if (currentPivotMode === 'breakdown') {
        RecruitmentPivot.setHeader("PTK Number,Tipe Metrik,Current Approver By,Seleksi Administrasi,Psikotes,Interview HR,Interview User,Tes Teknik,MCU,Offering Letter,Keterangan");
        RecruitmentPivot.attachHeader("#text_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        RecruitmentPivot.setInitWidths("140,110,140,90,80,90,90,80,70,90,220");
        RecruitmentPivot.setColAlign("left,center,left,center,center,center,center,center,center,center,left");
        RecruitmentPivot.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
        RecruitmentPivot.setColSorting("str,str,str,int,int,int,int,int,int,int,str");
    } else {
        RecruitmentPivot.setHeader("PTK Number,Current Approver By,Seleksi Administrasi,Psikotes,Interview HR,Interview User,Tes Teknik,MCU,Offering Letter,Keterangan");
        RecruitmentPivot.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        RecruitmentPivot.setInitWidths("150,150,90,80,90,90,80,70,90,250");
        RecruitmentPivot.setColAlign("left,left,center,center,center,center,center,center,center,left");
        RecruitmentPivot.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
        RecruitmentPivot.setColSorting("str,str,int,int,int,int,int,int,int,str");
    }

    RecruitmentPivot.enableKeyboardSupport(true);
    RecruitmentPivot.setEditable(false);
    RecruitmentPivot.setSkin("dhx_skyblue");
    RecruitmentPivot.attachEvent("onRowDblClicked", function(id, ind) {
        document.getElementById('detailPTKCard').style.display = '';
        var rawPtk = id.indexOf('|') !== -1 ? id.split('|')[0] : id;
        filterByRequisition(rawPtk);
    });
    RecruitmentPivot.init();
    RecruitmentPivot.load('xml-hrd-recruitment-pivot?mode=' + currentPivotMode);
}

function setPivotMode(mode) {
    if (mode === 'breakdown') {
        $('#btnModeBreakdown').removeClass('btn-outline-light text-white').addClass('btn-light text-primary active');
        $('#btnModeTotal').removeClass('btn-light text-primary active').addClass('btn-outline-light text-white');
    } else {
        $('#btnModeTotal').removeClass('btn-outline-light text-white').addClass('btn-light text-primary active');
        $('#btnModeBreakdown').removeClass('btn-light text-primary active').addClass('btn-outline-light text-white');
    }
    initPivotGrid(mode);
}

// Inisialisasi awal Pivot Grid
initPivotGrid('total');

// Build grid candidate pertama kali
buildRecruitmentGrid(RecActiveCols);

// Auto-refresh setiap 60 detik
setInterval(function() {
    refreshdata();
    if (RecruitmentPivot) {
        RecruitmentPivot.load('xml-hrd-recruitment-pivot?mode=' + currentPivotMode);
    }
}, 60000);
</script>

<!-- Required Libraries untuk Pivot UI -->
<script type="text/javascript" src="{{ asset('assets/plugins/jquery-ui/js/jquery-ui.js') }}"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/tips_data.min.js"></script>
<script>
    try {
        var rawJson = `{!! json_encode($rows ? $rows->values()->toArray() : []) !!}`;
        var parsedJson = JSON.parse(rawJson);
        
        if (parsedJson && parsedJson.length > 0 && typeof $.fn.pivotUI === 'function') {
            $('#pivottrial').pivotUI(parsedJson, {
                rows: ["no_ptk", "Type"],
                cols: ["Stage"],
                aggregatorName: "Sum",
                vals: ["Count"],
                rendererName: "Table"
            });
        } else {
            $('#pivottrial').html('<div class="text-center text-muted p-4"><i class="fa fa-info-circle fa-2x mb-2"></i><br>Tidak ada data untuk ditampilkan di pivot table</div>');
        }
    } catch (e) {
        console.warn('Pivot UI error:', e);
        $('#pivottrial').html('<div class="text-center text-muted p-4"><i class="fa fa-info-circle fa-2x mb-2"></i><br>Data pivot belum tersedia</div>');
    }
</script>
@endsection
