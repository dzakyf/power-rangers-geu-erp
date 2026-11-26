@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@php
    $defaultStartDate = date('Y-m-01');
    $defaultEndDate   = date('Y-m-d');
@endphp
<style>
    .po-outstanding-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    }
    .po-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #198754;
        border-radius: 8px;
        padding: 10px 14px;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
        transition: transform 0.12s ease;
    }
    .po-kpi-card:hover {
        transform: translateY(-1px);
    }
    .po-kpi-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #64748b;
        margin-bottom: 2px;
    }
    .po-kpi-value {
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .po-kpi-sub {
        font-size: 11px;
        color: #64748b;
        margin-top: 2px;
    }
    .po-filter-bar {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
    }
    .po-legend-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: #334155;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 3px 9px;
        border-radius: 999px;
    }
    .po-legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }
    /* Rapikan DHTMLX Grid */
    div.gridbox_dhx_skyblue.gridbox {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px;
        overflow: hidden;
        font-family: inherit;
    }
    div.gridbox_dhx_skyblue table.hdr td {
        background: linear-gradient(180deg, #f8fafc 0%, #eef2f6 100%) !important;
        color: #1e293b !important;
        font-weight: 700 !important;
        font-size: 11.5px !important;
        border-right: 1px solid #cbd5e1 !important;
        border-bottom: 1px solid #cbd5e1 !important;
    }
    div.gridbox_dhx_skyblue table.obj tr td {
        font-size: 11.5px !important;
        color: #1e293b;
        border-right: 1px solid #f1f5f9 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding-top: 6px !important;
        padding-bottom: 6px !important;
        vertical-align: middle !important;
    }
    div.gridbox_dhx_skyblue table.obj tr.rowselected td {
        background-color: #dcfce7 !important;
        color: #064e3b !important;
        font-weight: 600;
    }
    .po-badge {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 5px;
        font-size: 10.5px;
        font-weight: 700;
        line-height: 1.25;
    }
    .po-badge-open { background: #e0f2fe; color: #0369a1; }
    .po-badge-approved { background: #dcfce7; color: #15803d; }
    .po-badge-delivery { background: #ffedd5; color: #c2410c; }
    .po-badge-partial { background: #cffafe; color: #0e7490; }
    .po-badge-proses { background: #fef9c3; color: #854d0e; }
    .po-badge-done { background: #dcfce7; color: #166534; }
    .po-badge-up { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .po-badge-down { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
    .po-badge-normal { background: #f1f5f9; color: #334155; }
</style>

<div class="main-content-header">
    <h1>PROCUREMENT</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard1">Home</a></li>
        <li class="breadcrumb-item active"><span>Outstanding PO</span></li>
    </ol>
</div>

<!-- Ringkasan KPI PO -->
<div class="row mb-3">
    <div class="col-lg col-md-6 mb-2 mb-lg-0">
        <div class="po-kpi-card" style="border-left-color: #0f766e;">
            <div class="po-kpi-label">Total PO Ditampilkan</div>
            <div class="po-kpi-value" id="KpiTotalCount">0 <span style="font-size:12px; font-weight:600; color:#64748b;">PO</span></div>
            <div class="po-kpi-sub">Semua baris di grid</div>
        </div>
    </div>
    <div class="col-lg col-md-6 mb-2 mb-lg-0">
        <div class="po-kpi-card" style="border-left-color: #15803d;">
            <div class="po-kpi-label">Total Nilai PO</div>
            <div class="po-kpi-value text-success" id="KpiTotalAmount">Rp 0</div>
            <div class="po-kpi-sub">Akumulasi Grand Total PO</div>
        </div>
    </div>
    <div class="col-lg col-md-6 mb-2 mb-md-0">
        <div class="po-kpi-card" style="border-left-color: #d97706;">
            <div class="po-kpi-label">On Proses</div>
            <div class="po-kpi-value" style="color:#b45309;" id="KpiOnProsesCount">0 <span style="font-size:12px; font-weight:600; color:#64748b;">PO</span></div>
            <div class="po-kpi-sub">Masih berjalan / belum selesai</div>
        </div>
    </div>
    <div class="col-lg col-md-6 mb-2 mb-md-0">
        <div class="po-kpi-card" style="border-left-color: #16a34a;">
            <div class="po-kpi-label">Status Done</div>
            <div class="po-kpi-value text-success" id="KpiDoneCount">0 <span style="font-size:12px; font-weight:600; color:#64748b;">PO</span></div>
            <div class="po-kpi-sub" id="KpiDoneAmount">Nilai Done: Rp 0</div>
        </div>
    </div>
    <div class="col-lg col-md-6">
        <div class="po-kpi-card" style="border-left-color: #dc2626;">
            <div class="po-kpi-label">PO Emergency</div>
            <div class="po-kpi-value text-danger" id="KpiEmergencyCount">0 <span style="font-size:12px; font-weight:600; color:#64748b;">PO</span></div>
            <div class="po-kpi-sub">Ref. PR berstatus EMERGENCY</div>
        </div>
    </div>
</div>

<!-- Panel Utama Grid Outstanding PO -->
<div class="po-outstanding-card p-3 mb-3">
    <!-- Filter Range Tanggal PO -->
    <div class="po-filter-bar mb-3">
        <div class="row align-items-end">
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="small font-weight-bold text-dark mb-1">
                    <i class="far fa-calendar-alt mr-1 text-success"></i>Dari Tanggal PO
                </label>
                <input type="date" id="FilterStartDate" class="form-control form-control-sm" value="{{ $defaultStartDate }}">
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="small font-weight-bold text-dark mb-1">
                    <i class="far fa-calendar-alt mr-1 text-success"></i>Sampai Tanggal PO
                </label>
                <input type="date" id="FilterEndDate" class="form-control form-control-sm" value="{{ $defaultEndDate }}">
            </div>
            <div class="col-md-6 d-flex flex-wrap align-items-center" style="gap: 8px;">
                <button type="button" id="BtnFilterDatePO" class="btn btn-primary btn-sm font-weight-bold shadow-sm">
                    <i class="fas fa-filter mr-1"></i> Filter Tanggal
                </button>
                <button type="button" id="BtnResetDatePO" class="btn btn-outline-secondary btn-sm font-weight-bold" title="Kembalikan ke Tanggal 1 s.d. Hari Ini">
                    <i class="fas fa-undo mr-1"></i> Reset (Tgl 1 - Hari Ini)
                </button>
                <span class="text-muted small ml-md-auto" id="ActivePeriodLabel" style="font-size: 11px;">
                    Periode: <strong>{{ $defaultStartDate }}</strong> s/d <strong>{{ $defaultEndDate }}</strong>
                </span>
            </div>
        </div>
    </div>

    <!-- Toolbar Aksi & Legenda -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: 10px;">
        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            <button type="button" id="BtnOpenDetailPO" class="btn btn-success btn-sm font-weight-bold shadow-sm">
                <i class="fa fa-eye mr-1"></i> Lihat Detail PO
            </button>
            <button type="button" id="BtnPrintPO" class="btn btn-dark btn-sm font-weight-bold shadow-sm">
                <i class="fa fa-print mr-1"></i> Cetak Purchase Order (PDF)
            </button>
            <button type="button" id="BtnExportExcelPO" class="btn btn-outline-success btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </button>
            <button type="button" id="BtnRefreshPO" class="btn btn-outline-secondary btn-sm font-weight-bold">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Data
            </button>
        </div>

        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            <span class="po-legend-pill">
                <span class="po-legend-dot" style="background:#f59e0b;"></span>
                Harga di atas histori vendor/item
            </span>
            <span class="po-legend-pill">
                <span class="po-legend-dot" style="background:#10b981;"></span>
                Harga di bawah histori vendor/item
            </span>
            <span class="text-muted small" style="font-size: 11px;">
                <i class="fas fa-mouse-pointer mr-1"></i>Double-click baris untuk detail &amp; penerimaan
            </span>
        </div>
    </div>

    <!-- Grid Container -->
    <div id="POGrid" style="width:100%; height:410px; background-color:white; overflow:hidden"></div>
</div>

<!-- Container Detail Item PO -->
<div id="PODetailDiv" class="mt-3"></div>

<script>
const DEFAULT_START_DATE = "{{ $defaultStartDate }}";
const DEFAULT_END_DATE   = "{{ $defaultEndDate }}";

var POGrid = new dhtmlXGridObject('POGrid');
POGrid.setImagePath("./dhtmlx/imgs/");
POGrid.setHeader("No.,No. PO,Tanggal PO,Ref. PR,Vendor / Supplier,Department,Termin,Grand Total (Rp),History Harga,Lead Time (PR-Complete),Lead Time (PO-Received),Proses,Status PO,Status Receive,Dibuat Oleh");
POGrid.setInitWidths("48,155,95,145,235,95,85,125,170,130,135,90,92,100,90");
POGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#select_filter,#text_filter");
POGrid.setColAlign("center,center,center,center,left,center,center,right,left,center,center,center,center,center,center");
POGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
POGrid.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
POGrid.setEditable(false);
POGrid.enableMultiline(true);
POGrid.setSkin("dhx_skyblue");
POGrid.init();

function parseRupiahToNumber(val) {
    if (!val) return 0;
    var clean = String(val).replace(/<[^>]*>/g, '').replace(/[^0-9,-]/g, '').replace(/\./g, '').replace(',', '.');
    var num = parseFloat(clean);
    return isNaN(num) ? 0 : num;
}

function formatRupiahDisplay(num) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(num || 0));
}

function renumberVisibleGridRows() {
    var seq = 0;
    POGrid.forEachRow(function(rowId) {
        seq++;
        POGrid.cells(rowId, 0).setValue(seq);
    });
}

function formatGridCellsAndKpi() {
    var totalRows = 0;
    var totalAmount = 0;
    var onProsesCount = 0;
    var doneCount = 0;
    var doneAmount = 0;
    var emergencyCount = 0;

    POGrid.forEachRow(function(rowId) {
        totalRows++;
        POGrid.cells(rowId, 0).setValue(totalRows);

        var refPrText    = String(POGrid.cells(rowId, 3).getValue() || '').replace(/<[^>]*>/g, '').trim().toUpperCase();
        var grandText    = String(POGrid.cells(rowId, 7).getValue() || '').replace(/<[^>]*>/g, '').trim();
        var histText     = String(POGrid.cells(rowId, 8).getValue() || '').replace(/<[^>]*>/g, '').trim();
        var poLtText     = String(POGrid.cells(rowId, 10).getValue() || '').replace(/<[^>]*>/g, '').trim();
        var prosesText   = String(POGrid.cells(rowId, 11).getValue() || '').replace(/<[^>]*>/g, '').trim().toUpperCase();
        var stPoText     = String(POGrid.cells(rowId, 12).getValue() || '').replace(/<[^>]*>/g, '').trim().toUpperCase();

        var rowAmount = parseRupiahToNumber(grandText);
        totalAmount += rowAmount;

        if (prosesText === 'DONE') {
            doneCount++;
            doneAmount += rowAmount;
        } else {
            onProsesCount++;
        }

        if (refPrText.indexOf('EMERGENCY') !== -1) emergencyCount++;

        // Badge History Harga (index 8)
        if (histText.indexOf('Naik vs Hist') !== -1) {
            POGrid.cells(rowId, 8).setValue('<span class="po-badge po-badge-up">' + histText + '</span>');
        } else if (histText.indexOf('Turun vs Hist') !== -1) {
            POGrid.cells(rowId, 8).setValue('<span class="po-badge po-badge-down">' + histText + '</span>');
        } else if (histText && histText !== '-') {
            POGrid.cells(rowId, 8).setValue('<span class="po-badge po-badge-normal">' + histText + '</span>');
        }

        // Badge Lead Time (PO-Received) (index 10)
        if (poLtText && poLtText !== '-') {
            var ltUpper = poLtText.toUpperCase();
            var ltClass = ltUpper.indexOf('DONE') !== -1 || ltUpper.indexOf('RECEIVED') !== -1
                ? 'po-badge-proses'
                : 'po-badge-open';
            POGrid.cells(rowId, 10).setValue('<span class="po-badge ' + ltClass + '">' + poLtText + '</span>');
        }

        // Badge Proses (index 11)
        if (prosesText === 'DONE') {
            POGrid.cells(rowId, 11).setValue('<span class="po-badge po-badge-done">DONE</span>');
        } else if (prosesText) {
            POGrid.cells(rowId, 11).setValue('<span class="po-badge po-badge-proses">' + prosesText + '</span>');
        }

        // Badge Status PO (index 12)
        var poClass = 'po-badge-open';
        if (stPoText === 'APPROVED') poClass = 'po-badge-approved';
        else if (stPoText === 'DELIVERY') poClass = 'po-badge-delivery';
        else if (stPoText === 'PARTIAL') poClass = 'po-badge-partial';
        else if (stPoText === 'RECEIVED' || stPoText === 'CLOSED' || stPoText === 'DONE') poClass = 'po-badge-done';
        if (stPoText) {
            POGrid.cells(rowId, 12).setValue('<span class="po-badge ' + poClass + '">' + stPoText + '</span>');
        }
    });

    $('#KpiTotalCount').html(totalRows + ' <span style="font-size:12px; font-weight:600; color:#64748b;">PO</span>');
    $('#KpiTotalAmount').text(formatRupiahDisplay(totalAmount));
    $('#KpiOnProsesCount').html(onProsesCount + ' <span style="font-size:12px; font-weight:600; color:#64748b;">PO</span>');
    $('#KpiDoneCount').html(doneCount + ' <span style="font-size:12px; font-weight:600; color:#64748b;">PO</span>');
    $('#KpiDoneAmount').text('Nilai Done: ' + formatRupiahDisplay(doneAmount));
    $('#KpiEmergencyCount').html(emergencyCount + ' <span style="font-size:12px; font-weight:600; color:#64748b;">PO</span>');
}

function loadOutstandingPOGrid() {
    var startDt = $('#FilterStartDate').val() || DEFAULT_START_DATE;
    var endDt   = $('#FilterEndDate').val() || DEFAULT_END_DATE;

    if (startDt && endDt && startDt > endDt) {
        alert('Dari Tanggal tidak boleh lebih besar dari Sampai Tanggal.');
        return;
    }

    $('#ActivePeriodLabel').html('Periode: <strong>' + startDt + '</strong> s/d <strong>' + endDt + '</strong>');

    var url = "{{ url('xml-outstanding-po-list') }}"
        + "?start_date=" + encodeURIComponent(startDt)
        + "&end_date=" + encodeURIComponent(endDt);

    POGrid.clearAll();
    POGrid.load(url, function() {
        formatGridCellsAndKpi();
    });
}

POGrid.attachEvent("onFilterEnd", function() {
    formatGridCellsAndKpi();
});

POGrid.attachEvent("onAfterSorting", function() {
    renumberVisibleGridRows();
});

function bukaDetailPO(poNo) {
    if (!poNo) return;
    $('#PODetailDiv').html('<div class="alert alert-info py-2 shadow-sm"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat rincian Purchase Order <strong>' + poNo + '</strong>...</div>');
    $('#PODetailDiv').load("{{ url('po-detail') }}?PO_No=" + encodeURIComponent(poNo), function() {
        $('html, body').animate({
            scrollTop: $('#PODetailDiv').offset().top - 80
        }, 250);
    });
}

function escapeExcelHtml(str) {
    return String(str || '')
        .replace(/<[^>]*>/g, '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// Export data grid ke Excel (.xls)
$('#BtnExportExcelPO').on('click', function() {
    var colCount = POGrid.getColumnCount();
    var headers = [];
    for (var c = 0; c < colCount; c++) {
        headers.push(POGrid.getColLabel(c));
    }

    var rowsHtml = '';
    var rowNum = 0;
    POGrid.forEachRow(function(rowId) {
        rowNum++;
        var cellsHtml = '';
        for (var c = 0; c < colCount; c++) {
            var val = POGrid.cells(rowId, c).getValue();
            if (c === 0) val = rowNum;
            cellsHtml += '<td>' + escapeExcelHtml(val) + '</td>';
        }
        rowsHtml += '<tr>' + cellsHtml + '</tr>';
    });

    if (rowNum === 0) {
        alert('Tidak ada data Purchase Order untuk di-export pada periode ini.');
        return;
    }

    var startDt = $('#FilterStartDate').val() || DEFAULT_START_DATE;
    var endDt   = $('#FilterEndDate').val() || DEFAULT_END_DATE;

    var headCells = headers.map(function(h) {
        return '<th style="background:#15803d;color:#ffffff;font-weight:bold;border:1px solid #cbd5e1;padding:6px;">' + escapeExcelHtml(h) + '</th>';
    }).join('');

    var tableHtml = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">'
        + '<head><meta charset="UTF-8"></head><body>'
        + '<h3>Laporan Purchase Order (Periode: ' + escapeExcelHtml(startDt) + ' s/d ' + escapeExcelHtml(endDt) + ')</h3>'
        + '<table border="1" cellspacing="0" cellpadding="4">'
        + '<thead><tr>' + headCells + '</tr></thead>'
        + '<tbody>' + rowsHtml + '</tbody>'
        + '</table></body></html>';

    var blob = new Blob([tableHtml], { type: 'application/vnd.ms-excel;charset=utf-8;' });
    var downloadUrl = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = downloadUrl;
    a.download = 'Purchase_Order_' + startDt + '_to_' + endDt + '.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(function() { URL.revokeObjectURL(downloadUrl); }, 1000);
});

// Muat data awal (Tanggal 1 s/d Hari Ini)
loadOutstandingPOGrid();

$('#BtnFilterDatePO').on('click', function() {
    $('#PODetailDiv').html('');
    loadOutstandingPOGrid();
});

$('#FilterStartDate, #FilterEndDate').on('change', function() {
    $('#PODetailDiv').html('');
    loadOutstandingPOGrid();
});

$('#BtnResetDatePO').on('click', function() {
    $('#FilterStartDate').val(DEFAULT_START_DATE);
    $('#FilterEndDate').val(DEFAULT_END_DATE);
    $('#PODetailDiv').html('');
    loadOutstandingPOGrid();
});

// Double-click untuk melihat rincian item PO
POGrid.attachEvent("onRowDblClicked", function(poNo) {
    bukaDetailPO(poNo);
});

// Tombol Lihat Detail PO
$('#BtnOpenDetailPO').on('click', function() {
    let selectedId = POGrid.getSelectedRowId();
    if (!selectedId) {
        alert('Silakan pilih salah satu baris Purchase Order terlebih dahulu.');
        return;
    }
    bukaDetailPO(selectedId);
});

// Tombol Cetak PDF PO
$('#BtnPrintPO').on('click', function() {
    let selectedId = POGrid.getSelectedRowId();
    if (!selectedId) {
        alert('Silakan pilih salah satu baris Purchase Order terlebih dahulu.');
        return;
    }
    let printUrl = "{{ url('po-print') }}?PO_No=" + encodeURIComponent(selectedId);
    window.open(printUrl, '_blank');
});

// Tombol Refresh Grid
$('#BtnRefreshPO').on('click', function() {
    $('#PODetailDiv').html('');
    loadOutstandingPOGrid();
});
</script>
@endsection