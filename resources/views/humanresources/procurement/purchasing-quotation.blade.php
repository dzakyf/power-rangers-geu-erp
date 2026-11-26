@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<style>
    .rfq-hero-banner {
        background: linear-gradient(135deg, #064E3B 0%, #047857 100%);
        border-radius: 12px;
        padding: 18px 22px;
        color: #FFFFFF;
        margin-bottom: 18px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 4px 14px rgba(6, 78, 59, 0.14);
    }
    .rfq-hero-title {
        font-size: 19px;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .rfq-hero-breadcrumb {
        font-size: 12px;
        color: #D1FAE5;
        margin-top: 4px;
    }
    .rfq-hero-breadcrumb a {
        color: #A7F3D0;
        text-decoration: none;
    }
    .rfq-card-panel {
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 18px 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    }
    .rfq-card-header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 14px;
    }
    .rfq-card-title {
        font-size: 15.5px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }
    .rfq-card-subtitle {
        font-size: 12px;
        color: #6B7280;
        margin-top: 2px;
    }
    .rfq-pill-badge {
        background: #0284C7;
        color: #FFFFFF;
        font-size: 11.5px;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .rfq-grid-wrap {
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        overflow: hidden;
    }
    .rfq-bottom-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid #F1F5F9;
    }
    .rfq-action-btn {
        border-radius: 999px !important;
        padding: 7px 16px !important;
        font-size: 12.5px !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>

<!-- Header Banner Hijau ala Dynamic Payroll Approval -->
<div class="rfq-hero-banner">
    <div>
        <h1 class="rfq-hero-title">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Quotation &amp; Vendor Quote</span>
        </h1>
        <div class="rfq-hero-breadcrumb">
            <a href="{{ url('procurement-dashboard') }}">Procurement</a> / <span>RFQ &amp; Vendor Quote</span>
        </div>
    </div>
</div>

<!-- Panel Card Putih -->
<div class="rfq-card-panel">
    <div class="rfq-card-header">
        <div>
            <h2 class="rfq-card-title">Daftar RFQ Multi-Vendor</h2>
            <div class="rfq-card-subtitle">
                Pilih satu baris RFQ pada tabel di bawah, lalu klik tombol aksi untuk input/lihat Quotation atau membuka Matrik Quotation.
            </div>
        </div>
        <span class="rfq-pill-badge">
            <i class="fas fa-layer-group"></i> Request Type: RFQ MULTI-VENDOR
        </span>
    </div>

    <!-- DHTMLX Grid Container -->
    <div class="rfq-grid-wrap">
        <div id="RFQ" style="width:100%; height:420px; background-color:white; overflow:hidden;"></div>
    </div>

    <!-- Bottom Action Bar (Tanpa Double Click) -->
    <div class="rfq-bottom-toolbar">
        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            <button type="button" id="BtnInputQuotationRFQ" class="btn btn-success rfq-action-btn shadow-sm" onclick="RFQDecryptID()">
                <i class="fas fa-edit"></i> Input / Lihat Quotation
            </button>

            <button type="button" id="BtnMatrikQuotationRFQ" class="btn btn-info rfq-action-btn shadow-sm" onclick="bukaMatriksPerbandingan(3)">
                <i class="fas fa-tasks"></i> Matrik Quotation
            </button>
        </div>

        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            <button type="button" id="BtnRefreshRFQGrid" class="btn btn-outline-secondary rfq-action-btn" onclick="refreshRFQGrid()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Kontainer tempat matriks akan dirender -->
<div id="ComparisonDiv" class="mt-3"></div>

<!-- CONTAINER DETAIL INPUT QUOTATION VENDOR -->
<div id="RFQDiv" class="mt-3"></div>

<script>
function getSelectedRFQRow() {
    var selectedRFQ = RFQ.getSelectedId();
    if (!selectedRFQ) {
        alert("Silakan klik / pilih salah satu baris RFQ di tabel terlebih dahulu!");
        return null;
    }
    return selectedRFQ;
}

function bukaMatriksPerbandingan(versi) {
    versi = versi || 3;
    var selectedRFQ = getSelectedRFQRow();
    if (!selectedRFQ) return;

    openComparisonMatrix(selectedRFQ, versi);
}

function openComparisonMatrix(rfqNo, versi) {
    versi = versi || 3;
    $('#RFQDiv').empty();
    $('#ComparisonDiv').html('<div class="text-center p-4 bg-white rounded border"><i class="fas fa-spinner fa-spin fa-2x text-success"></i><p class="mt-2 mb-0 small text-muted">Memuat Matriks Perbandingan Vendor...</p></div>');
    $('#ComparisonDiv').load("{{ url('vendor-comparison-matrix') }}?pr_no=" + encodeURIComponent(rfqNo) + "&v=" + encodeURIComponent(versi), function(response, status, xhr) {
        if (status == "error") {
            $('#ComparisonDiv').html('<div class="alert alert-danger">Gagal memuat matriks perbandingan.</div>');
        } else {
            $('html, body').animate({ scrollTop: $('#ComparisonDiv').offset().top - 70 }, 400);
        }
    });
}

function RFQDecryptID() {
    var RFQMID = getSelectedRFQRow();
    if (!RFQMID) return;

    $('#ComparisonDiv').empty();
    $('#RFQDiv').html('<div class="text-center p-4 bg-white rounded border"><i class="fas fa-spinner fa-spin fa-2x text-success"></i><p class="mt-2 mb-0 small text-muted">Memuat rincian RFQ &amp; Quotation...</p></div>');
    $('#RFQDiv').load("{{ url('qtn-create-detail') }}?rfq=" + encodeURIComponent(RFQMID), function() {
        $('html, body').animate({ scrollTop: $('#RFQDiv').offset().top - 70 }, 400);
    });
}

function refreshRFQGrid() {
    RFQ.clearAll();
    RFQ.load("{{ url('xml-purchasing-quotation') }}");
    $('#ComparisonDiv').empty();
    $('#RFQDiv').empty();
}

var RFQ = new dhtmlXGridObject('RFQ');
RFQ.setImagePath("./dhtmlx/imgs/");
RFQ.setHeader("No.,No. RFQ,No. PR,Vendor / Supplier,Tanggal RFQ,No. Quotation,Grand Total (Rp),Status,Dibuat Oleh");
RFQ.setInitWidths("55,165,165,220,105,140,140,105,105");
RFQ.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
RFQ.setColAlign("center,center,center,left,center,center,right,center,center");
RFQ.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
RFQ.setColSorting("int,str,str,str,str,str,str,str,str");
RFQ.setEditable(false);
RFQ.enableMultiline(true);
RFQ.setSkin("dhx_skyblue");
RFQ.init();

// Memuat data XML dari endpoint Laravel
RFQ.load("{{ url('xml-purchasing-quotation') }}");
</script>
@endsection