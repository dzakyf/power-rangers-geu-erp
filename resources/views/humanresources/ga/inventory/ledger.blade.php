@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">General Affairs</a></li>
            <li class="breadcrumb-item"><a href="#">Inventory</a></li>
            <li class="breadcrumb-item active"><span class="active">Stock Ledger</span></li>
        </ol>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="row g-2 mb-2">
                <div class="col-md-2">
                    <input type="text" id="GAINV_Ledger_Product" class="form-control form-control-sm" placeholder="Product Code" />
                </div>
                <div class="col-md-2">
                    <select id="GAINV_Ledger_Location" class="form-control form-control-sm">
                        <option value="">Semua Lokasi</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location }}" @if ((string) $location === (string) $defaultLocation) selected @endif>{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="GAINV_Ledger_WH" class="form-control form-control-sm">
                        <option value="">Semua Gudang</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse }}">{{ $warehouse }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" id="GAINV_Ledger_DateFrom" class="form-control form-control-sm" value="{{ $defaultDateFrom }}" />
                </div>
                <div class="col-md-2">
                    <input type="date" id="GAINV_Ledger_DateTo" class="form-control form-control-sm" value="{{ $defaultDateTo }}" />
                </div>
                <div class="col-md-2">
                    <input type="text" id="GAINV_Ledger_Search" class="form-control form-control-sm" placeholder="Cari deskripsi" />
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-primary" onclick="GAINV_LedgerApply()">Terapkan Filter</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="GAINV_LedgerReset()">Reset</button>
                    <button type="button" class="btn btn-sm btn-info text-white" onclick="GAINV_LedgerRefresh()">Refresh Data</button>
                    @if (can_menu('SI01015', 'edit'))
                        <button type="button" class="btn btn-sm btn-warning text-dark" onclick="GAINV_BackfillOpening()">Backfill Opening</button>
                    @endif
                </div>
            </div>

            <div id="GAINV_Ledger" style="width:100%;height:420px;background-color:white;overflow:hidden"></div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-low-stock') }}">Low Stock</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-minimum-stock') }}">Minimum Stock</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-cutoff') }}">Cutoff Stock</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('inventory-incoming') }}"><i class="mdi mdi-truck-delivery"></i> Incoming</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('inventory-request') }}"><i class="mdi mdi-cart-arrow-down"></i> Request</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('hrd-ga-inventory-transfer-stock') }}"><i class="mdi mdi-arrow-left-right"></i> Moving Stock</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('hrd-ga-inventory-reusable') }}"><i class="mdi mdi-cube-send"></i> Reusable</a>
            </div>
            <div class="mt-2 text-muted small">
                Backfill Opening memakai tanggal pada kolom Date From dan hanya menulis ke ledger GA, tidak mengubah data supplies.
            </div>
        </div>
    </div>

<script>
function GAINV_LedgerQuery() {
    var params = [];
    var product = document.getElementById('GAINV_Ledger_Product').value;
    var location = document.getElementById('GAINV_Ledger_Location').value;
    var wh = document.getElementById('GAINV_Ledger_WH').value;
    var from = document.getElementById('GAINV_Ledger_DateFrom').value;
    var to = document.getElementById('GAINV_Ledger_DateTo').value;
    var search = document.getElementById('GAINV_Ledger_Search').value;
    if (product) params.push('product_code=' + encodeURIComponent(product));
    if (location) params.push('location=' + encodeURIComponent(location));
    if (wh) params.push('wh_id=' + encodeURIComponent(wh));
    if (from) params.push('date_from=' + encodeURIComponent(from));
    if (to) params.push('date_to=' + encodeURIComponent(to));
    if (search) params.push('search=' + encodeURIComponent(search));
    return params.length ? ('?' + params.join('&')) : '';
}

function GAINV_LedgerApply() {
    GAINV_Ledger.clearAndLoad('hrd-ga-inventory-ledger-data' + GAINV_LedgerQuery());
}

function GAINV_LedgerReset() {
    document.getElementById('GAINV_Ledger_Product').value = '';
    document.getElementById('GAINV_Ledger_Location').value = '';
    document.getElementById('GAINV_Ledger_WH').value = '';
    document.getElementById('GAINV_Ledger_DateFrom').value = '';
    document.getElementById('GAINV_Ledger_DateTo').value = '';
    document.getElementById('GAINV_Ledger_Search').value = '';
    GAINV_Ledger.clearAndLoad('hrd-ga-inventory-ledger-data');
}

function GAINV_LedgerRefresh() {
    GAINV_Ledger.clearAndLoad('hrd-ga-inventory-ledger-data' + GAINV_LedgerQuery());
}

function GAINV_BackfillOpening() {
    var date = document.getElementById('GAINV_Ledger_DateFrom').value;
    var location = document.getElementById('GAINV_Ledger_Location').value;
    var wh = document.getElementById('GAINV_Ledger_WH').value;
    if (!confirm('Backfill opening balance dari supplies untuk tanggal ' + (date || 'hari ini') + '?')) {
        return false;
    }
    $.post('hrd-ga-inventory-opening-backfill', {
        _token: '{{ csrf_token() }}',
        Movement_Date: date,
        Location: location,
        WH_ID: wh
    }, function (response) {
        alert(response.message || 'Backfill selesai.');
        GAINV_Ledger.clearAndLoad('hrd-ga-inventory-ledger-data' + GAINV_LedgerQuery());
    }).fail(function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Backfill gagal.');
    });
}

GAINV_Ledger = new dhtmlXGridObject('GAINV_Ledger');
GAINV_Ledger.setImagePath('./dhtmlx/imgs/');
GAINV_Ledger.setHeader('No,Tanggal,Type,Product Code,Description,Location,WH,Bin,Qty In,Qty Out,Unit Cost,Running Qty,Running Value,Avg Cost,Source,Created By');
GAINV_Ledger.attachHeader('&nbsp;,#text_filter,#select_filter,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter');
GAINV_Ledger.setInitWidths('50,90,90,120,220,90,90,80,70,70,90,90,100,90,150,100');
GAINV_Ledger.setColAlign('center,center,center,center,left,center,center,center,right,right,right,right,right,right,left,left');
GAINV_Ledger.setColSorting('int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str');
GAINV_Ledger.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro');
GAINV_Ledger.enableSmartRendering(true, 200);
GAINV_Ledger.setSkin('dhx_skyblue');
GAINV_Ledger.init();
GAINV_Ledger.clearAndLoad('hrd-ga-inventory-ledger-data' + GAINV_LedgerQuery());
</script>
@endsection