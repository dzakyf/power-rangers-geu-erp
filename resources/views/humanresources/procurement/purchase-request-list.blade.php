@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="font-size: 12px;">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
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

        <button type="button" id="PrintPRProcurement" name="PrintPRProcurement" class="btn btn-dark btn-sm me-1">
                            <i class="fa fa-print me-1"></i> Cetak PR
        </button>
    </div>

    <!-- Grid Container -->
    <div class="row">
        <div id="PRGrid" style="width:100%; height:450px; background-color:white; overflow:hidden"></div>
    </div>
    <div id="PRDiv" class="mt-3"></div>

<script>

    function selectedPRPO() {
            let pmid = PRGrid.getSelectedRowId();
            if(pmid == null) { 
                alert('Silakan pilih salah satu baris PR terlebih dahulu.'); 
                return null; 
            }
            return {
                pr: PRGrid.cells(pmid,1).getValue(),
                // po: PRGrid.cells(pmid,5).getValue()
            };
        }

$('#PrintPRProcurement').on('click', function() {
    let s = selectedPRPO(); 
    if (!s) return;

    // Jika fungsi streamPdf sudah ada di mainlayout, gunakan streamPdf; jika tidak, buka di tab baru
    let printUrl = "{{ url('hrd-procurement-print-pr') }}?PR_No=" + encodeURIComponent(s.pr);
    
    if (typeof streamPdf === 'function') {
        streamPdf(printUrl, 'Cetak PR ' + s.pr);
    } else {
        window.open(printUrl, '_blank');
    }
});

function PRDoubleClick() {
    var prId = PRGrid.getSelectedId();
    if (!prId) return;

    // Ambil status berdasarkan atribut row di grid DHTMLX
    var status = PRGrid.getUserData(prId, "status");
    
    // Jika tidak tertangkap via userData, kita bisa ambil dari cell index ke-8 (kolom Status)
    if (!status) {
        var cellIndexStatus = 4; // Kolom status ada di indeks ke-8
        status = PRGrid.cells(prId, cellIndexStatus).getValue();
    }

    // Kondisi pengarahan berdasarkan status
    if (status === 'PENDING') {
        LoadM('PRDiv', 'purchase-request-detail-pending?PR_No=' + prId);
    } else if (status === 'APPROVED') {
        LoadM('PRDiv', 'purchase-request-detail-approved?PR_No=' + prId);
    } else {
        // Status lain jika ada (misal: APPROVAL_UPPER, dll.)
        LoadM('PRDiv', 'purchase-request-detail?PR_No=' + prId);
    }
}

var PRGrid = new dhtmlXGridObject('PRGrid');
PRGrid.setImagePath("./dhtmlx/imgs/");
PRGrid.setHeader("No.,No. PR,Date PR,Department,Status,Note,yang Mengajukan,Phone,Email,Site Destination,Qty,Satuan,Category,Estimasi Budget");
PRGrid.setInitWidths("70,180,110,130,130,150,250,150,150,150,80,70,100,100");
PRGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#select_filter,#select_filter");
PRGrid.setColAlign("center,center,center,center,center,left,center,center,center,center");
PRGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
PRGrid.setColSorting("int,str,str,str,str,str,str,str,str,int,str,str,str,str");
PRGrid.setEditable(false);
PRGrid.enableMultiline(true);
PRGrid.attachEvent("onRowDblClicked", PRDoubleClick);
PRGrid.setSkin("dhx_skyblue");
PRGrid.init();

// Memuat data XML dari endpoint Laravel
PRGrid.load("/geu/hcga/public/xml-purchase-request-list");
</script>
@endsection