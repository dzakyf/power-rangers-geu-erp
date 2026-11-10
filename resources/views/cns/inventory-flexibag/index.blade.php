@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Inventory</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ url('home-cns') }}">Home</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Inventory Flexi Bag</span>
        </li>
    </ol>
</div>

<style>
    #FlexibagGrid .hdr tr:first-child td {
        background-color: #174375 !important;
        color: #ffffff !important;
        font-weight: bold !important;
        text-align: center !important;
        vertical-align: middle !important;
        border-right: 1px solid #ffffff !important;
        font-size: 12px !important;
    }
    #FlexibagGrid td {
        font-size: 12px !important;
        vertical-align: middle !important;
    }
    .badge-balance {
        font-size: 14px;
        font-weight: bold;
        background-color: #e3f2fd;
        color: #0d47a1;
        padding: 6px 12px;
        border-radius: 4px;
        border: 1px solid #90caf9;
    }
</style>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">INVENTORY FLEXI BAG (MUTASI STOK)</div>
    <div class="card">
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
                        <button class="btn btn-sm btn-primary" onclick="applyFilters()">Filter</button>
                        <button class="btn btn-sm btn-secondary ml-1" onclick="resetFilters()">Reset</button>
                    </div>
                </div>
                <div class="m-1 d-flex align-items-center flex-wrap gap-2">
                    <span class="badge-balance mr-2" id="initialBalanceWrapper" style="display:none; background-color:#fff3e0; color:#e65100; border-color:#ffe082;">
                        Saldo Awal Periode: <span id="initialBalanceDisplay">0</span> Pcs
                    </span>
                    <span class="badge-balance">
                        Sisa Stok Saat Ini: <span id="currentBalanceDisplay">{{ number_format($currentBalance, 0, ',', '.') }}</span> Pcs
                    </span>
                </div>
            </div>

            <div id="FlexibagGrid" style="width:100%;height:450px; background-color:white;overflow:hidden" class="mt-2"></div>
        </div>
    </div>
</div>

<script>
    var FlexibagGrid;

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
        FlexibagGrid.enableSmartRendering(true);
        FlexibagGrid.init();

        applyFilters();
    }

    function applyFilters() {
        var start = $('#filterStartDate').val();
        var end = $('#filterEndDate').val();
        var params = [];

        if (start) params.push('start_date=' + encodeURIComponent(start));
        if (end) params.push('end_date=' + encodeURIComponent(end));

        var url = 'xml-cns-inventory-flexibag' + (params.length ? '?' + params.join('&') : '');
        FlexibagGrid.clearAll();
        FlexibagGrid.load(url, function() {
            var balanceUrl = 'cns-inventory-flexibag-balance' + (params.length ? '?' + params.join('&') : '');
            $.get(balanceUrl, function(res) {
                if (res && res.balance !== undefined) {
                    $('#currentBalanceDisplay').text(Number(res.balance).toLocaleString('id-ID'));
                }
                if (res && res.initial_balance !== undefined && start) {
                    $('#initialBalanceDisplay').text(Number(res.initial_balance).toLocaleString('id-ID'));
                    $('#initialBalanceWrapper').show();
                } else {
                    $('#initialBalanceWrapper').hide();
                }
            });
        }, 'xml');
    }

    function resetFilters() {
        initDefaultDateRange();
        applyFilters();
    }
</script>
@endsection
