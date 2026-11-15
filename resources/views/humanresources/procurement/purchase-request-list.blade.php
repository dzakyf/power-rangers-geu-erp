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
    <div id="PRDiv" class="mt-3"></div>

<script>
function PRDoubleClick() {
    var prId = PRGrid.getSelectedId();
    if (!prId) return;

    // Ambil status berdasarkan atribut row di grid DHTMLX
    var status = PRGrid.getUserData(prId, "status");
    
    // Jika tidak tertangkap via userData, kita bisa ambil dari cell index ke-8 (kolom Status)
    if (!status) {
        var cellIndexStatus = 8; // Kolom status ada di indeks ke-8
        status = PRGrid.cells(prId, cellIndexStatus).getValue();
    }

    // Kondisi pengarahan berdasarkan status
    if (status === 'PENDING') {
        LoadM('PRDiv', 'purchase-request-detail-pending?PR_No=' + prId);
    } else if (status === 'APPROVED') {
        LoadM('PRDiv', 'purchase-request-detail-approved?PR_No=' + prId);
    } else {
        // Status lain jika ada (misal: APPROVAL_UPPER, dll.)
        LoadM('PRDiv', 'purchase-request-detail-pending?PR_No=' + prId);
    }
}

var PRGrid = new dhtmlXGridObject('PRGrid');
PRGrid.setImagePath("./dhtmlx/imgs/");
PRGrid.setHeader("No.,No. PR,Tanggal PR,Department,Note,yang Mengajukan,Qty,Satuan,Status");
PRGrid.setInitWidths("70,180,110,130,250,220,80,80,110");
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
PRGrid.load("/geu/hcga/public/xml-purchase-request-list");
</script>
@endsection