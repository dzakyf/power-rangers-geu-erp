@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard1">Home</a></li>
            <li class="breadcrumb-item active"><span>Purchase Request (PR)</span></li>
        </ol>
    </div> 

    <!-- Tombol Buat PR Baru -->
    <div class="mb-3">
        <a href="{{ url('purchase-request-create') }}" class="btn btn-success btn-sm font-weight-bold">
            <i class="fas fa-plus mr-1"></i> Buat Purchase Request Baru
        </a>
    </div>

    <!-- Grid Container -->
    <div class="row">
        <div id="PRGrid" style="width:100%; height:450px; background-color:white; overflow:hidden"></div>
    </div>
    <div id="PRDiv"></div>

<script>
function PRDoubleClick() {
    var prId = PRGrid.getSelectedId();
    // Membuka detail PR saat baris diklik dua kali
    LoadM('PRDiv', 'purchase-request-detail?PR_No=' + prId);
}

var PRGrid = new dhtmlXGridObject('PRGrid');
PRGrid.setImagePath("./dhtmlx/imgs/");
PRGrid.setHeader("No.,No. PR,Tanggal PR,Department,Material Code,Nama Item,Qty,Satuan,Status");
PRGrid.setInitWidths("70,180,110,130,150,220,80,80,110");
PRGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
PRGrid.setColAlign("center,center,center,center,center,left,center,center,center");
PRGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
PRGrid.setColSorting("int,str,str,str,str,str,int,str,str");
PRGrid.setEditable(false);
PRGrid.enableMultiline(true);
PRGrid.attachEvent("onRowDblClicked", PRDoubleClick);
PRGrid.setSkin("dhx_skyblue");
PRGrid.init();

// Memuat data XML dari endpoint Laravel
// PRGrid.load("/xml-purchase-request-list");
PRGrid.load("/geu/hcga/public/xml-purchase-request-list");
</script>
@endsection