@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Procurement</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ url('cns-procurement-purchase-order') }}">Purchase Order</a></li>
        <li class="breadcrumb-item active"><span class="active">Goods Receipt</span></li>
    </ol>
</div>

<style>
    #GRGrid .hdr tr:first-child td {
        background-color: #174375 !important;
        color: #ffffff !important;
        font-weight: bold !important;
        text-align: center !important;
        vertical-align: middle !important;
        border-right: 1px solid #ffffff !important;
        font-size: 12px !important;
    }
    #GRGrid td {
        font-size: 12px !important;
        vertical-align: middle !important;
    }
    .badge-status-draft { background-color: #ffc107; color: #212529; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
    .badge-status-approved { background-color: #28a745; color: #ffffff; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
    .badge-status-void { background-color: #dc3545; color: #ffffff; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
</style>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">GOODS RECEIPT (PENERIMAAN BARANG NON-UCO)</div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show w-100" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card w-100">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-end flex-wrap gap-2">
                    <div class="m-1">
                        <label class="d-block mb-1"><small>Dari Tanggal</small></label>
                        <input type="date" id="filterStartDate" class="form-control form-control-sm" style="width:145px;" />
                    </div>
                    <div class="m-1">
                        <label class="d-block mb-1"><small>Sampai Tanggal</small></label>
                        <input type="date" id="filterEndDate" class="form-control form-control-sm" style="width:145px;" />
                    </div>
                    <div class="m-1">
                        <label class="d-block mb-1"><small>Status</small></label>
                        <select id="filterStatus" class="form-control form-control-sm" style="width:130px;">
                            <option value="">Semua Status</option>
                            <option value="DRAFT">DRAFT</option>
                            <option value="APPROVED">APPROVED</option>
                            <option value="VOID">VOID</option>
                        </select>
                    </div>
                    <div class="m-1">
                        <button class="btn btn-sm btn-primary" onclick="applyFilters()">Filter</button>
                        <button class="btn btn-sm btn-secondary ml-1" onclick="resetFilters()">Reset</button>
                    </div>
                </div>
            </div>

            <div id="GRGrid" style="width:100%;height:450px; background-color:white;overflow:hidden" class="mt-2"></div>
            <div class="btn btn-primary mt-2 mr-2">
                <a href="{{ route('cns-procurement-goods-receipt.create') }}">
                Buat GR
                </a>
            </div>
            <div class="mt-2 text-muted small">
                <i class="fa fa-info-circle"></i> Double-click baris tabel untuk melihat detail, verifikasi/approval, atau cetak Berita Acara Penerimaan Barang.
            </div>
        </div>
    </div>
</div>

<script>
    var GRGrid;
    $(document).ready(function() {
        initGRGrid();
        loadGridData();
    });

    function initGRGrid() {
        GRGrid = new dhtmlXGridObject('GRGrid');
        GRGrid.setImagePath("./dhtmlx/imgs/");
        GRGrid.setHeader("No,No. GR,No. PO,Supplier,Tanggal Terima,No. Surat Jalan,Driver / Plat,Item Diterima,Status,Diterima Oleh,Disetujui Oleh");
        GRGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter");
        GRGrid.setInitWidths("45,150,150,180,105,140,150,*,95,110,110");
        GRGrid.setColAlign("center,center,center,left,center,left,left,left,center,center,center");
        GRGrid.setColSorting("int,str,str,str,date,str,str,str,str,str,str");
        GRGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
        GRGrid.enableSmartRendering(true);
        GRGrid.attachEvent("onRowDblClicked", function(rowId) {
            window.location.href = "{{ url('cns-procurement-goods-receipt-detail') }}/" + rowId;
        });
        GRGrid.init();
    }

    function loadGridData() {
        var params = [];
        var start = $('#filterStartDate').val();
        var end   = $('#filterEndDate').val();
        var status = $('#filterStatus').val();

        if (start) params.push('start_date=' + encodeURIComponent(start));
        if (end)   params.push('end_date=' + encodeURIComponent(end));
        if (status) params.push('status=' + encodeURIComponent(status));

        var url = "{{ route('cns-procurement-goods-receipt.xml') }}" + (params.length ? '?' + params.join('&') : '');
        GRGrid.clearAll();
        GRGrid.load(url);
    }

    function applyFilters() {
        loadGridData();
    }

    function resetFilters() {
        $('#filterStartDate').val('');
        $('#filterEndDate').val('');
        $('#filterStatus').val('');
        loadGridData();
    }
</script>
@endsection
