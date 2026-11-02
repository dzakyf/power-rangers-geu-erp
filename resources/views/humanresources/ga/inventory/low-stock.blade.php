@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">General Affairs</a></li>
            <li class="breadcrumb-item"><a href="#">Inventory</a></li>
            <li class="breadcrumb-item active"><span class="active">Low Stock</span></li>
        </ol>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="row g-2 align-items-center mb-3">
                <div class="col-md-2">
                    <select id="GAINV_LS_Location" class="form-control form-control-sm">
                        <option value="">Semua Lokasi</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location }}" @if ((string) $location === (string) $defaultLocation) selected @endif>{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="GAINV_LS_WH" class="form-control form-control-sm">
                        <option value="">Semua Gudang</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse }}">{{ $warehouse }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" id="GAINV_LS_Search" class="form-control form-control-sm" placeholder="Cari kode / deskripsi barang" />
                </div>
                <div class="col-md-5 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-primary" onclick="GAINVLowStockApply()">Terapkan Filter</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="GAINVLowStockReset()">Reset</button>
                    <button type="button" class="btn btn-sm btn-info text-white" onclick="GAINVLowStockRefresh()">Refresh Data</button>
                </div>
            </div>

            <div id="GAINVLowStock" style="width:100%;height:400px;background-color:white;overflow:hidden"></div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="button" class="btn btn-warning btn-sm text-dark" onclick="GAINVLowStockMinimum()"><i class="mdi mdi-settings"></i> Atur Minimum Barang</button>
                <button type="button" class="btn btn-success btn-sm" onclick="GAINVGenerateDraftPR()"><i class="mdi mdi-cart-plus"></i> Buat Draft Purchase Request</button>
                <a class="btn btn-outline-info btn-sm" href="{{ url('hrd-ga-inventory-adjustment') }}"><i class="mdi mdi-scale-balance"></i> Stock Adjustment</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-minimum-stock') }}">Minimum Stock</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-ledger') }}">Stock Ledger</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-cutoff') }}">Cutoff Stock</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('inventory-incoming') }}"><i class="mdi mdi-truck-delivery"></i> Incoming</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('inventory-request') }}"><i class="mdi mdi-cart-arrow-down"></i> Request</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('hrd-ga-inventory-transfer-stock') }}"><i class="mdi mdi-arrow-left-right"></i> Moving Stock</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('hrd-ga-inventory-reusable') }}"><i class="mdi mdi-cube-send"></i> Reusable</a>
            </div>
            <div class="mt-2 text-muted small">
                Minimum default diambil dari kolom Dimension pada master barang jika belum ada pengaturan minimum khusus.
            </div>
        </div>
    </div>

<script>
function GAINVLowStockQuery() {
    var params = [];
    var location = document.getElementById('GAINV_LS_Location').value;
    var wh = document.getElementById('GAINV_LS_WH').value;
    var search = document.getElementById('GAINV_LS_Search').value;
    if (location) params.push('location=' + encodeURIComponent(location));
    if (wh) params.push('wh_id=' + encodeURIComponent(wh));
    if (search) params.push('search=' + encodeURIComponent(search));
    return params.length ? ('?' + params.join('&')) : '';
}

function GAINVLowStockApply() {
    GAINVLowStock.clearAndLoad('hrd-ga-inventory-low-stock-data' + GAINVLowStockQuery());
}

function GAINVLowStockReset() {
    document.getElementById('GAINV_LS_Location').value = '';
    document.getElementById('GAINV_LS_WH').value = '';
    document.getElementById('GAINV_LS_Search').value = '';
    GAINVLowStock.clearAndLoad('hrd-ga-inventory-low-stock-data');
}

function GAINVLowStockRefresh() {
    GAINVLowStock.clearAndLoad('hrd-ga-inventory-low-stock-data' + GAINVLowStockQuery());
}

function GAINVLowStockMinimum() {
    var product = GAINVLowStock.getSelectedRowId();
    if (!product) {
        alert('Pilih baris barang terlebih dahulu.');
        return false;
    }
    var location = GAINVLowStock.cells(product, 4).getValue();
    var wh = document.getElementById('GAINV_LS_WH').value;
    var url = '{{ url('hrd-ga-inventory-minimum-stock') }}?Product_Code=' + encodeURIComponent(product)
        + '&Location=' + encodeURIComponent(location === '-' ? '' : location)
        + '&WH_ID=' + encodeURIComponent(wh);
    window.location.href = url;
}

GAINVLowStock = new dhtmlXGridObject('GAINVLowStock');
GAINVLowStock.setImagePath('./dhtmlx/imgs/');
GAINVLowStock.setHeader('No,Product Code,Description,Unit,Location,On Hand,Min Qty,Shortage,Status');
GAINVLowStock.attachHeader('&nbsp;,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#text_filter,#text_filter,#select_filter');
GAINVLowStock.setInitWidths('50,130,260,70,100,90,90,90,140');
GAINVLowStock.setColAlign('center,center,left,center,center,right,right,right,center');
GAINVLowStock.setColSorting('int,str,str,str,str,str,str,str,str');
GAINVLowStock.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro,ro');
GAINVLowStock.enableSmartRendering(true, 200);
GAINVLowStock.setSkin('dhx_skyblue');
GAINVLowStock.init();
GAINVLowStock.clearAndLoad('hrd-ga-inventory-low-stock-data' + GAINVLowStockQuery());

function GAINVGenerateDraftPR() {
    var product = GAINVLowStock.getSelectedRowId();
    if (!product) {
        alert('Pilih baris barang yang stoknya menipis terlebih dahulu.');
        return false;
    }

    if (!confirm('Buat Draft Purchase Request (PR) otomatis untuk barang ' + product + ' ke tim Purchasing?')) {
        return false;
    }

    var location = GAINVLowStock.cells(product, 4).getValue();
    location = (location === '-' ? '' : location);

    $.post("{{ url('hrd-ga-inventory-generate-pr') }}", {
        _token: "{{ csrf_token() }}",
        product_codes: [product],
        Location: location,
        Note: "Permintaan pembelian otomatis dari GA Low Stock Alert"
    }, function(resp) {
        alert(resp.message);
        if (confirm('Buka daftar Purchase Request di menu Purchasing sekarang?')) {
            window.location.href = "{{ url('purchasing-request-new') }}";
        }
    }).fail(function(err) {
        alert((err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal membuat draft PR.');
    });
}
</script>
@endsection