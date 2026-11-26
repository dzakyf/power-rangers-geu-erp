@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<style>
    .pr-hero-banner {
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
    .pr-hero-title {
        font-size: 19px;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pr-hero-breadcrumb {
        font-size: 12px;
        color: #D1FAE5;
        margin-top: 4px;
    }
    .pr-hero-breadcrumb a {
        color: #A7F3D0;
        text-decoration: none;
    }
    .pr-hero-actions .btn {
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        padding: 7px 14px;
    }
    .pr-card-panel {
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 18px 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    }
    .pr-card-header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 14px;
    }
    .pr-card-title {
        font-size: 15.5px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }
    .pr-card-subtitle {
        font-size: 12px;
        color: #6B7280;
        margin-top: 2px;
    }
    .pr-pill-badge {
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
    .pr-grid-wrap {
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        overflow: hidden;
    }
    .pr-bottom-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid #F1F5F9;
    }
    .pr-action-btn {
        border-radius: 999px !important;
        padding: 7px 16px !important;
        font-size: 12.5px !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="font-size: 12.5px; border-radius: 10px;">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Header Banner Hijau ala Dynamic Payroll Approval -->
<div class="pr-hero-banner">
    <div>
        <h1 class="pr-hero-title">
            <i class="fas fa-file-invoice"></i>
            <span>Daftar Purchase Request (Procurement PR)</span>
        </h1>
        <div class="pr-hero-breadcrumb">
            <a href="/dashboard1">Procurement</a> / <span>Purchase Request (PR)</span>
        </div>
    </div>

    <div class="pr-hero-actions d-flex align-items-center gap-2">
        <a href="{{ url('purchase-request-create') }}" class="btn btn-light text-success shadow-sm">
            <i class="fas fa-plus-circle mr-1"></i> Buat Purchase Request Baru
        </a>
    </div>
</div>

<!-- Panel Card Putih -->
<div class="pr-card-panel">
    <div class="pr-card-header">
        <div>
            <h2 class="pr-card-title">Daftar Pengajuan Purchase Request</h2>
            <div class="pr-card-subtitle">
                Pilih satu baris Purchase Request pada tabel di bawah, lalu klik tombol <strong>Cek Detail PR</strong> untuk melihat tahapan approval &amp; rincian item.
            </div>
        </div>
        <span class="pr-pill-badge">
            <i class="fas fa-layer-group"></i> Request Type: PROCUREMENT PR
        </span>
    </div>

    <!-- DHTMLX Grid Container -->
    <div class="pr-grid-wrap">
        <div id="PRGrid" style="width:100%; height:440px; background-color:white; overflow:hidden;"></div>
    </div>

    <!-- Bottom Action Bar (Tanpa Double Click) -->
    <div class="pr-bottom-toolbar">
        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            <button type="button" id="BtnCekDetailPR" class="btn btn-success pr-action-btn shadow-sm">
                <i class="fas fa-eye"></i> Cek Detail PR &amp; Tahapan
            </button>

            <button type="button" id="PrintPRProcurement" name="PrintPRProcurement" class="btn btn-dark pr-action-btn shadow-sm">
                <i class="fas fa-print"></i> Cetak PR
            </button>

            <button type="button" id="BtnEditCancelPR" class="btn btn-warning text-dark pr-action-btn shadow-sm">
                <i class="fas fa-edit"></i> Edit / Batalkan PR
            </button>

        </div>

        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            <button type="button" id="BtnRefreshPRGrid" class="btn btn-outline-secondary pr-action-btn">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Container Hasil Load Detail PR -->
<div id="PRDiv" class="mt-3"></div>

<script>

function getSelectedPRStatus(rowId) {
    let status = PRGrid.getUserData(rowId, "status");
    if (!status) {
        status = PRGrid.cells(rowId, 4).getValue();
    }
    return String(status || '').replace(/<[^>]*>/g, '').trim().toUpperCase();
}

$('#BtnEditCancelPR').on('click', function() {
    let s = selectedPRPO();
    if (!s) return;

    let status = getSelectedPRStatus(s.rowId);
    if (status !== 'PENDING') {
        alert('Aksi Edit / Batalkan PR hanya tersedia untuk status PENDING. Status PR ini saat ini: ' + status);
        return;
    }

    // Muat form/detail PENDING ke #PRDiv untuk diedit atau dibatalkan
    LoadM('PRDiv', 'purchase-request-detail-editpr?PR_No=' + encodeURIComponent(s.rowId));

    setTimeout(function() {
        let detailEl = document.getElementById('PRDiv');
        if (detailEl) {
            detailEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 250);
});



    
    function selectedPRPO() {
        let pmid = PRGrid.getSelectedRowId();
        if (pmid == null) {
            alert('Silakan pilih salah satu baris PR terlebih dahulu.');
            return null;
        }
        return {
            rowId: pmid,
            pr: PRGrid.cells(pmid, 1).getValue()
        };
    }

    function openSelectedPRDetail() {
        let s = selectedPRPO();
        if (!s) return;

        let prId = s.rowId;
        let status = PRGrid.getUserData(prId, "status");

        if (!status) {
            status = PRGrid.cells(prId, 4).getValue();
        }
        status = String(status || '').trim().toUpperCase();

        if (status === 'PENDING' || status === 'RETURNED') {
            LoadM('PRDiv', 'purchase-request-detail-pending?PR_No=' + encodeURIComponent(prId));
        } else if (status === 'APPROVED') {
            LoadM('PRDiv', 'purchase-request-detail-approved?PR_No=' + encodeURIComponent(prId));
        } else {
            LoadM('PRDiv', 'purchase-request-detail?PR_No=' + encodeURIComponent(prId));
        }

        // Scroll halus ke panel detail PR di bawah
        setTimeout(function() {
            let detailEl = document.getElementById('PRDiv');
            if (detailEl) {
                detailEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 250);
    }

    $('#BtnCekDetailPR').on('click', function() {
        openSelectedPRDetail();
    });

    $('#PrintPRProcurement').on('click', function() {
        let s = selectedPRPO();
        if (!s) return;

        let printUrl = "{{ url('hrd-procurement-print-pr') }}?PR_No=" + encodeURIComponent(s.pr);

        if (typeof streamPdf === 'function') {
            streamPdf(printUrl, 'Cetak PR ' + s.pr);
        } else {
            window.open(printUrl, '_blank');
        }
    });

    $('#BtnRefreshPRGrid').on('click', function() {
        PRGrid.clearAll();
        PRGrid.load("/geu/hcga/public/xml-purchase-request-list");
        $('#PRDiv').empty();
    });

    var PRGrid = new dhtmlXGridObject('PRGrid');
    PRGrid.setImagePath("./dhtmlx/imgs/");
    PRGrid.setHeader("No.,No. PR,Date PR,Department,Status,Note,Requested by,Phone,Email,Site Destination,Qty,Satuan,Category,Estimasi Budget");
    PRGrid.setInitWidths("60,180,105,125,120,170,180,130,170,130,70,70,100,120");
    PRGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter,#select_filter,#select_filter,#text_filter");
    PRGrid.setColAlign("center,center,center,center,center,left,center,center,center,center,right,center,center,right");
    PRGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    PRGrid.setColSorting("int,str,str,str,str,str,str,str,str,str,int,str,str,str");
    PRGrid.setEditable(false);
    PRGrid.enableMultiline(true);
    PRGrid.setSkin("dhx_skyblue");
    PRGrid.init();

    // Memuat data XML dari endpoint Laravel
    PRGrid.load("/geu/hcga/public/xml-purchase-request-list");
</script>
@endsection