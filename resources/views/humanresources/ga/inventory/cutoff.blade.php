@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">General Affairs</a></li>
            <li class="breadcrumb-item"><a href="#">Inventory</a></li>
            <li class="breadcrumb-item active"><span class="active">Cutoff Stock</span></li>
        </ol>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="row g-2 align-items-center mb-3">
                <div class="col-md-2">
                    <input type="date" id="GAINV_Cutoff_AsOf" class="form-control form-control-sm" value="{{ $defaultAsOf }}" />
                </div>
                <div class="col-md-2">
                    <select id="GAINV_Cutoff_Location" class="form-control form-control-sm">
                        <option value="">Semua Lokasi</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location }}" @if ((string) $location === (string) $defaultLocation) selected @endif>{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="GAINV_Cutoff_WH" class="form-control form-control-sm">
                        <option value="">Semua Gudang</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse }}">{{ $warehouse }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" id="GAINV_Cutoff_Search" class="form-control form-control-sm" placeholder="Cari kode / deskripsi barang" />
                </div>
                <div class="col-md-3 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-primary" onclick="GAINV_CutoffApply()">Terapkan Filter</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="GAINV_CutoffReset()">Reset</button>
                    <button type="button" class="btn btn-sm btn-info text-white" onclick="GAINV_CutoffRefresh()">Refresh Data</button>
                </div>
            </div>

            <div id="GAINV_Cutoff" style="width:100%;height:420px;background-color:white;overflow:hidden"></div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-low-stock') }}">Low Stock</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-minimum-stock') }}">Minimum Stock</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-ledger') }}">Stock Ledger</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('inventory-incoming') }}"><i class="mdi mdi-truck-delivery"></i> Incoming</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('inventory-request') }}"><i class="mdi mdi-cart-arrow-down"></i> Request</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('hrd-ga-inventory-transfer-stock') }}"><i class="mdi mdi-arrow-left-right"></i> Moving Stock</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('hrd-ga-inventory-reusable') }}"><i class="mdi mdi-cube-send"></i> Reusable</a>
            </div>
            <div class="mt-2 text-muted small">
                Cutoff menampilkan saldo ledger terakhir pada atau sebelum tanggal yang dipilih.
            </div>
        </div>
    </div>

<script>
function GAINV_CutoffQuery() {
    var params = [];
    var asOf = document.getElementById('GAINV_Cutoff_AsOf').value;
    var location = document.getElementById('GAINV_Cutoff_Location').value;
    var wh = document.getElementById('GAINV_Cutoff_WH').value;
    var search = document.getElementById('GAINV_Cutoff_Search').value;
    if (asOf) params.push('as_of=' + encodeURIComponent(asOf));
    if (location) params.push('location=' + encodeURIComponent(location));
    if (wh) params.push('wh_id=' + encodeURIComponent(wh));
    if (search) params.push('search=' + encodeURIComponent(search));
    return params.length ? ('?' + params.join('&')) : '';
}

function GAINV_CutoffApply() {
    GAINV_Cutoff.clearAndLoad('hrd-ga-inventory-cutoff-data' + GAINV_CutoffQuery());
}

function GAINV_CutoffReset() {
    document.getElementById('GAINV_Cutoff_AsOf').value = '';
    document.getElementById('GAINV_Cutoff_Location').value = '';
    document.getElementById('GAINV_Cutoff_WH').value = '';
    document.getElementById('GAINV_Cutoff_Search').value = '';
    GAINV_Cutoff.clearAndLoad('hrd-ga-inventory-cutoff-data');
}

function GAINV_CutoffRefresh() {
    GAINV_Cutoff.clearAndLoad('hrd-ga-inventory-cutoff-data' + GAINV_CutoffQuery());
}

GAINV_Cutoff = new dhtmlXGridObject('GAINV_Cutoff');
GAINV_Cutoff.setImagePath('./dhtmlx/imgs/');
GAINV_Cutoff.setHeader('No,Product Code,Description,Unit,Location,WH,Qty,Value,Avg Cost,Last Movement');
GAINV_Cutoff.attachHeader('&nbsp;,#text_filter,#text_filter,#select_filter,#select_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter');
GAINV_Cutoff.setInitWidths('50,130,300,70,100,100,90,110,90,110');
GAINV_Cutoff.setColAlign('center,center,left,center,center,center,right,right,right,center');
GAINV_Cutoff.setColSorting('int,str,str,str,str,str,str,str,str,str');
GAINV_Cutoff.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro,ro,ro');
GAINV_Cutoff.enableSmartRendering(true, 200);
GAINV_Cutoff.setSkin('dhx_skyblue');
GAINV_Cutoff.init();
GAINV_Cutoff.clearAndLoad('hrd-ga-inventory-cutoff-data' + GAINV_CutoffQuery());
</script>
@endsection