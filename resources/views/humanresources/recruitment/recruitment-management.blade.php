@extends('layout.mainlayout-hrd')
@section('content-wrapper')
@csrf

<style>
/* Custom Recruitment Management UX styling (Reference: Approval Engine UI) */
.management-metric-card {
    transition: all 0.2s ease-in-out;
    border-radius: 8px;
    border: 1px solid rgba(0,0,0,0.06);
}
.management-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.pipeline-step {
    transition: all 0.15s ease;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.3rem 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
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
#RecruitmentPivotGrid, #RecruitmentLists {
    border-radius: 4px;
}
</style>

{{-- Modern Breadcrumb Header --}}
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            Recruitment Management Dashboard
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Recruitment</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Dashboard Progress</li>
            </ol>
        </nav>
    </div>
    <div class="d-none d-sm-flex align-items-center gap-2">
        <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-chart-line me-1"></i> Funnel &amp; Progress Tracker
        </span>
    </div>
</div>

{{-- Info Box: Pipeline Alur Recruitment --}}
<div class="card shadow-sm border-0 mb-3 bg-white">
    <div class="card-body py-3 px-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <span class="fw-bold text-dark fs-13 d-flex align-items-center">
                    <i class="fa fa-diagram-project text-primary me-2"></i> Pipeline Alur Rekrutmen HCGA:
                </span>
                <div class="d-flex flex-wrap align-items-center gap-1 mt-2">
                    <span class="pipeline-step bg-info-transparent text-info border">1. Seleksi Berkas</span>
                    <i class="fa fa-chevron-right text-muted fs-11"></i>
                    <span class="pipeline-step bg-primary-transparent text-white border">2. Psikotest</span>
                    <i class="fa fa-chevron-right text-muted fs-11"></i>
                    <span class="pipeline-step bg-info-transparent text-info border">3. Interview HR</span>
                    <i class="fa fa-chevron-right text-muted fs-11"></i>
                    <span class="pipeline-step bg-warning-transparent text-warning border">4. Interview User</span>
                    <i class="fa fa-chevron-right text-muted fs-11"></i>
                    <span class="pipeline-step bg-secondary-transparent text-secondary border">5. Tes Teknik</span>
                    <i class="fa fa-chevron-right text-muted fs-11"></i>
                    <span class="pipeline-step bg-info-transparent text-info border">6. MCU</span>
                    <i class="fa fa-chevron-right text-muted fs-11"></i>
                    <span class="pipeline-step bg-success-transparent text-success border">7. Offering &amp; Hired</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary quick-action-btn" onclick="toggleDivPivotTable()">
                    <i class="fa fa-chart-pie me-1"></i> Toggle Pivot Table
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Panel Pengaturan Kolom Dinamis --}}
<div class="card shadow-sm border mb-3">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <a href="javascript:void(0)" onclick="toggleRecColPanel()" class="text-primary text-decoration-none fw-bold">
                    <span id="RecColToggleIcon">&#9656;</span> Pengaturan Kolom Tabel (<span id="RecColCount">{{ count($activeCols) }}</span> aktif)
                </a>
                <input type="text" id="RecColSearch" placeholder="Cari kolom..." onkeyup="filterRecCols()" onclick="expandRecColPanel()" style="padding:4px 10px;width:220px;border:1px solid #ced4da;border-radius:4px;font-size:13px;" />
                <span id="RecColFound" class="text-muted ms-1 small">{{ count($allCols) }} kolom tersedia</span>
                <span id="RecColMsg" class="ms-2 text-success fw-bold small"></span>
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
                <button type="button" class="btn btn-success btn-sm quick-action-btn" onclick="saveRecruitmentCols(false)">
                    <i class="fa fa-check me-1"></i> Simpan Pilihan
                </button>
                <button type="button" class="btn btn-secondary btn-sm quick-action-btn" onclick="saveRecruitmentCols(true)">
                    <i class="fa fa-undo me-1"></i> Reset Default
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Pivot UI Table Container (Collapsible) --}}
<div class="row" id="divPivotTable" style="display:none;">
    <div class="col-12 mb-3">
        <div class="card shadow-sm border">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-dark fs-13">
                    <i class="fa fa-chart-pie text-primary me-2"></i>Pivot UI Analysis
                </span>
                <button type="button" class="btn-close btn-sm" onclick="toggleDivPivotTable()"></button>
            </div>
            <div class="card-body">
                <div class="w-100 mb-2" id="pivottrial"></div>
            </div>
        </div>
    </div>
</div>

{{-- Dashboard Progress (Pivot Grid Table) --}}
<div class="card shadow-sm border mb-3">
    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <span class="fw-semibold text-dark fs-13">
                <i class="fa fa-table-list text-primary me-2"></i>Rekap Progress Rekrutmen per PTK
            </span>
            <div class="btn-group btn-group-sm" role="group" aria-label="Pivot Mode Toggle">
                <button type="button" class="btn btn-primary btn-sm quick-action-btn active" id="btnModeTotal" onclick="setPivotMode('total')">
                    <i class="fa fa-list-ol me-1"></i> Mode Total
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm quick-action-btn" id="btnModeBreakdown" onclick="setPivotMode('breakdown')">
                    <i class="fa fa-layer-group me-1"></i> Mode Rincian (Masuk vs Lolos)
                </button>
            </div>
        </div>
        <span class="badge bg-light text-muted border fw-normal">
            <i class="fa fa-mouse-pointer text-primary me-1"></i> Klik sel pada tabel untuk menyaring daftar kandidat
        </span>
    </div>
    <div class="card-body p-0">
        <div id="RecruitmentPivotGrid" style="width:100%;height:280px;background-color:white;overflow:hidden;"></div>
    </div>
    <div class="card-footer bg-light py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <small class="text-muted">
            <i class="fa fa-circle-info text-info me-1"></i> <strong>Tips:</strong> Klik baris PTK untuk menampilkan seluruh kandidat posisi terkait, atau klik angka kolom tahapan untuk menyaring kandidat pada tahap tersebut.
        </small>
        <button type="button" class="btn btn-outline-secondary btn-sm quick-action-btn py-0 px-2 fs-11" onclick="RecruitmentPivot.load('xml-hrd-recruitment-pivot?mode=' + currentPivotMode);">
            <i class="fa fa-rotate me-1"></i> Refresh Rekap
        </button>
    </div>
</div>

{{-- Detail PTK Card (Grid Utama Kandidat) --}}
<div class="card shadow-sm border mb-3" id="detailPTKCard" style="display: none;">
    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <span class="fw-semibold text-dark fs-13" id="detailCardTitle">
                <i class="fa fa-users text-primary me-2"></i>Daftar Kandidat (Recruitment Lists)
            </span>
            <small id="detailCardSubtitle" class="text-muted ms-2" style="display:none;"></small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-muted border fw-normal">
                <i class="fa fa-circle-info text-info me-1"></i> Klik ganda baris untuk melihat detail lengkap
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="delsRecruitment();" title="Tutup Tabel Kandidat">
                <i class="fa fa-xmark"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="RecruitmentLists" style="width:100%;height:520px;background-color:white;overflow:hidden;"></div>
    </div>
    <div class="card-footer bg-light py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-success btn-sm text-white quick-action-btn" onclick="RecruitmentLists.toExcel('./export/generate.php');">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm quick-action-btn" onclick="refreshdata();">
                <i class="fa fa-rotate me-1"></i> Refresh
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm quick-action-btn" onclick="delsRecruitment();">
                <i class="fa fa-xmark me-1"></i> Tutup Seleksi
            </button>
        </div>
        <div id="selectedPtkNotice" class="text-muted small"></div>
    </div>
</div>

<div id="divlistRecruitment" class="mb-4"></div>

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
        var t = $(this).attr("title").toLowerCase();
        if (t.indexOf(q) !== -1) {
            $(this).show();
            n++;
        } else {
            $(this).hide();
        }
    });
    $("#RecColFound").text(n + " kolom ditemukan");
}

function saveRecruitmentCols(reset) {
    var cols = [];
    if (reset) {
        cols = RecDefaultCols.slice();
    } else {
        $(".recColCheck:checked").each(function() {
            cols.push($(this).val());
        });
    }
    if (!cols.length) {
        alert("Pilih minimal 1 kolom.");
        return;
    }

    $("#RecColMsg").text("Menyimpan...").removeClass("text-danger").addClass("text-success");
    $.ajax({
        url: "{{ url('hrd-recruitment-management-columns-save') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            cols: cols.join(",")
        },
        dataType: "json",
        success: function(res) {
            if (res.status === "ok") {
                RecActiveCols = cols;
                $("#RecColCount").text(cols.length);
                $(".recColCheck").each(function() {
                    $(this).prop("checked", cols.indexOf($(this).val()) !== -1);
                });
                $("#RecColMsg").text("Tersimpan.");
                setTimeout(function() { $("#RecColMsg").text(""); }, 2000);
                buildRecruitmentGrid(cols);
            } else {
                $("#RecColMsg").text(res.message || "Gagal menyimpan").removeClass("text-success").addClass("text-danger");
            }
        },
        error: function() {
            $("#RecColMsg").text("Gagal koneksi").removeClass("text-success").addClass("text-danger");
        }
    });
}

var currentFilterPtk = null;
var currentFilterStage = null;
var currentFilterMetric = 'ENTERED';

function filterByRequisitionAndStage(ptk, stageKey, stageLabel, metric) {
    currentFilterPtk = ptk;
    currentFilterStage = stageKey;
    currentFilterMetric = metric || 'ENTERED';

    var title = '<i class="fa fa-users text-primary me-2"></i>Daftar Candidate PTK: <span class="text-primary">' + ptk + '</span>';
    var subTitle = '';

    if (stageLabel) {
        var metricLabel = (currentFilterMetric === 'PASSED') ? 'Lolos / Approved' : 'Total Masuk';
        title += ' &bull; Tahap: <span class="badge bg-primary text-white">' + stageLabel + ' (' + metricLabel + ')</span>';
        subTitle = 'Menyaring kandidat yang masuk kriteria ' + stageLabel + ' (' + metricLabel + ') pada nomor PTK ' + ptk;
    } else {
        subTitle = 'Menampilkan seluruh kandidat pada nomor PTK ' + ptk;
    }

    $('#detailCardTitle').html(title);
    if (subTitle) {
        $('#detailCardSubtitle').html(subTitle).show();
    } else {
        $('#detailCardSubtitle').hide();
    }

    $('#detailPTKCard').slideDown();
    $('html, body').animate({
        scrollTop: $("#detailPTKCard").offset().top - 70
    }, 300);

    refreshdata();
}

function delsRecruitment() {
    currentFilterPtk = null;
    currentFilterStage = null;
    currentFilterMetric = 'ENTERED';
    $('#detailPTKCard').slideUp();
    $('#divlistRecruitment').html('');
}

function refreshdata() {
    if (!RecruitmentLists) return;
    var url = 'xml-hrd-recruitment-management?cols=' + encodeURIComponent(RecActiveCols.join(','));
    if (currentFilterPtk) {
        url += '&Requisition_ID=' + encodeURIComponent(currentFilterPtk);
    }
    if (currentFilterStage) {
        url += '&stage=' + encodeURIComponent(currentFilterStage);
        url += '&metric=' + encodeURIComponent(currentFilterMetric);
    }
    RecruitmentLists.clearAndLoad(url);
}

function buildRecruitmentGrid(activeCols) {
    if (!activeCols || !activeCols.length) {
        activeCols = RecDefaultCols;
    }

    var headers = ["No"];
    var filters = ["#rspan"];
    var widths = ["50"];
    var aligns = ["center"];
    var sorts = ["int"];
    var types = ["ro"];

    for (var i = 0; i < activeCols.length; i++) {
        var k = activeCols[i];
        var m = RecColMeta[k];
        if (!m) continue;
        headers.push(m.label);
        filters.push(m.filter || "#text_filter");
        widths.push(m.width || 120);
        aligns.push(m.align || "left");
        sorts.push(m.sort || "str");
        types.push(m.type || "ro");
    }

    document.getElementById("RecruitmentLists").innerHTML = "";
    RecruitmentLists = new dhtmlXGridObject("RecruitmentLists");
    RecruitmentLists.setImagePath("./dhtmlx/imgs/");
    RecruitmentLists.setHeader(headers.join(","));
    RecruitmentLists.attachHeader(filters.join(","));
    RecruitmentLists.setInitWidths(widths.join(","));
    RecruitmentLists.setColAlign(aligns.join(","));
    RecruitmentLists.setColSorting(sorts.join(","));
    RecruitmentLists.setColTypes(types.join(","));
    RecruitmentLists.enableSmartRendering(true, 150);
    RecruitmentLists.enableKeyboardSupport(true);
    RecruitmentLists.setSkin("dhx_skyblue");

    RecruitmentLists.attachEvent("onRowDblClicked", function(rowId) {
        var realId = rowId.indexOf("|") !== -1 ? rowId.split("|")[0] : rowId;
        if (realId) {
            $('#divlistRecruitment').html(
                '<div class="card shadow-sm border p-4 text-center my-3">' +
                    '<div class="spinner-border text-primary mx-auto mb-2" role="status"></div>' +
                    '<div class="text-muted small">Memuat rincian dokumen &amp; profil kandidat...</div>' +
                '</div>'
            );
            $('html, body').animate({
                scrollTop: $("#divlistRecruitment").offset().top - 80
            }, 300);
            LoadM("divlistRecruitment", "recruitment-lists-detail?ID=" + encodeURIComponent(realId));
        }
        return true;
    });

    RecruitmentLists.init();
    refreshdata();
}

// Inisialisasi Pivot Grid (Dashboard Summary)
var currentPivotMode = 'total';

var stageColMapTotal = {
    2: { key: 'Seleksi_Adm', label: 'Seleksi Administrasi' },
    3: { key: 'Psikotes', label: 'Psikotes' },
    4: { key: 'Interview_HR', label: 'Interview HR' },
    5: { key: 'Interview_User', label: 'Interview User' },
    6: { key: 'Tes_Teknik', label: 'Tes Teknik' },
    7: { key: 'MCU', label: 'MCU' },
    8: { key: 'Offering', label: 'Offering Letter' }
};

var stageColMapBreakdown = {
    3: { key: 'Seleksi_Adm', label: 'Seleksi Administrasi' },
    4: { key: 'Psikotes', label: 'Psikotes' },
    5: { key: 'Interview_HR', label: 'Interview HR' },
    6: { key: 'Interview_User', label: 'Interview User' },
    7: { key: 'Tes_Teknik', label: 'Tes Teknik' },
    8: { key: 'MCU', label: 'MCU' },
    9: { key: 'Offering', label: 'Offering Letter' }
};

function handlePivotCellInteraction(id, ind) {
    var rawPtk = id.indexOf('|') !== -1 ? id.split('|')[0] : id;
    var metric = 'ENTERED';
    if (id.indexOf('|PASSED') !== -1) {
        metric = 'PASSED';
    }

    var colMap = (currentPivotMode === 'breakdown') ? stageColMapBreakdown : stageColMapTotal;
    var stageInfo = colMap[ind] || null;

    if (stageInfo) {
        filterByRequisitionAndStage(rawPtk, stageInfo.key, stageInfo.label, metric);
    } else {
        filterByRequisitionAndStage(rawPtk, null, null, metric);
    }
}

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

    // Single click pada cell kolom
    RecruitmentPivot.attachEvent("onRowSelect", function(id, ind) {
        handlePivotCellInteraction(id, ind);
    });

    // Double click pada baris/cell
    RecruitmentPivot.attachEvent("onRowDblClicked", function(id, ind) {
        handlePivotCellInteraction(id, ind);
    });

    RecruitmentPivot.init();
    RecruitmentPivot.load('xml-hrd-recruitment-pivot?mode=' + currentPivotMode);
}

function setPivotMode(mode) {
    if (mode === 'breakdown') {
        $('#btnModeBreakdown').removeClass('btn-outline-secondary').addClass('btn-primary active');
        $('#btnModeTotal').removeClass('btn-primary active').addClass('btn-outline-secondary');
    } else {
        $('#btnModeTotal').removeClass('btn-outline-secondary').addClass('btn-primary active');
        $('#btnModeBreakdown').removeClass('btn-primary active').addClass('btn-outline-secondary');
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
