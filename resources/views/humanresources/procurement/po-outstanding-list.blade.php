@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard1">Home</a></li>
            <li class="breadcrumb-item active"><span>Outstanding PO</span></li>
        </ol>
    </div>

    <div class="mb-3 d-flex align-items-center">
        <button type="button" id="BtnPrintPO" class="btn btn-dark btn-sm font-weight-bold mr-2">
            <i class="fa fa-print mr-1"></i> Cetak Purchase Order (PDF)
        </button>
        <button type="button" id="BtnRefreshPO" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-sync-alt mr-1"></i> Refresh Data
        </button>
    </div>

    <!-- Grid Container -->
    <div class="row">
        <div id="POGrid" style="width:100%; height:420px; background-color:white; overflow:hidden"></div>
    </div>

    <!-- Container Detail Item PO -->
    <div id="PODetailDiv" class="mt-3"></div>

<script>
var POGrid = new dhtmlXGridObject('POGrid');
POGrid.setImagePath("./dhtmlx/imgs/");
POGrid.setHeader("No.,No. PO,Tanggal PO,Ref. PR,Vendor / Supplier,Department,Termin,Grand Total (Rp),Status PO,Status Receive,Dibuat Oleh");
POGrid.setInitWidths("50,165,100,165,240,100,95,130,95,105,100");
POGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#select_filter,#select_filter,#text_filter");
POGrid.setColAlign("center,center,center,center,left,center,center,right,center,center,center");
POGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
POGrid.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
POGrid.setEditable(false);
POGrid.enableMultiline(true);
POGrid.setSkin("dhx_skyblue");
POGrid.init();

// Muat data Outstanding PO
POGrid.load("{{ url('xml-outstanding-po-list') }}");

// Double-click untuk melihat rincian item PO
POGrid.attachEvent("onRowDblClicked", function(poNo) {
    if (!poNo) return;
    $('#PODetailDiv').html('<div class="alert alert-info py-2">Memuat rincian Purchase Order...</div>');
    $('#PODetailDiv').load("{{ url('po-detail') }}?PO_No=" + encodeURIComponent(poNo));
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
    POGrid.clearAll();
    POGrid.load("{{ url('xml-outstanding-po-list') }}");
    $('#PODetailDiv').html('');
});
</script>
@endsection