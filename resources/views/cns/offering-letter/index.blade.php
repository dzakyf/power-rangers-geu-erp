@extends('layout.mainlayout-cns')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Document Control</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">Contract</a></li>
        <li class="breadcrumb-item active"><span class="active">Offering Letter</span></li>
    </ol>
</div>

<style>
    #OLGridWrapper {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }
    #OLGrid {
        box-sizing: border-box !important;
        max-width: 100% !important;
    }
    #OLGrid .xhdr {
        height: auto !important;
        min-height: 0 !important;
    }
    #OLGrid table.hdr td {
        color: #000000 !important;
        font-weight: bold !important;
        text-align: center !important;
        vertical-align: middle !important;
        font-size: 12px !important;
        padding: 5px 6px !important;
        line-height: 1.2 !important;
        height: auto !important;
    }
    #OLGrid table.hdr td.filter {
        padding: 3px 4px !important;
        font-weight: normal !important;
    }
    #OLGrid table.hdr td div.hdrcell {
        padding: 0 4px !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
    }
    #OLGrid table.hdr td.filter input,
    #OLGrid table.hdr td.filter select {
        width: 100% !important;
        height: 22px !important;
        padding: 1px 4px !important;
        font-size: 11px !important;
        line-height: 1.2 !important;
        box-sizing: border-box !important;
    }
    #OLGrid td {
        font-size: 12px !important;
        vertical-align: middle !important;
    }
</style>

<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-md-2">
                <label for="filterSeller" class="mb-1">Seller</label>
                <select id="filterSeller" class="form-control">
                    <option value="">Semua</option>
                    @foreach($sellers as $seller)
                        <option value="{{ $seller->code }}">{{ $seller->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filterStatus" class="mb-1">Status</label>
                <select id="filterStatus" class="form-control">
                    <option value="">Semua</option>
                    <option value="DRAFT">DRAFT</option>
                    <option value="APPROVED">APPROVED</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filterStartDate" class="mb-1">Dari Tanggal</label>
                <input type="date" id="filterStartDate" class="form-control">
            </div>
            <div class="col-md-2">
                <label for="filterEndDate" class="mb-1">Sampai Tanggal</label>
                <input type="date" id="filterEndDate" class="form-control">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="button" class="btn btn-primary mr-2" onclick="loadGridData()">Filter</button>
                <button type="button" class="btn btn-light" onclick="resetFilters()">Reset</button>
            </div>
        </div>

        <div id="OLGridWrapper">
            <div id="OLGrid" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
        </div>

        <div class="mt-3">
            <a href="{{ route('cns-offering-letter.create') }}" class="btn btn-primary">New Offering Letter</a>
            <button type="button" class="btn btn-info" onclick="openSelectedDetail()">Open Detail</button>
            <button type="button" class="btn btn-secondary" onclick="printSelected()">Print</button>
        </div>
    </div>
</div>

<script>
    var OLGrid;

    $(document).ready(function () {
        OLGrid = new dhtmlXGridObject('OLGrid');
        OLGrid.setImagePath("./dhtmlx/imgs/");
        OLGrid.setSkin("dhx_skyblue");
        OLGrid.setHeader("No,No. Offer,Tanggal,Seller,Buyer,Quantity,Price/MT,Delivery Term,Loading Period,Valid Until,Status");
        OLGrid.attachHeader("#rspan,#text_filter,#rspan,#select_filter,#text_filter,#rspan,#rspan,#text_filter,#text_filter,#rspan,#select_filter");
        OLGrid.setInitWidths("45,180,95,190,*,105,115,130,120,105,95");
        OLGrid.setColAlign("center,center,center,left,left,right,right,left,center,center,center");
        OLGrid.setColSorting("int,str,date,str,str,str,str,str,str,date,str");
        OLGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
        OLGrid.enableSmartRendering(true);
        OLGrid.attachEvent("onRowDblClicked", function (rowId) {
            window.location.href = "{{ url('cns-offering-letter-detail') }}/" + rowId;
        });
        OLGrid.init();
        loadGridData();

        $(window).on('resize', function () {
            if (OLGrid) OLGrid.setSizes();
        });
    });

    function loadGridData() {
        var params = [];
        var seller = $('#filterSeller').val();
        var status = $('#filterStatus').val();
        var start  = $('#filterStartDate').val();
        var end    = $('#filterEndDate').val();

        if (seller) params.push('seller_code=' + encodeURIComponent(seller));
        if (status) params.push('status=' + encodeURIComponent(status));
        if (start)  params.push('start_date=' + encodeURIComponent(start));
        if (end)    params.push('end_date=' + encodeURIComponent(end));

        var url = "{{ route('cns-offering-letter.xml') }}" + (params.length ? '?' + params.join('&') : '');
        OLGrid.clearAll();
        OLGrid.load(url, function () {
            if (OLGrid) OLGrid.setSizes();
        });
    }

    function resetFilters() {
        $('#filterSeller').val('');
        $('#filterStatus').val('');
        $('#filterStartDate').val('');
        $('#filterEndDate').val('');
        loadGridData();
    }

    function openSelectedDetail() {
        var rowId = OLGrid.getSelectedRowId();
        if (!rowId) {
            alert('Pilih data terlebih dahulu.');
            return;
        }
        window.location.href = "{{ url('cns-offering-letter-detail') }}/" + rowId;
    }

    function printSelected() {
        var rowId = OLGrid.getSelectedRowId();
        if (rowId != null && rowId !== '') {
            var offerNo = OLGrid.cells(rowId, 1).getValue();
            window.open('{{ url("cns-offering-letter-print") }}?Task=' + encodeURIComponent(offerNo), '_blank');
        } else {
            if (typeof dhtmlx !== 'undefined' && dhtmlx.alert) {
                dhtmlx.alert({
                    title: "Peringatan",
                    text: "Pilih baris data terlebih dahulu",
                    ok: "OK"
                });
            } else {
                alert('Pilih baris data terlebih dahulu');
            }
        }
        return true;
    }
</script>
@endsection
