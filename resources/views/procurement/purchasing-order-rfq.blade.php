@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Purchase Order</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">RFQ Quotation</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

    <!-- Stats Card -->
    <div class="row">
        <div id="RFQ" style="width:100%; height:450px; background-color:white; overflow:hidden"></div>
    </div>
<div id="RFQDiv"></div>

<script>
function RFQDecryptID() {
    // Ambil ID baris yang diklik (yaitu RFQ_No)
    var RFQMID = RFQ.getSelectedId();
    // Arahkan ke halaman input penawaran QTN berdasarkan No. RFQ
    LoadM('RFQDiv', 'qtn-create-detail?rfq=' + RFQMID);
}

var RFQ = new dhtmlXGridObject('RFQ');
RFQ.setImagePath("./dhtmlx/imgs/");
// Sesuaikan header dengan kolom tabel RFQ
RFQ.setHeader("No.,No. RFQ,No. PR,Vendor / Supplier,Tanggal RFQ,Status,Dibuat Oleh");
RFQ.setInitWidths("70,180,180,220,120,120,120");
RFQ.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
RFQ.setColAlign("center,center,center,left,center,center,center");
RFQ.setColTypes("ro,ro,ro,ro,ro,ro,ro");
RFQ.setColSorting("int,str,str,str,str,str,str");
RFQ.setEditable(false);
RFQ.enableMultiline(true);
RFQ.attachEvent("onRowDblClicked", RFQDecryptID);
RFQ.setSkin("dhx_skyblue");
RFQ.init();

// Memuat data XML dari endpoint Laravel
RFQ.loadXML("xml-purchasing-order-rfq");
</script>
@endsection
