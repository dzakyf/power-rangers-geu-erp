@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
@include('cns.partials.page-header', [
    'title' => 'Inventory Flexi Bag',
    'breadcrumbs' => [
        ['label' => 'Inventory Flexi Bag'],
    ],
])

<style>
    .badge-balance {
        font-size: 13px;
        font-weight: bold;
        background-color: #e3f2fd;
        color: #0d47a1;
        padding: 5px 10px;
        border-radius: 4px;
        border: 1px solid #90caf9;
    }
</style>

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title">Inventory Flexi Bag (Mutasi Stok)</h5>
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
            <span class="badge-balance" id="initialBalanceWrapper" style="display:none; background-color:#fff3e0; color:#e65100; border-color:#ffe082;">
                Saldo Awal Periode: <span id="initialBalanceDisplay">0</span> Pcs
            </span>
            <span class="badge-balance">
                Sisa Stok Saat Ini: <span id="currentBalanceDisplay">{{ number_format((float) $currentBalance, 2, '.', ',') }}</span> Pcs
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="cns-filter-bar">
            <div>
                <label class="d-block mb-1"><small>Dari Tanggal</small></label>
                <input type="date" id="filterStartDate" class="form-control form-control-sm" style="width:145px;" />
            </div>
            <div>
                <label class="d-block mb-1"><small>Sampai Tanggal</small></label>
                <input type="date" id="filterEndDate" class="form-control form-control-sm" style="width:145px;" />
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-primary" onclick="applyFilters()">Filter</button>
                <button type="button" class="btn btn-sm btn-secondary ml-1" onclick="resetFilters()">Reset</button>
            </div>
        </div>

        <div class="cns-grid-wrapper">
            <div id="FlexibagGrid" class="cns-grid" style="width:100%;height:450px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>

<script>
    var FlexibagGrid;
    var FlexibagPagination;

    $(document).ready(function() {
        initDefaultDateRange();
        initFlexibagGrid();
    });

    function initDefaultDateRange() {
        var now = new Date();
        var yyyy = now.getFullYear();
        var mm = String(now.getMonth() + 1).padStart(2, '0');
        var dd = String(now.getDate()).padStart(2, '0');

        var firstDay = yyyy + '-' + mm + '-01';
        var currentDay = yyyy + '-' + mm + '-' + dd;

        $('#filterStartDate').val(firstDay);
        $('#filterEndDate').val(currentDay);
    }

    function initFlexibagGrid() {
        FlexibagGrid = new dhtmlXGridObject('FlexibagGrid');
        FlexibagGrid.setImagePath("./dhtmlx/imgs/");
        FlexibagGrid.setHeader("No,Tanggal,No Referensi (Booking/Kontrak),Tipe Mutasi,Qty IN (Pcs),Qty OUT (Pcs),Saldo Akhir (Pcs),Keterangan,Dicatat Oleh");
        FlexibagGrid.attachHeader("#rspan,#text_filter,#text_filter,#select_filter,#rspan,#rspan,#rspan,#text_filter,#text_filter");
        FlexibagGrid.setInitWidths("50,110,220,100,120,120,130,*,140");
        FlexibagGrid.setColAlign("center,center,left,center,right,right,right,left,left");
        FlexibagGrid.setColSorting("int,date,str,str,int,int,int,str,str");
        FlexibagGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
        FlexibagGrid.init();

        FlexibagPagination = initCnsGridPagination(FlexibagGrid, {
            pageSize: 25,
            pageSizeOptions: [10, 25, 50, 100],
            getUrl: function (params) {
                var q = [];
                q.push('posStart=' + params.posStart);
                q.push('count=' + params.count);

                var start = $('#filterStartDate').val();
                var end = $('#filterEndDate').val();
                if (start) q.push('start_date=' + encodeURIComponent(start));
                if (end)   q.push('end_date=' + encodeURIComponent(end));

                if (params.sortCol !== null && params.sortCol !== undefined) {
                    q.push('sort_col=' + params.sortCol);
                    q.push('sort_dir=' + (params.sortDir || 'asc'));
                }

                if (params.filters) {
                    for (var colIdx in params.filters) {
                        q.push('filters[' + colIdx + ']=' + encodeURIComponent(params.filters[colIdx]));
                    }
                }

                return 'xml-cns-inventory-flexibag?' + q.join('&');
            },
            onLoaded: function () {
                var start = $('#filterStartDate').val();
                var end = $('#filterEndDate').val();
                var params = [];
                if (start) params.push('start_date=' + encodeURIComponent(start));
                if (end)   params.push('end_date=' + encodeURIComponent(end));

                var balanceUrl = 'cns-inventory-flexibag-balance' + (params.length ? '?' + params.join('&') : '');
                $.get(balanceUrl, function(res) {
                    if (res && res.balance !== undefined) {
                        $('#currentBalanceDisplay').text(Number(res.balance).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    }
                    if (res && res.initial_balance !== undefined && start) {
                        $('#initialBalanceDisplay').text(Number(res.initial_balance).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                        $('#initialBalanceWrapper').show();
                    } else {
                        $('#initialBalanceWrapper').hide();
                    }
                });
            }
        });

        applyFilters();
    }

    function applyFilters() {
        if (FlexibagPagination) {
            FlexibagPagination.reload(true);
        }
    }

    function resetFilters() {
        initDefaultDateRange();
        applyFilters();
    }
</script>
@endsection
