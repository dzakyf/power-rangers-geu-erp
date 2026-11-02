@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">General Affairs</a></li>
            <li class="breadcrumb-item"><a href="#">Inventory</a></li>
            <li class="breadcrumb-item active"><span class="active">Minimum Stock</span></li>
        </ol>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="row g-2 align-items-center mb-3">
                <div class="col-md-2">
                    <select id="GAINV_Min_Filter_Location" class="form-control form-control-sm">
                        <option value="">Semua Lokasi</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location }}" @if ((string) $location === (string) $defaultLocation) selected @endif>{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="GAINV_Min_Filter_WH" class="form-control form-control-sm">
                        <option value="">Semua Gudang</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse }}" @if ((string) $warehouse === (string) $defaultWhId) selected @endif>{{ $warehouse }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" id="GAINV_Min_Filter_Search" class="form-control form-control-sm" placeholder="Cari kode / deskripsi barang" />
                </div>
                <div class="col-md-5 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-primary" onclick="GAINVMinimumApply()">Terapkan Filter</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="GAINVMinimumReset()">Reset</button>
                    <button type="button" class="btn btn-sm btn-info text-white" onclick="GAINVMinimumRefresh()">Refresh Data</button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 mb-3 mb-md-0">
                    <div id="GAINVMinimum" style="width:100%;height:400px;background-color:white;overflow:hidden"></div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 bg-light">
                        <h5 class="mb-3">Pengaturan Minimum</h5>
                        <div class="form-group mb-3">
                            <label>Product Code</label>
                            <input type="text" id="GAINV_Min_Product" class="form-control form-control-sm" list="GAINV_Products" value="{{ $defaultProductCode }}" />
                            <datalist id="GAINV_Products">
                                @foreach ($products as $product)
                                    <option value="{{ $product->Product_Code }}">{{ $product->Description }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="form-group mb-3">
                            <label>Location</label>
                            <input type="text" id="GAINV_Min_Location" class="form-control form-control-sm" value="{{ $defaultLocation }}" />
                        </div>
                        <div class="form-group mb-3">
                            <label>Warehouse</label>
                            <input type="text" id="GAINV_Min_WH" class="form-control form-control-sm" value="{{ $defaultWhId }}" />
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label>Min Qty</label>
                                <input type="number" step="0.01" min="0" id="GAINV_Min_Qty" class="form-control form-control-sm" value="0" />
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label>Target Qty</label>
                                <input type="number" step="0.01" min="0" id="GAINV_Min_Target" class="form-control form-control-sm" value="0" />
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" id="GAINV_Min_Active" class="form-check-input" checked />
                            <label class="form-check-label" for="GAINV_Min_Active">Aktif</label>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if (can_menu('SI01016', 'edit'))
                                <button type="button" class="btn btn-primary btn-sm" onclick="GAINVMinimumSave()">Simpan</button>
                            @endif
                            <button type="button" class="btn btn-secondary btn-sm" onclick="GAINVMinimumClear()">Baru</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-low-stock') }}">Low Stock</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-ledger') }}">Stock Ledger</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-cutoff') }}">Cutoff Stock</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('inventory-incoming') }}"><i class="mdi mdi-truck-delivery"></i> Incoming</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('inventory-request') }}"><i class="mdi mdi-cart-arrow-down"></i> Request</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('hrd-ga-inventory-transfer-stock') }}"><i class="mdi mdi-arrow-left-right"></i> Moving Stock</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('hrd-ga-inventory-reusable') }}"><i class="mdi mdi-cube-send"></i> Reusable</a>
            </div>
        </div>
    </div>

<script>
function GAINVMinimumQuery() {
    var params = [];
    var location = document.getElementById('GAINV_Min_Filter_Location').value;
    var wh = document.getElementById('GAINV_Min_Filter_WH').value;
    var search = document.getElementById('GAINV_Min_Filter_Search').value;
    if (location) params.push('location=' + encodeURIComponent(location));
    if (wh) params.push('wh_id=' + encodeURIComponent(wh));
    if (search) params.push('search=' + encodeURIComponent(search));
    return params.length ? ('?' + params.join('&')) : '';
}

function GAINVMinimumApply() {
    GAINVMinimum.clearAndLoad('hrd-ga-inventory-minimum-stock-data' + GAINVMinimumQuery());
}

function GAINVMinimumReset() {
    document.getElementById('GAINV_Min_Filter_Location').value = '';
    document.getElementById('GAINV_Min_Filter_WH').value = '';
    document.getElementById('GAINV_Min_Filter_Search').value = '';
    GAINVMinimum.clearAndLoad('hrd-ga-inventory-minimum-stock-data');
}

function GAINVMinimumRefresh() {
    GAINVMinimum.clearAndLoad('hrd-ga-inventory-minimum-stock-data' + GAINVMinimumQuery());
}

function GAINVMinimumSelect(id) {
    if (!id) return;
    var location = GAINVMinimum.cells(id, 3).getValue();
    var wh = GAINVMinimum.cells(id, 4).getValue();
    document.getElementById('GAINV_Min_Product').value = GAINVMinimum.cells(id, 1).getValue();
    document.getElementById('GAINV_Min_Location').value = location === '-' ? '' : location;
    document.getElementById('GAINV_Min_WH').value = wh === '-' ? '' : wh;
    document.getElementById('GAINV_Min_Qty').value = GAINVMinimum.cells(id, 5).getValue();
    document.getElementById('GAINV_Min_Target').value = GAINVMinimum.cells(id, 6).getValue();
    document.getElementById('GAINV_Min_Active').checked = GAINVMinimum.cells(id, 7).getValue() === 'Ya';
}

function GAINVMinimumClear() {
    document.getElementById('GAINV_Min_Product').value = '';
    document.getElementById('GAINV_Min_Location').value = document.getElementById('GAINV_Min_Filter_Location').value;
    document.getElementById('GAINV_Min_WH').value = document.getElementById('GAINV_Min_Filter_WH').value;
    document.getElementById('GAINV_Min_Qty').value = '0';
    document.getElementById('GAINV_Min_Target').value = '0';
    document.getElementById('GAINV_Min_Active').checked = true;
}

function GAINVMinimumSave() {
    var product = document.getElementById('GAINV_Min_Product').value.trim();
    if (!product) {
        alert('Product Code wajib diisi.');
        return false;
    }

    $.post('hrd-ga-inventory-minimum-stock-save', {
        _token: '{{ csrf_token() }}',
        Product_Code: product,
        Location: document.getElementById('GAINV_Min_Location').value,
        WH_ID: document.getElementById('GAINV_Min_WH').value,
        Min_Qty: document.getElementById('GAINV_Min_Qty').value,
        Target_Qty: document.getElementById('GAINV_Min_Target').value,
        Is_Active: document.getElementById('GAINV_Min_Active').checked ? 1 : 0
    }, function (response) {
        alert(response.message || 'Minimum stock disimpan.');
        GAINVMinimum.clearAndLoad('hrd-ga-inventory-minimum-stock-data' + GAINVMinimumQuery());
    }).fail(function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan minimum stock.');
    });
}

GAINVMinimum = new dhtmlXGridObject('GAINVMinimum');
GAINVMinimum.setImagePath('./dhtmlx/imgs/');
GAINVMinimum.setHeader('No,Product Code,Description,Location,WH,Min Qty,Target Qty,Active');
GAINVMinimum.attachHeader('&nbsp;,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#text_filter,#select_filter');
GAINVMinimum.setInitWidths('50,130,260,100,100,90,90,80');
GAINVMinimum.setColAlign('center,center,left,center,center,right,right,center');
GAINVMinimum.setColSorting('int,str,str,str,str,str,str,str');
GAINVMinimum.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro');
GAINVMinimum.enableSmartRendering(true, 200);
GAINVMinimum.setSkin('dhx_skyblue');
GAINVMinimum.init();
GAINVMinimum.attachEvent('onRowSelect', function (id) {
    GAINVMinimumSelect(id);
});
GAINVMinimum.clearAndLoad('hrd-ga-inventory-minimum-stock-data' + GAINVMinimumQuery());
</script>
@endsection