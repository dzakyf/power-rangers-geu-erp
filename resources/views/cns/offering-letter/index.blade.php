@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
    'title' => 'Offering Letter',
    'breadcrumbs' => [
        ['label' => 'Sales & Commercial'],
        ['label' => 'Pricing & Quotation'],
        ['label' => 'Offering Letter'],
    ],
])

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title">Daftar Offering Letter</h5>
        <div class="d-flex flex-wrap" style="gap: 8px;">
            <a href="{{ route('cns-offering-letter.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus-circle"></i> New Offering Letter
            </a>
            <button type="button" class="btn btn-info btn-sm" onclick="openSelectedDetail()">
                <i class="fa fa-eye"></i> Open Detail
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="printSelected()">
                <i class="fa fa-print"></i> Print
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row cns-filter-bar">
            <div class="col-md-2">
                <label for="filterSeller" class="small font-weight-bold mb-1">Seller</label>
                <select id="filterSeller" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    @foreach($sellers as $seller)
                        <option value="{{ $seller->code }}">{{ $seller->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filterStatus" class="small font-weight-bold mb-1">Status</label>
                <select id="filterStatus" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    <option value="DRAFT">DRAFT</option>
                    <option value="APPROVED">APPROVED</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filterStartDate" class="small font-weight-bold mb-1">Dari Tanggal</label>
                <input type="date" id="filterStartDate" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label for="filterEndDate" class="small font-weight-bold mb-1">Sampai Tanggal</label>
                <input type="date" id="filterEndDate" class="form-control form-control-sm">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="button" class="btn btn-sm btn-primary mr-2" onclick="loadGridData()">Filter</button>
                <button type="button" class="btn btn-sm btn-light" onclick="resetFilters()">Reset</button>
            </div>
        </div>

        <div id="OLGridWrapper" class="cns-grid-wrapper">
            <div id="OLGrid" class="cns-grid" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>

<script>
    var OLGrid;
    var OLPagination;

    $(document).ready(function () {
        OLGrid = new dhtmlXGridObject('OLGrid');
        OLGrid.setImagePath("./dhtmlx/imgs/");
        OLGrid.setSkin("dhx_skyblue");
        OLGrid.setHeader("No,No. Offer,Tanggal,Seller,Buyer,Quantity,Price/MT,Delivery Term,Loading Period,Valid Until,Status");
        OLGrid.attachHeader("#rspan,#text_filter,#rspan,#select_filter,#text_filter,#rspan,#rspan,#text_filter,#text_filter,#rspan,#select_filter");
        OLGrid.setInitWidths("45,180,95,190,*,105,115,130,120,105,95");
        OLGrid.setColAlign("center,center,center,left,left,right,right,left,center,center,center");
        OLGrid.setColSorting("int,str,date,str,str,str,str,str,str,date,str");
        OLGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,cns_badge");
        OLGrid.attachEvent("onRowDblClicked", function (rowId) {
            window.location.href = "{{ url('cns-offering-letter-detail') }}/" + rowId;
        });
        OLGrid.init();

        OLPagination = initCnsGridPagination(OLGrid, {
            pageSize: 25,
            pageSizeOptions: [10, 25, 50, 100],
            getUrl: function (params) {
                var q = [];
                q.push('posStart=' + params.posStart);
                q.push('count=' + params.count);

                var seller = $('#filterSeller').val();
                var status = $('#filterStatus').val();
                var start  = $('#filterStartDate').val();
                var end    = $('#filterEndDate').val();

                if (seller) q.push('seller_code=' + encodeURIComponent(seller));
                if (status) q.push('status=' + encodeURIComponent(status));
                if (start)  q.push('start_date=' + encodeURIComponent(start));
                if (end)    q.push('end_date=' + encodeURIComponent(end));

                if (params.sortCol !== null && params.sortCol !== undefined) {
                    q.push('sort_col=' + params.sortCol);
                    q.push('sort_dir=' + (params.sortDir || 'asc'));
                }

                if (params.filters) {
                    for (var colIdx in params.filters) {
                        q.push('filters[' + colIdx + ']=' + encodeURIComponent(params.filters[colIdx]));
                    }
                }

                return "{{ route('cns-offering-letter.xml') }}?" + q.join('&');
            }
        });

        loadGridData();

        $(window).on('resize', function () {
            if (OLGrid) OLGrid.setSizes();
        });
    });

    function loadGridData() {
        if (OLPagination) {
            OLPagination.reload(true);
        }
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
            swal('Perhatian', 'Pilih data terlebih dahulu.', 'warning');
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
            swal('Peringatan', 'Pilih baris data terlebih dahulu', 'warning');
        }
        return true;
    }
</script>
@endsection
