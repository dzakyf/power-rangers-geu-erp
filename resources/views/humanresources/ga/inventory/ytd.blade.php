@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">General Affairs</a></li>
            <li class="breadcrumb-item"><a href="#">Inventory</a></li>
            <li class="breadcrumb-item active"><span class="active">YTD Stock Report</span></li>
        </ol>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="row g-2 align-items-end mb-3">
                <div class="col-md-2">
                    <label class="small fw-bold mb-1">Tahun</label>
                    <select id="GAINV_YTD_Year" class="form-control form-control-sm">
                        @for ($y = (int)date('Y'); $y >= 2024; $y--)
                            <option value="{{ $y }}" @if ($y == (int)$defaultYear) selected @endif>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold mb-1">Sampai Tanggal (As Of)</label>
                    <input type="date" id="GAINV_YTD_AsOf" class="form-control form-control-sm" value="{{ $defaultAsOf }}" />
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold mb-1">Lokasi</label>
                    <select id="GAINV_YTD_Location" class="form-control form-control-sm">
                        <option value="">Semua Lokasi</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location }}" @if ((string) $location === (string) $defaultLocation) selected @endif>{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold mb-1">Gudang</label>
                    <select id="GAINV_YTD_WH" class="form-control form-control-sm">
                        <option value="">Semua Gudang</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse }}">{{ $warehouse }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold mb-1">Pencarian Barang</label>
                    <input type="text" id="GAINV_YTD_Search" class="form-control form-control-sm" placeholder="Kode / Nama Barang" />
                </div>
                <div class="col-md-2 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-primary" onclick="GAINV_YTDApply()">Terapkan</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="GAINV_YTDReset()">Reset</button>
                    <button type="button" class="btn btn-sm btn-warning text-dark" onclick="GAINV_YTDGrid.toExcel('./export/generate.php');">Excel</button>
                </div>
            </div>

            <div id="GAINV_YTDGrid" style="width:100%;height:430px;background-color:white;overflow:hidden"></div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-cutoff') }}">Cutoff Stock</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-ledger') }}">Stock Ledger</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-low-stock') }}">Low Stock</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-inventory-minimum-stock') }}">Minimum Stock</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('inventory-incoming') }}"><i class="mdi mdi-truck-delivery"></i> Incoming</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ url('inventory-request') }}"><i class="mdi mdi-cart-arrow-down"></i> Request</a>
            </div>
            <div class="mt-2 text-muted small">
                * Laporan Year-to-Date (YTD) menghitung Saldo Awal per 1 Januari, Total Penerimaan YTD, Total Pengeluaran YTD, dan Saldo Akhir Kumulatif.
            </div>
        </div>
    </div>

<script>
function GAINV_YTDQuery() {
    var params = [];
    var year = document.getElementById('GAINV_YTD_Year').value;
    var asOf = document.getElementById('GAINV_YTD_AsOf').value;
    var location = document.getElementById('GAINV_YTD_Location').value;
    var wh = document.getElementById('GAINV_YTD_WH').value;
    var search = document.getElementById('GAINV_YTD_Search').value;
    if (year) params.push('year=' + encodeURIComponent(year));
    if (asOf) params.push('as_of=' + encodeURIComponent(asOf));
    if (location) params.push('location=' + encodeURIComponent(location));
    if (wh) params.push('wh_id=' + encodeURIComponent(wh));
    if (search) params.push('search=' + encodeURIComponent(search));
    return params.length ? ('?' + params.join('&')) : '';
}

function GAINV_YTDApply() {
    GAINV_YTDGrid.clearAndLoad('hrd-ga-inventory-ytd-data' + GAINV_YTDQuery());
}

function GAINV_YTDReset() {
    document.getElementById('GAINV_YTD_Search').value = '';
    document.getElementById('GAINV_YTD_WH').value = '';
    GAINV_YTDApply();
}

GAINV_YTDGrid = new dhtmlXGridObject('GAINV_YTDGrid');
GAINV_YTDGrid.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
GAINV_YTDGrid.setHeader("No.,Kode Barang,Deskripsi,Satuan,Lokasi,Gudang,Saldo Awal (Qty),Nilai Awal (Rp),Masuk YTD,Keluar YTD,Saldo Akhir (Qty),Nilai Akhir (Rp),Avg Cost (Rp),Per Tanggal");
GAINV_YTDGrid.setInitWidths("50,110,220,70,90,90,110,120,90,90,110,130,110,100");
GAINV_YTDGrid.attachHeader("#rspan,#text_filter,#text_filter,#select_filter,#select_filter,#select_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#text_filter");
GAINV_YTDGrid.setColAlign("center,left,left,center,center,center,right,right,right,right,right,right,right,center");
GAINV_YTDGrid.setColTypes("ro,ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ron,ron,ro");
GAINV_YTDGrid.setNumberFormat("0,000.00", 6, ".", ",");
GAINV_YTDGrid.setNumberFormat("0,000.00", 7, ".", ",");
GAINV_YTDGrid.setNumberFormat("0,000.00", 8, ".", ",");
GAINV_YTDGrid.setNumberFormat("0,000.00", 9, ".", ",");
GAINV_YTDGrid.setNumberFormat("0,000.00", 10, ".", ",");
GAINV_YTDGrid.setNumberFormat("0,000.00", 11, ".", ",");
GAINV_YTDGrid.setNumberFormat("0,000.00", 12, ".", ",");
GAINV_YTDGrid.setColSorting("int,str,str,str,str,str,int,int,int,int,int,int,int,str");
GAINV_YTDGrid.setSkin("dhx_skyblue");
GAINV_YTDGrid.init();
GAINV_YTDApply();
</script>
@endsection

