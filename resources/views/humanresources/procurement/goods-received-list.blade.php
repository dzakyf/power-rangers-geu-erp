@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="container-fluid mt-3">
    <div class="card shadow-sm" style="border-radius: 8px;">
        <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
                <i class="fas fa-boxes mr-2"></i>Daftar Goods Received (Penerimaan Barang)
            </h6>
            <div>
                <button type="button" class="btn btn-sm btn-light font-weight-bold py-0 px-2" onclick="reloadGRGrid()">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                </button>
                <a href="{{ url('po-outstanding-list') }}" class="btn btn-sm btn-warning text-dark font-weight-bold py-0 px-2 ml-1">
                    <i class="fas fa-file-invoice mr-1"></i> ke Outstanding PO
                </a>
            </div>
        </div>

        <div class="card-body bg-white p-3">
            <div class="alert alert-light border py-1 px-2 mb-2" style="font-size: 11.5px;">
                <i class="fas fa-info-circle text-success mr-1"></i>
                Klik atau double-click baris pada tabel untuk melihat rincian item (<strong>good_receive_dtl</strong>) dari nomor GR tersebut.
            </div>

            <div id="GRGrid" style="width: 100%; height: 340px; background-color: white;"></div>
            <div id="GRDetailDiv"></div>
        </div>
    </div>
</div>

<script>
var GRGrid;

$(document).ready(function() {
    GRGrid = new dhtmlXGridObject('GRGrid');
    GRGrid.setImagePath("{{ asset('dhtmlx/codebase/imgs/') }}/");
    GRGrid.setHeader("No,No. GR,Tanggal Masuk,No. PO,No. PR,Vendor / Supplier,Ref / Surat Jalan,Total Qty Terima,Total Nilai (Rp),Status,Dibuat Oleh");
    GRGrid.setInitWidths("45,145,100,145,155,*,130,105,120,90,95");
    GRGrid.setColAlign("center,left,center,left,left,left,left,right,right,center,center");
    GRGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    GRGrid.setColSorting("int,str,date,str,str,str,str,int,int,str,str");
    GRGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#select_filter,#text_filter");
    GRGrid.init();
    GRGrid.load("{{ url('xml-goods-received-list') }}");

    GRGrid.attachEvent("onRowDblClicked", function(rowId) {
    loadDetailGR(rowId);
});

    GRGrid.attachEvent("onRowSelect", function(rowId) {
        loadDetailGR(rowId);
    });
});

function loadDetailGR(grNo) {
    if (!grNo) return;
    $('#GRDetailDiv').html('<div class="text-center py-3 text-muted" style="font-size:12px;">Memuat rincian item ' + grNo + '...</div>');
    $('#GRDetailDiv').load("{{ url('goods-received-detail') }}?GR_No=" + encodeURIComponent(grNo));
}

function reloadGRGrid() {
    $('#GRDetailDiv').html('');
    if (GRGrid) {
        GRGrid.clearAll();
        GRGrid.load("{{ url('xml-goods-received-list') }}");
    }
}
</script>
@endsection