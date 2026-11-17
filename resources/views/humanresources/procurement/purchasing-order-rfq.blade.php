@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('procurement-dashboard') }}">Procurement</a></li>
            <li class="breadcrumb-item active"><span>Request For Quotation (RFQ) & Vendor Quote</span></li>
        </ol>
    </div> 

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
                <i class="fas fa-file-invoice-dollar mr-2"></i>Daftar RFQ Multi-Vendor &mdash; <span class="text-warning">Tips: Double-klik pada baris untuk menginput / melihat penawaran harga (Quotation)</span>
            </h6>
        </div>

        
        <div class="card-body p-0">
            <div id="RFQ" style="width:100%; height:420px; background-color:white; overflow:hidden"></div>
        </div>

    </div>
    <button type="button" class="btn btn-sm btn-success font-weight-bold mb-2" onclick="bukaMatriksPerbandingan()">
            <i class="fas fa-balance-scale mr-1"></i> Lihat Matriks Perbandingan (Berdasarkan RFQ Dipilih)
        </button>



    <!-- Kontainer tempat matriks akan dirender -->
<div id="ComparisonDiv"></div>

    <!-- CONTAINER DETAIL INPUT QUOTATION VENDOR -->
    <div id="RFQDiv"></div>


    

<script>
function bukaMatriksPerbandingan() {
    var selectedRFQ = RFQ.getSelectedId();
    if (!selectedRFQ) {
        alert("Silakan klik / pilih salah satu baris RFQ di tabel terlebih dahulu!");
        return;
    }
    
    // Panggil fungsi openComparisonMatrix menggunakan ID RFQ yang diklik
    openComparisonMatrix(selectedRFQ);
}

// DEFINISIKAN FUNGSI openComparisonMatrix DI SINI
function openComparisonMatrix(rfqNo) {
    $('#ComparisonDiv').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x text-success"></i><p class="mt-2">Memuat Matriks Perbandingan Vendor...</p></div>');
    $('#ComparisonDiv').load("{{ url('vendor-comparison-matrix') }}?pr_no=" + encodeURIComponent(rfqNo), function(response, status, xhr) {
        if (status == "error") {
            $('#ComparisonDiv').html('<div class="alert alert-danger">Gagal memuat matriks perbandingan.</div>');
        } else {
            $('html, body').animate({ scrollTop: $('#ComparisonDiv').offset().top - 70 }, 400);
        }
    });
}

function RFQDecryptID() {
    var RFQMID = RFQ.getSelectedId();
    if (!RFQMID) return;
    // Muat form input penawaran vendor ke dalam RFQDiv
    $('#RFQDiv').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2">Memuat rincian RFQ & Quotation...</p></div>');
    $('#RFQDiv').load("{{ url('qtn-create-detail') }}?rfq=" + encodeURIComponent(RFQMID), function() {
        $('html, body').animate({ scrollTop: $('#RFQDiv').offset().top - 70 }, 400);
    });
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
RFQ.attachEvent("onRowDblClicked", RFQDecryptID);
RFQ.setSkin("dhx_skyblue");
RFQ.init();

// Memuat data XML dari endpoint Laravel
RFQ.load("{{ url('xml-purchasing-order-rfq') }}");
</script>
@endsection